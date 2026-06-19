<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Language strings for local_su_statboard_api.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// General plugin strings.

$string['back_to_settings'] = 'Retour aux paramètres';
$string['cachedef_statboard_max'] = 'Maximum de connexions journalières (30 jours)';
$string['cachedef_statboard_quiz'] = 'Quiz terminés aujourd\'hui';
$string['cachedef_statboard_totals'] = 'Totaux du tableau de bord (utilisateurs, cours)';
$string['copy_token'] = 'Copier le jeton';
$string['cron_info_desc'] = 'Ce plugin utilise deux tâches planifiées. La tâche quotidienne s\'exécute chaque nuit à 00h05 et calcule le nombre de connexions distinctes de la veille, en stockant le résultat dans une table de résumé pour que l\'API puisse lire instantanément le maximum sur 30 jours. La tâche horaire s\'exécute toutes les heures à HH:01 et calcule un snapshot de connexion pour l\'heure précédente (utilisateurs actifs dans les 5 minutes précédant chaque heure). Les deux tâches purgent automatiquement les entrées obsolètes.';
$string['cron_info_heading'] = 'Tâches planifiées';
$string['current_expiration'] = 'Expiration actuelle';
$string['current_token'] = 'Jeton actuel';
$string['data_retention_desc'] = 'Les snapshots horaires de connexion sont conservés 30 jours glissants (720 lignes maximum). Le graphique du dashboard permet de naviguer sur les 30 derniers jours via le sélecteur de date. Les statistiques journalières de connexion maximale sont également conservées sur 30 jours.';
$string['data_retention_heading'] = 'Rétention des données';
$string['enable_edit'] = 'Activer l\'édition';
$string['eventstatsviewed'] = 'Statistiques consultées';
$string['expiration_date'] = 'Date d\'expiration';
$string['expiration_date_desc'] = 'Sélectionnez une date d\'expiration pour le jeton.';
$string['expiration_date_past'] = 'La date d\'expiration ne peut pas être dans le passé';
$string['expiration_section'] = 'Paramètres d\'expiration';
$string['inconsistency_token_should_be_permanent'] = 'Incohérence : la configuration indique « pas d\'expiration » mais le jeton expire en réalité le {$a}. Rendez-vous dans les paramètres du jeton pour corriger.';
$string['inconsistency_token_should_expire'] = 'Incohérence : la configuration indique que le jeton doit expirer mais il est actuellement défini comme permanent. Rendez-vous dans les paramètres du jeton pour corriger.';
$string['manage_token'] = 'Gérer le jeton';
$string['modify_expiration_date'] = 'Modifier la date d\'expiration du jeton';
$string['modify_expiration_instructions'] = 'Sélectionnez une nouvelle date d\'expiration pour le jeton. Cette date sera appliquée à tous les jetons de ce service.';
$string['modify_token'] = 'Modifier le jeton';
$string['modify_token_instructions'] = 'Utilisez ce formulaire pour modifier le jeton du service web.';
$string['modify_validity_period'] = 'Modifier la période de validité';
$string['modify_validity_period_instructions'] = 'Utilisez le formulaire ci-dessous pour modifier la période de validité du jeton. La valeur est exprimée en jours.';
$string['new_token'] = 'Nouveau jeton';
$string['no_expiration'] = 'Jeton sans expiration';
$string['no_expiration_desc'] = 'Si activé, le jeton n\'expirera jamais';
$string['no_expiration_label'] = 'Ne jamais faire expirer ce jeton';
$string['no_token'] = 'Aucun jeton trouvé. Essayez de réinstaller le plugin.';
$string['no_token_configured'] = 'Aucun jeton configuré. Veuillez réinstaller le plugin.';
$string['no_tokens_found'] = 'Aucun jeton trouvé pour ce service. Veuillez réinstaller le plugin.';
$string['pluginname'] = 'Statboard API';
$string['privacy:metadata:core_logging'] = 'Le plugin journalise les actions des utilisateurs via le système de logs de Moodle';
$string['privacy:metadata:external_tokens'] = 'Informations sur les jetons de service web stockés pour accéder à l\'API';
$string['privacy:metadata:external_tokens:token'] = 'La valeur du jeton';
$string['privacy:metadata:external_tokens:userid'] = 'L\'identifiant de l\'utilisateur auquel appartient le jeton';
$string['privacy:metadata:external_tokens:validuntil'] = 'La date jusqu\'à laquelle le jeton est valide';
$string['privacy:metadata:moodle_webservice'] = 'Le plugin transmet des données en externe via les services web de Moodle';
$string['privacy:metadata:moodle_webservice:token'] = 'Le jeton de l\'utilisateur pour l\'authentification au service web';
$string['privacy:metadata:moodle_webservice:user_id'] = 'L\'identifiant utilisateur pour l\'authentification au service web';
$string['regenerate_token'] = 'Générer un nouveau jeton';
$string['save_changes'] = 'Enregistrer les modifications';
$string['service_not_found'] = 'Service web introuvable. Veuillez réinstaller le plugin.';
$string['settings'] = 'Paramètres de l\'API Statboard';
$string['su_statboard_api:managetokensettings'] = 'Gérer les paramètres du jeton API';
$string['su_statboard_api:view'] = 'Voir les statistiques d\'utilisation';
$string['su_token_admin'] = 'Jeton API';
$string['task_aggregate_daily_stats'] = 'Agrégation quotidienne des statistiques de connexion';
$string['task_aggregate_hourly_stats'] = 'Agrégation horaire des snapshots de connexion';
$string['token'] = 'Jeton du service web';
$string['token_copied'] = 'Jeton copié dans le presse-papiers !';
$string['token_desc'] = 'Utilisez ce jeton pour accéder aux statistiques via l\'API';
$string['token_empty'] = 'Le jeton ne peut pas être vide';
$string['token_error'] = 'Erreur lors de la mise à jour du jeton';
$string['token_expiration_date'] = 'Date d\'expiration du jeton';
$string['token_expiration_date_desc'] = 'Sélectionnez la date à laquelle le jeton expirera. Cette date sera appliquée à tous les jetons de ce service.';
$string['token_expiration_disabled'] = 'Expiration du jeton désactivée.';
$string['token_expiration_enabled'] = 'Expiration du jeton activée.';
$string['token_expired'] = 'Jeton expiré';
$string['token_expires'] = 'Date d\'expiration';
$string['token_intro'] = 'Utilisez ce jeton pour accéder à l\'API des journaux :';
$string['token_management'] = 'Gestion des jetons';
$string['token_management_desc'] = 'Utilisez cette page pour gérer les paramètres du jeton du service web.';
$string['token_no_expiration_info'] = 'Le jeton est configuré pour ne jamais expirer';
$string['token_not_found_db'] = 'Jeton introuvable en base de données. Veuillez réinstaller le plugin.';
$string['token_page_title'] = 'Jeton API pour les journaux du tableau de bord SU Statboard';
$string['token_placeholder'] = 'Saisir un nouveau jeton';
$string['token_regenerated'] = 'Jeton régénéré avec succès';
$string['token_section'] = 'Jeton du service web';
$string['token_settings_title'] = 'Paramètres du jeton API';
$string['token_update_exception'] = 'Erreur lors de la mise à jour du jeton';
$string['token_updated'] = 'Jeton mis à jour avec succès';
$string['token_valid'] = 'Jeton valide jusqu\'au';
$string['token_validity_period'] = 'Période de validité du jeton (jours)';
$string['token_validity_period_desc'] = 'Nombre de jours pendant lesquels le jeton sera valide avant expiration';
$string['update_expiration_date'] = 'Mettre à jour la date d\'expiration';
$string['update_token'] = 'Mettre à jour le jeton';
$string['update_validity_period'] = 'Mettre à jour la période de validité';
$string['validity_days'] = 'Jours de validité';
$string['validity_period_error'] = 'Erreur lors de la mise à jour de la période de validité';
$string['validity_period_invalid'] = 'La période de validité doit être un nombre positif';
$string['validity_period_updated'] = 'Période de validité mise à jour avec succès';
$string['view_token'] = 'Voir le jeton API';
