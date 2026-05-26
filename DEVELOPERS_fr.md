# Statboard API — Documentation technique

Documentation technique du plugin Moodle **`local_su_statboard_api`** pour exposer des statistiques d'usage de la plateforme via une API REST sécurisée, optimisée et destinée à alimenter un tableau de bord externe.

## Table des matières

1. [Vue d'ensemble](#vue-densemble)
2. [Compatibilité et prérequis](#compatibilité-et-prérequis)
3. [Arborescence du plugin](#arborescence-du-plugin)
4. [Schéma de base de données](#schéma-de-base-de-données)
5. [API REST](#api-rest)
6. [Stratégie de cache MUC](#stratégie-de-cache-muc)
7. [Tâches planifiées](#tâches-planifiées)
8. [Gestion des tokens](#gestion-des-tokens)
9. [Installation](#installation)
10. [Désinstallation](#désinstallation)
11. [Sécurité et permissions](#sécurité-et-permissions)
12. [Conformité RGPD](#conformité-rgpd)
13. [Internationalisation](#internationalisation)
14. [Guide de développement](#guide-de-développement)
15. [Maintenance et dépannage](#maintenance-et-dépannage)

## Vue d'ensemble

Le plugin **`local_su_statboard_api`** (release `v1.0.0`, version `2025021406`) est un plugin local Moodle qui expose en un appel REST un ensemble de statistiques d'usage destinées à alimenter un tableau de bord externe.

L'architecture combine trois mécanismes pour servir les métriques avec une latence minimale, même sur une plateforme à très fort volume (logstore de plus de 100 millions de lignes, cluster à 4 serveurs) :

- **Cache MUC** (Moodle Universal Cache) avec TTL différenciés selon la volatilité de la métrique.
- **Tables résumé** alimentées par cron, qui évitent d'attaquer le `logstore_standard_log` à chaque appel.
- **Requêtes SQL portables** écrites avec des paramètres nommés (sans `UNIX_TIMESTAMP()` ni fonction spécifique) pour fonctionner sur PostgreSQL, MySQL et MariaDB.

Sur un appel à froid (cache complètement vide), seules **4 requêtes** au plus sont exécutées contre la base, contre 55+ dans la première version du plugin.

### Licence

GNU GPL v3 ou ultérieure.

## Compatibilité et prérequis

### Moodle

- Version minimum requise : Moodle 4.1 (`2022112800`).
- Compatibilité testée jusqu'à Moodle 4.5+.

### Bases de données

| SGBD | Version minimum | Statut |
|------|-----------------|--------|
| PostgreSQL | 12.0+ | Supporté |
| MySQL | 5.7.33+ | Supporté |
| MariaDB | 10.6+ | Supporté |
| Microsoft SQL Server | — | **Non supporté** |
| Oracle | — | **Non supporté** |
| SQLite | — | **Non supporté** |

L'installation (`db/install.php`) vérifie automatiquement le type et la version du SGBD au démarrage. Si la base est non supportée ou trop ancienne, l'installation s'arrête avec un message d'erreur explicite.

## Arborescence du plugin

```
local/su_statboard_api/
├── amd/
│   ├── src/token_manager.js          # Source AMD (gestion JS de la page tokens)
│   └── build/token_manager.min.js    # Version minifiée
├── classes/
│   ├── event/
│   │   └── stats_viewed.php          # Événement déclenché à chaque appel API
│   ├── privacy/
│   │   └── provider.php              # Conformité RGPD (Privacy API complète)
│   └── task/
│       ├── aggregate_daily_stats.php   # Cron quotidien (00:05)
│       └── aggregate_hourly_stats.php  # Cron horaire (HH:01)
├── db/
│   ├── access.php                    # Capabilities (view + managetokensettings)
│   ├── admin.php                     # Pages d'administration
│   ├── caches.php                    # Définitions MUC (statboard_*)
│   ├── events.php                    # Observer config_log_created
│   ├── install.php                   # Installation automatisée
│   ├── install.xml                   # Schéma des tables custom
│   ├── services.php                  # Définition du WS exposé
│   ├── tasks.php                     # Déclaration des tâches planifiées
│   └── uninstall.php                 # Désinstallation propre et complète
├── lang/
│   ├── de/local_su_statboard_api.php
│   ├── en/local_su_statboard_api.php
│   ├── es/local_su_statboard_api.php
│   ├── fr/local_su_statboard_api.php
│   ├── it/local_su_statboard_api.php
│   ├── pt/local_su_statboard_api.php
│   └── pt_br/local_su_statboard_api.php
├── pix/
│   └── icon.png                      # Icône du plugin
├── style/
│   └── styles.css                    # Styles des pages d'admin
├── templates/
│   ├── token_settings.mustache       # UI gestion du token
│   └── view_token.mustache           # UI consultation du token
├── externallib.php                   # Implémentation de get_statboard_stats()
├── locallib.php                      # Helpers tokens + SQL portables
├── settings.php                      # Page de configuration plugin
├── token_settings.php                # Contrôleur gestion token
├── version.php                       # Métadonnées du plugin
└── view_token.php                    # Contrôleur consultation token
```

## Schéma de base de données

### Tables custom créées par le plugin

#### `su_statboard_daily_stats`

Agrégation quotidienne des connexions. Alimentée chaque nuit à 00:05 par la tâche `aggregate_daily_stats`. Contient au plus 30 lignes (rétention glissante).

| Champ | Type | Description |
|-------|------|-------------|
| `id` | int(10), AUTO | Clé primaire |
| `statsdate` | char(10) | Date au format `YYYY-MM-DD` (index unique) |
| `logins` | int(10) | Nombre d'utilisateurs distincts ayant déclenché un événement `\core\event\user_loggedin` ce jour-là |
| `timecreated` | int(10) | Timestamp de création de la ligne par le cron |

Index unique sur `statsdate` pour empêcher les doublons.

#### `su_statboard_hourly_stats`

Snapshots horaires des utilisateurs actifs. Alimentée chaque heure à HH:01 par la tâche `aggregate_hourly_stats`. Contient au plus 720 lignes (24 heures × 30 jours).

| Champ | Type | Description |
|-------|------|-------------|
| `id` | int(10), AUTO | Clé primaire |
| `statsdate` | char(10) | Date au format `YYYY-MM-DD` |
| `hour` | int(2) | Heure (0–23) |
| `connections` | int(10) | Nombre d'utilisateurs distincts actifs dans la fenêtre `[H-5min, H]` |
| `timecreated` | int(10) | Timestamp de création de la ligne par le cron |

Index unique composite sur `(statsdate, hour)`.

### Tables Moodle utilisées en lecture

- `user`, `course` : comptages totaux et utilisateurs en ligne.
- `logstore_standard_log` : source d'agrégation pour les crons (utilisée uniquement par les tâches planifiées et pour le jour courant dans `max_connections`).
- `quiz_attempts` : nombre de quiz finalisés aujourd'hui.

### Tables Moodle utilisées en lecture/écriture

- `external_services`, `external_services_functions`, `external_services_users`, `external_tokens` : déclaration et autorisation du service web.
- `config_plugins` : configuration du plugin (token, expiration).
- `user`, `role_assignments` : utilisateur webservice dédié et rôle manager.

### Configuration plugin

| Clé (`config_plugins`) | Description | Valeur par défaut |
|------------------------|-------------|-------------------|
| `webservice_token` | Token API courant | Généré à l'installation |
| `token_validity_period` | Durée de validité (jours) | `365` |
| `token_no_expiration` | Token permanent (1) ou non (0) | `'1'` |

## API REST

### Service web

Un service web unique est créé à l'installation :

- **Nom** : `SU Statboard API Service`
- **Shortname** : `local_su_statboard_api`
- **Authentification** : token bearer
- **Capability requise** : `local/su_statboard_api:view`

### Fonction exposée

`local_su_statboard_api_get_statboard_stats`

Implémentation : `local_su_statboard_api_external::get_statboard_stats()` dans `externallib.php`.

#### Paramètres

| Nom | Type | Obligatoire | Description |
|-----|------|-------------|-------------|
| `date` | int (timestamp Unix) | Non (défaut `0`) | Jour à analyser pour les métriques journalières. `0` = aujourd'hui. |

#### Réponse

```json
{
    "total_users": 1250,
    "total_courses": 89,
    "users_online_now": 42,
    "max_connections": {
        "count": 456,
        "date": "2025-01-15"
    },
    "hourly_connections": [
        { "hour": "00:00", "count": 5 },
        { "hour": "01:00", "count": 2 },
        { "hour": "08:00", "count": 45 }
    ],
    "quiz_completed_today": 312
}
```

#### Détail des métriques

**`total_users`** — Nombre d'utilisateurs actifs (non supprimés, non suspendus). Lecture sur `{user}`.

**`total_courses`** — Nombre de cours hors site (`id > 1`). Lecture sur `{course}`.

**`users_online_now`** — Utilisateurs réels actifs dans les 5 dernières minutes (basé sur `lastaccess`). Exclut les comptes techniques `webservice` et `nologin`. **Toujours en temps réel, jamais mis en cache.**

**`max_connections`** — Pic de connexions sur les 30 derniers jours, avec la date du pic. Combine la table résumé `su_statboard_daily_stats` (J-1 à J-30) et un comptage SQL pour le jour courant.

**`hourly_connections`** — Tableau du nombre d'utilisateurs distincts actifs par tranche horaire du jour demandé. Pour aujourd'hui : heures 00 à l'heure courante. Pour un jour passé : 24 heures complètes. Lu directement dans `su_statboard_hourly_stats`.

**`quiz_completed_today`** — Nombre de tentatives de quiz dans l'état `finished` démarrées depuis 00:00 du jour demandé. Lecture sur `{quiz_attempts}`.

### Audit

Chaque appel à l'API déclenche l'événement Moodle `\local_su_statboard_api\event\stats_viewed`, exploitable via le rapport des logs standards.

## Stratégie de cache MUC

Quatre stores MUC sont déclarés dans `db/caches.php`, tous en mode `MODE_APPLICATION` (partagés entre serveurs du cluster) :

| Store | TTL | Métriques concernées | Justification |
|-------|-----|----------------------|---------------|
| `statboard_totals` | 1 h | `total_users`, `total_courses` | Données très stables |
| `statboard_max` | 15 min | Comptage des connexions du jour pour `max_connections` | Évolue lentement dans la journée |
| `statboard_hourly` | 5 min | (réservé) | Cohérence avec la fréquence du cron horaire |
| `statboard_quiz` | 5 min | `quiz_completed_today` | Évolue régulièrement |

`users_online_now` n'est **jamais mis en cache** — la métrique doit rester en temps réel.

Les clés de cache de `max_connections` et `quiz_completed_today` incluent la date du jour (`max_today_YYYY-MM-DD`, `quiz_completed_YYYY-MM-DD`), ce qui assure une réinitialisation automatique au passage de minuit.

## Tâches planifiées

Déclarées dans `db/tasks.php`, toutes deux avec `blocking = 1` pour empêcher l'exécution simultanée sur le cluster.

### `aggregate_daily_stats` — quotidienne, 00:05

`classes/task/aggregate_daily_stats.php`

À chaque exécution :

1. Calcule le nombre de logins distincts pour J-1 en interrogeant `{logstore_standard_log}` avec un filtre `eventname = '\core\event\user_loggedin'` (utilisation de l'eventname exact pour éviter les `LIKE` coûteux sur des dizaines de millions de lignes).
2. Insère ou met à jour la ligne correspondante dans `su_statboard_daily_stats` (l'index unique sur `statsdate` empêche les doublons).
3. Purge toutes les entrées dont `statsdate` est plus ancien que J-30.

### `aggregate_hourly_stats` — horaire, HH:01

`classes/task/aggregate_hourly_stats.php`

À chaque exécution (par exemple à 11:01) :

1. Détermine l'heure qui vient de se terminer (10 dans cet exemple).
2. Compte les utilisateurs distincts actifs sur la fenêtre `[H:00 - 5 min, H:00]` (par exemple 09:55 → 10:00) en filtrant sur `{logstore_standard_log}`, `userid > 1` et en excluant les événements `%webservice%`.
3. Insère ou met à jour la ligne dans `su_statboard_hourly_stats` (index unique composite sur `(statsdate, hour)`).
4. Purge les entrées plus anciennes que 30 jours.

## Gestion des tokens

Toute la logique se trouve dans `locallib.php`. Une page d'administration dédiée (`token_settings.php`) permet à l'administrateur Moodle de gérer le token sans passer par les écrans natifs des web services.

### Fonctions principales

`local_su_statboard_api_regenerate_token()`
Génère un nouveau token via `external_generate_token()` (token permanent par défaut), supprime l'ancien et persiste le nouveau dans `config_plugins`. Respecte la configuration `token_no_expiration` et `token_validity_period`.

`local_su_statboard_api_update_expiration_date($timestamp)`
Met à jour la date d'expiration de tous les tokens du service et synchronise la valeur `token_validity_period` (en jours) dans la configuration.

`local_su_statboard_api_set_token_no_expiration($serviceid, $token = null)`
Bascule un token (ou tous ceux d'un service) en mode permanent en plaçant `validuntil = NULL` (compatible PostgreSQL).

`local_su_statboard_api_update_token($newtoken)`
Remplace la valeur d'un token existant sans en regénérer un nouveau (utilisé pour les corrections manuelles).

### Cohérence configuration ↔ base

Le contrôleur `token_settings.php` détecte et corrige automatiquement les incohérences entre la configuration plugin (`token_no_expiration`) et l'état réel du token (`external_tokens.validuntil`). Si les deux divergent, c'est l'état réel du token en base qui fait foi : la configuration plugin est réalignée silencieusement.

### Helpers SQL portables

`local_su_statboard_api_get_db_compatible_sql($operation, $column)` — utilitaire pour les rares cas où une fonction SQL spécifique au SGBD est nécessaire (`date_format`, `hour_extract`, `timestamp_to_date`). Bascule automatiquement entre la syntaxe PostgreSQL (`to_char`, `to_timestamp`, `EXTRACT`) et MySQL/MariaDB (`FROM_UNIXTIME`, `HOUR`).

## Installation

`db/install.php` orchestre une installation automatisée et idempotente :

1. Vérification de la compatibilité du SGBD (type + version minimum).
2. Nettoyage d'une éventuelle installation antérieure (tokens, fonctions et utilisateurs liés au service `local_su_statboard_api`).
3. Création (ou réutilisation) d'un utilisateur webservice `webservice_statboard_<timestamp>` avec mot de passe aléatoire.
4. Attribution du rôle `manager` au niveau système.
5. Création du service web `SU Statboard API Service` (`shortname = local_su_statboard_api`).
6. Liaison de la fonction `local_su_statboard_api_get_statboard_stats` au service.
7. Autorisation de l'utilisateur webservice pour ce service.
8. Configuration : `token_validity_period = 365`, `token_no_expiration = '1'`.
9. Génération d'un token permanent via `external_generate_token(EXTERNAL_TOKEN_PERMANENT, ...)` et persistance dans `config_plugins`.

L'installeur `mtrace` chaque étape pour faciliter le diagnostic en CLI.

L'administrateur doit ensuite **activer manuellement les services web et le protocole REST** via `Administration du site > Plugins > Services web`.

## Désinstallation

`db/uninstall.php` réalise un nettoyage complet :

1. Récupération du token et des services dont le `shortname` correspond ou dont le `name` matche `%SU Statboard API%`.
2. Suppression de tous les tokens, fonctions et autorisations liés.
3. Suppression des services eux-mêmes.
4. Suppression des tokens orphelins par valeur (sécurité).
5. Marquage `deleted = 1` des utilisateurs `webservice_statboard_*`.
6. Effacement de toute la configuration plugin via `unset_all_config_for_plugin('local_su_statboard_api')`.

Les tables custom `su_statboard_daily_stats` et `su_statboard_hourly_stats` sont supprimées automatiquement par Moodle (déclaration XMLDB).

## Sécurité et permissions

### Capabilities

Déclarées dans `db/access.php`.

`local/su_statboard_api:view` — Lecture des statistiques via l'API.
- `riskbitmask` : `RISK_PERSONAL`
- `captype` : `read`
- `contextlevel` : `CONTEXT_SYSTEM`
- Archetype : `manager` (autorisé par défaut)

`local/su_statboard_api:managetokensettings` — Gestion du token (régénération, expiration).
- `riskbitmask` : `RISK_CONFIG | RISK_PERSONAL`
- `captype` : `write`
- `contextlevel` : `CONTEXT_SYSTEM`
- Archetype : `manager` (autorisé par défaut)

### Authentification API

Tous les appels à `get_statboard_stats` exigent :

- Un token valide via le paramètre `wstoken`.
- La capability `local/su_statboard_api:view` au niveau système pour l'utilisateur lié au token.

### Pages d'administration

`db/admin.php` enregistre deux entrées dans le menu d'administration, toutes deux protégées par `local/su_statboard_api:managetokensettings` :

- Une entrée dans `localplugins` (page principale du plugin).
- Une entrée dans `webservicesettings` pour un accès direct depuis l'admin des services web.

## Conformité RGPD

`classes/privacy/provider.php` implémente l'API Privacy de Moodle dans son intégralité.

### Métadonnées déclarées

- Table `external_tokens` (champs `token`, `userid`, `validuntil`).
- Lien externe `moodle_webservice` (transmission via WS).
- Sous-système `core_logging` (événement `stats_viewed`).

### Méthodes implémentées

- `get_metadata()` : déclaration des données personnelles stockées et transmises.
- `get_contexts_for_userid($userid)` : retourne le contexte système si l'utilisateur a un token.
- `get_users_in_context(userlist)` : liste les utilisateurs ayant un token pour ce service.
- `export_user_data(approved_contextlist)` : exporte les tokens de l'utilisateur (valeur, date de création, expiration).
- `delete_data_for_all_users_in_context($context)` : supprime tous les tokens du service.
- `delete_data_for_user(approved_contextlist)` : supprime les tokens d'un utilisateur précis.
- `delete_data_for_users(approved_userlist)` : suppression en masse pour plusieurs utilisateurs.

### Données non concernées par la Privacy API

Les tables agrégées créées par le plugin (`su_statboard_daily_stats`, `su_statboard_hourly_stats`) ne sont **pas** déclarées dans `provider.php` car elles ne contiennent que des compteurs anonymes (nombre de connexions, nombre d'utilisateurs distincts). Aucun `userid` ni autre identifiant n'y est stocké, conformément au principe de minimisation des données.

Les appels à l'API génèrent en revanche un événement `stats_viewed` dans le logstore standard de Moodle, attribué à l'utilisateur du token. Ces logs suivent la politique de rétention standard de Moodle et sont couverts par la déclaration `core_logging` ci-dessus.

## Internationalisation

Le plugin est livré avec **7 langues** :

| Code | Langue |
|------|--------|
| `en` | Anglais (langue de référence) |
| `fr` | Français |
| `de` | Allemand |
| `es` | Espagnol |
| `it` | Italien |
| `pt` | Portugais (Portugal) |
| `pt_br` | Portugais (Brésil) |

Chaque fichier `lang/<code>/local_su_statboard_api.php` couvre l'ensemble des chaînes : nom du plugin, capabilities, gestion du token, dates d'expiration, descriptions des caches MUC, libellés des tâches planifiées, messages d'erreur, et chaînes Privacy API.

## Guide de développement

### Ajouter une métrique à `get_statboard_stats`

1. Ajouter la requête (idéalement avec cache MUC) dans `externallib.php::get_statboard_stats()`.
2. Si la métrique mérite un nouveau store, déclarer une définition dans `db/caches.php` avec un TTL approprié et ajouter la chaîne `cachedef_<nom>` dans tous les fichiers de langue.
3. Compléter `get_statboard_stats_returns()` avec la structure de retour.
4. Si la requête doit s'exécuter sur des volumes importants, envisager une table résumé alimentée par cron sur le modèle de `su_statboard_daily_stats`.
5. Tester sur PostgreSQL **et** sur MySQL/MariaDB.

### Ajouter une nouvelle fonction au service

```php
// classes ou externallib.php
public static function get_course_stats_parameters() {
    return new external_function_parameters([
        'courseid' => new external_value(PARAM_INT, 'Course ID'),
    ]);
}

public static function get_course_stats($courseid) {
    // implémentation
}

public static function get_course_stats_returns() {
    // structure de retour
}
```

```php
// db/services.php
'local_su_statboard_api_get_course_stats' => [
    'classname'    => 'local_su_statboard_api_external',
    'methodname'   => 'get_course_stats',
    'description'  => 'Get course-specific statistics',
    'type'         => 'read',
    'ajax'         => true,
    'capabilities' => 'local/su_statboard_api:view',
],
```

Ne pas oublier de bumper `$plugin->version` dans `version.php` pour que Moodle relise `services.php`.

### Tests d'appel manuel

```bash
curl -X POST "https://moodle.example.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=local_su_statboard_api_get_statboard_stats" \
  -d "moodlewsrestformat=json" \
  -d "date=0"
```

### Forçage manuel des crons

```bash
php admin/cli/scheduled_task.php --execute='\local_su_statboard_api\task\aggregate_daily_stats'
php admin/cli/scheduled_task.php --execute='\local_su_statboard_api\task\aggregate_hourly_stats'
```

## Maintenance et dépannage

### Vérification de santé

La page de configuration `Administration du site > Plugins > Plugins locaux > Statboard API` affiche en temps réel :

- L'état du token (valide / expiré / permanent).
- Une éventuelle incohérence détectée entre la configuration et la base.
- Un raccourci vers la liste des tâches planifiées Moodle.

### Codes d'erreur courants

| Code | Cause probable | Action |
|------|----------------|--------|
| `invalidtoken` | Token expiré, supprimé, ou mal copié | Régénérer le token via la page de gestion |
| `nopermissions` | Utilisateur du token sans `local/su_statboard_api:view` | Vérifier l'attribution du rôle manager |
| `servicenotavailable` | Service web global désactivé ou protocole REST désactivé | Activer dans `Services web > Vue d'ensemble` |
| `dml_*_exception` au cron | Problème transitoire de DB ou index unique violé | Vérifier les logs Moodle, relancer le cron |

### Vérification de la cohérence des tables résumé

```sql
-- Lignes attendues : <= 30
SELECT COUNT(*) FROM mdl_su_statboard_daily_stats;

-- Lignes attendues : <= 720
SELECT COUNT(*) FROM mdl_su_statboard_hourly_stats;

-- Dernière agrégation
SELECT MAX(timecreated) FROM mdl_su_statboard_daily_stats;
SELECT MAX(timecreated) FROM mdl_su_statboard_hourly_stats;
```

### Vidage manuel du cache MUC

Depuis l'interface : `Administration du site > Développement > Vidage des caches`.

Depuis le CLI :

```bash
php admin/cli/purge_caches.php
```

---

**Version du document** : 3.0
**Dernière mise à jour** : Avril 2026
