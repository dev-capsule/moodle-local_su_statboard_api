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

$string['back_to_settings'] = 'Volver a la configuración';
$string['cachedef_statboard_max'] = 'Máximo diario de conexiones del panel (30 días)';
$string['cachedef_statboard_quiz'] = 'Cuestionarios completados hoy';
$string['cachedef_statboard_totals'] = 'Totales del panel (usuarios, cursos)';
$string['copy_token'] = 'Copiar token';
$string['cron_info_desc'] = 'Este plugin utiliza dos tareas programadas. La tarea diaria se ejecuta cada noche a las 00:05 y calcula el número de inicios de sesión únicos del día anterior, almacenando el resultado en una tabla de resumen para que la API pueda leer el máximo de 30 días al instante. La tarea horaria se ejecuta cada hora a las HH:01 y calcula una instantánea de conexión para la hora anterior. Ambas tareas eliminan automáticamente las entradas obsoletas.';
$string['cron_info_heading'] = 'Tareas programadas';
$string['current_expiration'] = 'Expiración actual';
$string['current_token'] = 'Token actual';
$string['data_retention_desc'] = 'Los instantáneos horarios de conexión se conservan durante 30 días consecutivos (máximo 720 filas). El gráfico del panel permite navegar por los últimos 30 días mediante el selector de fecha. Las estadísticas diarias de conexiones máximas también se conservan durante 30 días.';
$string['data_retention_heading'] = 'Retención de datos';
$string['enable_edit'] = 'Habilitar edición';
$string['eventstatsviewed'] = 'Estadísticas consultadas';
$string['expiration_date'] = 'Fecha de expiración';
$string['expiration_date_desc'] = 'Seleccione una fecha de expiración para el token.';
$string['expiration_date_past'] = 'La fecha de expiración no puede estar en el pasado';
$string['expiration_section'] = 'Configuración de expiración';
$string['inconsistency_token_should_be_permanent'] = 'Incoherencia: la configuración dice "sin caducidad" pero el token caduca realmente el {$a}. Vaya a la configuración del token para corregirlo.';
$string['inconsistency_token_should_expire'] = 'Incoherencia: la configuración dice que el token debe caducar pero está configurado como permanente. Vaya a la configuración del token para corregirlo.';
$string['manage_token'] = 'Gestionar token';
$string['modify_expiration_date'] = 'Modificar la fecha de expiración del token';
$string['modify_expiration_instructions'] = 'Seleccione una nueva fecha de expiración para el token. La fecha seleccionada se aplicará a todos los tokens de este servicio.';
$string['modify_token'] = 'Modificar token';
$string['modify_token_instructions'] = 'Utilice este formulario para modificar el token del servicio web.';
$string['modify_validity_period'] = 'Modificar período de validez';
$string['modify_validity_period_instructions'] = 'Utilice el siguiente formulario para modificar el período de validez del token. El valor se expresa en días.';
$string['new_token'] = 'Nuevo token';
$string['no_expiration'] = 'Token sin expiración';
$string['no_expiration_desc'] = 'Si está habilitado, el token no expirará nunca';
$string['no_expiration_label'] = 'No hacer que este token expire nunca';
$string['no_token'] = 'No se encontró ningún token. Intente reinstalar el plugin.';
$string['no_token_configured'] = 'No hay ningún token configurado. Reinstale el plugin.';
$string['no_tokens_found'] = 'No se encontraron tokens para este servicio. Reinstale el plugin.';
$string['pluginname'] = 'Statboard API';
$string['privacy:metadata:core_logging'] = 'El plugin registra las acciones de los usuarios mediante el sistema de registro de Moodle';
$string['privacy:metadata:external_tokens'] = 'Información sobre los tokens de servicio web almacenados para acceder a la API';
$string['privacy:metadata:external_tokens:token'] = 'El valor del token';
$string['privacy:metadata:external_tokens:userid'] = 'El ID del usuario al que pertenece el token';
$string['privacy:metadata:external_tokens:validuntil'] = 'La fecha hasta la cual el token es válido';
$string['privacy:metadata:moodle_webservice'] = 'El plugin transmite datos externamente utilizando los servicios web de Moodle';
$string['privacy:metadata:moodle_webservice:token'] = 'El token del usuario para la autenticación con el servicio web';
$string['privacy:metadata:moodle_webservice:user_id'] = 'El ID del usuario para la autenticación con el servicio web';
$string['regenerate_token'] = 'Generar nuevo token';
$string['save_changes'] = 'Guardar cambios';
$string['service_not_found'] = 'Servicio web no encontrado. Reinstale el plugin.';
$string['settings'] = 'Configuración de Statboard API';
$string['su_statboard_api:managetokensettings'] = 'Gestionar la configuración del token de API';
$string['su_statboard_api:view'] = 'Ver las estadísticas de uso';
$string['su_token_admin'] = 'Token de API';
$string['task_aggregate_daily_stats'] = 'Agregación diaria de estadísticas de inicio de sesión';
$string['task_aggregate_hourly_stats'] = 'Agregación horaria de instantáneas de conexión';
$string['token'] = 'Token del servicio web';
$string['token_copied'] = '¡Token copiado al portapapeles!';
$string['token_desc'] = 'Utilice este token para acceder a las estadísticas mediante la API';
$string['token_empty'] = 'El token no puede estar vacío';
$string['token_error'] = 'Error al actualizar el token';
$string['token_expiration_date'] = 'Fecha de expiración del token';
$string['token_expiration_date_desc'] = 'Seleccione la fecha en la que expirará el token. Esta fecha se aplicará a todos los tokens de este servicio.';
$string['token_expiration_disabled'] = 'La expiración del token ha sido deshabilitada.';
$string['token_expiration_enabled'] = 'La expiración del token ha sido habilitada.';
$string['token_expired'] = 'Token expirado';
$string['token_expires'] = 'Fecha de expiración';
$string['token_intro'] = 'Utilice este token para acceder a la API de registros:';
$string['token_management'] = 'Gestión del token';
$string['token_management_desc'] = 'Utilice esta página para gestionar la configuración del token del servicio web.';
$string['token_no_expiration_info'] = 'El token está configurado para no expirar nunca';
$string['token_not_found_db'] = 'Token no encontrado en la base de datos. Reinstale el plugin.';
$string['token_page_title'] = 'Token de API para los registros de SU Statboard';
$string['token_placeholder'] = 'Introducir nuevo token';
$string['token_regenerated'] = 'Token regenerado correctamente';
$string['token_section'] = 'Token del servicio web';
$string['token_settings_title'] = 'Configuración del token de API';
$string['token_update_exception'] = 'Error al actualizar el token';
$string['token_updated'] = 'Token actualizado correctamente';
$string['token_valid'] = 'Token válido hasta';
$string['token_validity_period'] = 'Período de validez del token (días)';
$string['token_validity_period_desc'] = 'Número de días que el token será válido antes de expirar';
$string['update_expiration_date'] = 'Actualizar fecha de expiración';
$string['update_token'] = 'Actualizar token';
$string['update_validity_period'] = 'Actualizar período de validez';
$string['validity_days'] = 'Días de validez';
$string['validity_period_error'] = 'Error al actualizar el período de validez';
$string['validity_period_invalid'] = 'El período de validez debe ser un número positivo';
$string['validity_period_updated'] = 'Período de validez actualizado correctamente';
$string['view_token'] = 'Ver token de API';
