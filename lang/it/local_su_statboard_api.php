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

$string['back_to_settings'] = 'Torna alle impostazioni';
$string['cachedef_statboard_hourly'] = 'Connessioni orarie della dashboard';
$string['cachedef_statboard_max'] = 'Massimo giornaliero di connessioni della dashboard (30 giorni)';
$string['cachedef_statboard_quiz'] = 'Quiz completati oggi';
$string['cachedef_statboard_totals'] = 'Totali della dashboard (utenti, corsi)';
$string['copy_token'] = 'Copia token';
$string['cron_info_desc'] = 'Questo plugin utilizza due attività pianificate. L\'attività giornaliera viene eseguita ogni notte alle 00:05 e calcola il numero di accessi distinti del giorno precedente, memorizzando il risultato in una tabella di riepilogo. L\'attività oraria viene eseguita ogni ora alle HH:01 e calcola uno snapshot di connessione per l\'ora precedente. Entrambe le attività eliminano automaticamente le voci obsolete.';
$string['cron_info_heading'] = 'Attività pianificate';
$string['current_expiration'] = 'Scadenza attuale';
$string['current_token'] = 'Token attuale';
$string['data_retention_desc'] = 'Gli snapshot orari di connessione vengono conservati per 30 giorni consecutivi (massimo 720 righe). Il grafico della dashboard consente la navigazione negli ultimi 30 giorni tramite il selettore di data. Anche le statistiche giornaliere delle connessioni massime vengono conservate per 30 giorni.';
$string['data_retention_heading'] = 'Conservazione dei dati';
$string['enable_edit'] = 'Abilita modifica';
$string['eventstatsviewed'] = 'Statistiche visualizzate';
$string['expiration_date'] = 'Data di scadenza';
$string['expiration_date_desc'] = 'Seleziona una data di scadenza per il token.';
$string['expiration_date_past'] = 'La data di scadenza non può essere nel passato';
$string['expiration_section'] = 'Impostazioni di scadenza';
$string['inconsistency_token_should_be_permanent'] = 'Incoerenza: la configurazione indica "nessuna scadenza" ma il token scade effettivamente il {$a}. Vai nelle impostazioni del token per correggere.';
$string['inconsistency_token_should_expire'] = 'Incoerenza: la configurazione indica che il token deve scadere ma è attualmente impostato come permanente. Vai nelle impostazioni del token per correggere.';
$string['manage_token'] = 'Gestisci token';
$string['modify_expiration_date'] = 'Modifica la data di scadenza del token';
$string['modify_expiration_instructions'] = 'Seleziona una nuova data di scadenza per il token. La data selezionata sarà applicata a tutti i token per questo servizio.';
$string['modify_token'] = 'Modifica token';
$string['modify_token_instructions'] = 'Usa questo modulo per modificare il token del servizio web.';
$string['modify_validity_period'] = 'Modifica periodo di validità';
$string['modify_validity_period_instructions'] = 'Usa il modulo qui sotto per modificare il periodo di validità del token. Il valore è espresso in giorni.';
$string['new_token'] = 'Nuovo token';
$string['no_expiration'] = 'Token senza scadenza';
$string['no_expiration_desc'] = 'Se abilitato, il token non scadrà mai';
$string['no_expiration_label'] = 'Non far scadere mai questo token';
$string['no_token'] = 'Nessun token trovato. Prova a reinstallare il plugin.';
$string['no_token_configured'] = 'Nessun token configurato. Reinstalla il plugin.';
$string['no_tokens_found'] = 'Nessun token trovato per questo servizio. Reinstalla il plugin.';
$string['pluginname'] = 'Statboard API';
$string['privacy:metadata:core_logging'] = 'Il plugin registra le azioni degli utenti tramite il sistema di logging di Moodle';
$string['privacy:metadata:external_tokens'] = 'Informazioni sui token del servizio web memorizzati per accedere all\'API';
$string['privacy:metadata:external_tokens:token'] = 'Il valore del token';
$string['privacy:metadata:external_tokens:userid'] = 'L\'ID dell\'utente a cui appartiene il token';
$string['privacy:metadata:external_tokens:validuntil'] = 'La data fino alla quale il token è valido';
$string['privacy:metadata:moodle_webservice'] = 'Il plugin trasmette dati esternamente utilizzando i servizi web di Moodle';
$string['privacy:metadata:moodle_webservice:token'] = 'Il token dell\'utente per l\'autenticazione con il servizio web';
$string['privacy:metadata:moodle_webservice:user_id'] = 'L\'ID utente per l\'autenticazione con il servizio web';
$string['regenerate_token'] = 'Genera un nuovo token';
$string['save_changes'] = 'Salva modifiche';
$string['service_not_found'] = 'Servizio web non trovato. Reinstalla il plugin.';
$string['settings'] = 'Impostazioni Statboard API';
$string['su_statboard_api:managetokensettings'] = 'Gestire le impostazioni del token API';
$string['su_statboard_api:view'] = 'Visualizzare le statistiche di utilizzo';
$string['su_token_admin'] = 'Token API';
$string['task_aggregate_daily_stats'] = 'Aggregazione giornaliera delle statistiche di accesso';
$string['task_aggregate_hourly_stats'] = 'Aggregazione oraria degli snapshot di connessione';
$string['token'] = 'Token del servizio web';
$string['token_copied'] = 'Token copiato negli appunti!';
$string['token_desc'] = 'Usa questo token per accedere alle statistiche tramite l\'API';
$string['token_empty'] = 'Il token non può essere vuoto';
$string['token_error'] = 'Errore durante l\'aggiornamento del token';
$string['token_expiration_date'] = 'Data di scadenza del token';
$string['token_expiration_date_desc'] = 'Seleziona la data in cui il token scadrà. Questa data sarà applicata a tutti i token per questo servizio.';
$string['token_expiration_disabled'] = 'La scadenza del token è stata disabilitata.';
$string['token_expiration_enabled'] = 'La scadenza del token è stata abilitata.';
$string['token_expired'] = 'Token scaduto';
$string['token_expires'] = 'Data di scadenza';
$string['token_intro'] = 'Usa questo token per accedere all\'API dei log:';
$string['token_management'] = 'Gestione del token';
$string['token_management_desc'] = 'Usa questa pagina per gestire le impostazioni del token del servizio web.';
$string['token_no_expiration_info'] = 'Il token è configurato per non scadere mai';
$string['token_not_found_db'] = 'Token non trovato nel database. Reinstalla il plugin.';
$string['token_page_title'] = 'Token API per i log di SU Statboard';
$string['token_placeholder'] = 'Inserisci un nuovo token';
$string['token_regenerated'] = 'Token rigenerato con successo';
$string['token_section'] = 'Token del servizio web';
$string['token_settings_title'] = 'Impostazioni del token API';
$string['token_update_exception'] = 'Errore durante l\'aggiornamento del token';
$string['token_updated'] = 'Token aggiornato con successo';
$string['token_valid'] = 'Token valido fino al';
$string['token_validity_period'] = 'Periodo di validità del token (giorni)';
$string['token_validity_period_desc'] = 'Numero di giorni in cui il token sarà valido prima della scadenza';
$string['update_expiration_date'] = 'Aggiorna data di scadenza';
$string['update_token'] = 'Aggiorna token';
$string['update_validity_period'] = 'Aggiorna periodo di validità';
$string['validity_days'] = 'Giorni di validità';
$string['validity_period_error'] = 'Errore durante l\'aggiornamento del periodo di validità';
$string['validity_period_invalid'] = 'Il periodo di validità deve essere un numero positivo';
$string['validity_period_updated'] = 'Periodo di validità aggiornato con successo';
$string['view_token'] = 'Visualizza il token API';
