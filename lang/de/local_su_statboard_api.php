<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for local_su_statboard_api.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// General plugin strings.

$string['back_to_settings'] = 'Zurück zu den Einstellungen';
$string['cachedef_statboard_max'] = 'Maximale tägliche Dashboard-Verbindungen (30 Tage)';
$string['cachedef_statboard_quiz'] = 'Heute abgeschlossene Quizze';
$string['cachedef_statboard_totals'] = 'Dashboard-Gesamtwerte (Benutzer, Kurse)';
$string['copy_token'] = 'Token kopieren';
$string['cron_info_desc'] = 'Dieses Plugin verwendet zwei geplante Aufgaben. Die tägliche Aufgabe läuft jeden Abend um 00:05 Uhr und berechnet die Anzahl der eindeutigen Anmeldungen des Vortages. Das Ergebnis wird in einer Zusammenfassungstabelle gespeichert, sodass die API das Maximum über 30 Tage sofort lesen kann. Die stündliche Aufgabe läuft jede Stunde um HH:01 Uhr und berechnet einen Verbindungs-Snapshot für die vorherige Stunde. Beide Aufgaben bereinigen automatisch veraltete Einträge.';
$string['cron_info_heading'] = 'Geplante Aufgaben';
$string['current_expiration'] = 'Aktuelles Ablaufdatum';
$string['current_token'] = 'Aktuelles Token';
$string['data_retention_desc'] = 'Stündliche Verbindungs-Snapshots werden 30 Tage lang gespeichert (maximal 720 Zeilen). Das Dashboard-Diagramm ermöglicht die Navigation über die letzten 30 Tage über die Datumsauswahl. Tägliche maximale Verbindungsstatistiken werden ebenfalls 30 Tage lang gespeichert.';
$string['data_retention_heading'] = 'Datenspeicherung';
$string['enable_edit'] = 'Bearbeitung aktivieren';
$string['eventstatsviewed'] = 'Statistiken angesehen';
$string['expiration_date'] = 'Ablaufdatum';
$string['expiration_date_desc'] = 'Wählen Sie ein Ablaufdatum für das Token.';
$string['expiration_date_past'] = 'Das Ablaufdatum darf nicht in der Vergangenheit liegen';
$string['expiration_section'] = 'Ablauf-Einstellungen';
$string['inconsistency_token_should_be_permanent'] = 'Inkonsistenz: Die Konfiguration besagt „keine Ablauffrist", aber das Token läuft tatsächlich am {$a} ab. Bitte korrigieren Sie dies in den Token-Einstellungen.';
$string['inconsistency_token_should_expire'] = 'Inkonsistenz: Die Konfiguration besagt, dass das Token ablaufen soll, aber es ist derzeit als permanent festgelegt. Bitte korrigieren Sie dies in den Token-Einstellungen.';
$string['manage_token'] = 'Token verwalten';
$string['modify_expiration_date'] = 'Ablaufdatum des Tokens ändern';
$string['modify_expiration_instructions'] = 'Wählen Sie ein neues Ablaufdatum für das Token. Das ausgewählte Datum wird auf alle Tokens dieses Dienstes angewendet.';
$string['modify_token'] = 'Token ändern';
$string['modify_token_instructions'] = 'Verwenden Sie dieses Formular, um das Webservice-Token zu ändern.';
$string['modify_validity_period'] = 'Gültigkeitsdauer ändern';
$string['modify_validity_period_instructions'] = 'Verwenden Sie das folgende Formular, um die Gültigkeitsdauer des Tokens zu ändern. Der Wert wird in Tagen angegeben.';
$string['new_token'] = 'Neues Token';
$string['no_expiration'] = 'Token ohne Ablauf';
$string['no_expiration_desc'] = 'Wenn aktiviert, läuft das Token nie ab';
$string['no_expiration_label'] = 'Dieses Token nie ablaufen lassen';
$string['no_token'] = 'Kein Token gefunden. Versuchen Sie, das Plugin neu zu installieren.';
$string['no_token_configured'] = 'Kein Token konfiguriert. Bitte installieren Sie das Plugin neu.';
$string['no_tokens_found'] = 'Keine Tokens für diesen Dienst gefunden. Bitte installieren Sie das Plugin neu.';
$string['pluginname'] = 'Statboard API';
$string['privacy:metadata:core_logging'] = 'Das Plugin protokolliert Benutzeraktionen über das Moodle-Protokollierungssystem';
$string['privacy:metadata:external_tokens'] = 'Informationen über die gespeicherten Webservice-Tokens für den Zugriff auf die API';
$string['privacy:metadata:external_tokens:token'] = 'Der Token-Wert';
$string['privacy:metadata:external_tokens:userid'] = 'Die ID des Benutzers, zu dem das Token gehört';
$string['privacy:metadata:external_tokens:validuntil'] = 'Das Datum, bis zu dem das Token gültig ist';
$string['privacy:metadata:moodle_webservice'] = 'Das Plugin überträgt Daten extern über Moodle-Webservices';
$string['privacy:metadata:moodle_webservice:token'] = 'Das Token des Benutzers zur Authentifizierung beim Webservice';
$string['privacy:metadata:moodle_webservice:user_id'] = 'Die Benutzer-ID zur Authentifizierung beim Webservice';
$string['regenerate_token'] = 'Neues Token generieren';
$string['save_changes'] = 'Änderungen speichern';
$string['service_not_found'] = 'Webservice nicht gefunden. Bitte installieren Sie das Plugin neu.';
$string['settings'] = 'Statboard-API-Einstellungen';
$string['su_statboard_api:managetokensettings'] = 'API-Token-Einstellungen verwalten';
$string['su_statboard_api:view'] = 'Nutzungsstatistiken anzeigen';
$string['su_token_admin'] = 'API-Token';
$string['task_aggregate_daily_stats'] = 'Tägliche Aggregation der Anmeldestatistiken';
$string['task_aggregate_hourly_stats'] = 'Stündliche Aggregation der Verbindungs-Snapshots';
$string['token'] = 'Webservice-Token';
$string['token_copied'] = 'Token in die Zwischenablage kopiert!';
$string['token_desc'] = 'Verwenden Sie dieses Token, um über die API auf Statistiken zuzugreifen';
$string['token_empty'] = 'Das Token darf nicht leer sein';
$string['token_error'] = 'Fehler beim Aktualisieren des Tokens';
$string['token_expiration_date'] = 'Ablaufdatum des Tokens';
$string['token_expiration_date_desc'] = 'Wählen Sie das Datum, an dem das Token abläuft. Dieses Datum wird auf alle Tokens dieses Dienstes angewendet.';
$string['token_expiration_disabled'] = 'Der Token-Ablauf wurde deaktiviert.';
$string['token_expiration_enabled'] = 'Der Token-Ablauf wurde aktiviert.';
$string['token_expired'] = 'Token abgelaufen';
$string['token_expires'] = 'Ablaufdatum';
$string['token_intro'] = 'Verwenden Sie dieses Token, um auf die Protokoll-API zuzugreifen:';
$string['token_management'] = 'Token-Verwaltung';
$string['token_management_desc'] = 'Verwenden Sie diese Seite, um die Einstellungen des Webservice-Tokens zu verwalten.';
$string['token_no_expiration_info'] = 'Das Token ist so konfiguriert, dass es nie abläuft';
$string['token_not_found_db'] = 'Token nicht in der Datenbank gefunden. Bitte installieren Sie das Plugin neu.';
$string['token_page_title'] = 'API-Token für SU-Statboard-Protokolle';
$string['token_placeholder'] = 'Neues Token eingeben';
$string['token_regenerated'] = 'Token erfolgreich neu generiert';
$string['token_section'] = 'Webservice-Token';
$string['token_settings_title'] = 'API-Token-Einstellungen';
$string['token_update_exception'] = 'Fehler beim Aktualisieren des Tokens';
$string['token_updated'] = 'Token erfolgreich aktualisiert';
$string['token_valid'] = 'Token gültig bis';
$string['token_validity_period'] = 'Gültigkeitsdauer des Tokens (Tage)';
$string['token_validity_period_desc'] = 'Anzahl der Tage, die das Token vor dem Ablauf gültig ist';
$string['update_expiration_date'] = 'Ablaufdatum aktualisieren';
$string['update_token'] = 'Token aktualisieren';
$string['update_validity_period'] = 'Gültigkeitsdauer aktualisieren';
$string['validity_days'] = 'Gültigkeitstage';
$string['validity_period_error'] = 'Fehler beim Aktualisieren der Gültigkeitsdauer';
$string['validity_period_invalid'] = 'Die Gültigkeitsdauer muss eine positive Zahl sein';
$string['validity_period_updated'] = 'Gültigkeitsdauer erfolgreich aktualisiert';
$string['view_token'] = 'API-Token anzeigen';
