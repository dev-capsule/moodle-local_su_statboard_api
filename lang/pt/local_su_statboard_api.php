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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

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

$string['back_to_settings'] = 'Voltar às definições';
$string['cachedef_statboard_max'] = 'Máximo diário de ligações do dashboard (30 dias)';
$string['cachedef_statboard_quiz'] = 'Questionários concluídos hoje';
$string['cachedef_statboard_totals'] = 'Totais do dashboard (utilizadores, cursos)';
$string['copy_token'] = 'Copiar token';
$string['cron_info_desc'] = 'Este plugin utiliza duas tarefas agendadas. A tarefa diária é executada todas as noites às 00:05 e calcula o número de inícios de sessão distintos do dia anterior, armazenando o resultado numa tabela de resumo. A tarefa horária é executada a cada hora às HH:01 e calcula um instantâneo de ligação para a hora anterior. Ambas as tarefas eliminam automaticamente as entradas obsoletas.';
$string['cron_info_heading'] = 'Tarefas agendadas';
$string['current_expiration'] = 'Expiração atual';
$string['current_token'] = 'Token atual';
$string['data_retention_desc'] = 'Os instantâneos horários de ligação são conservados durante 30 dias consecutivos (máximo 720 linhas). O gráfico do dashboard permite navegar nos últimos 30 dias através do seletor de data. As estatísticas diárias de ligações máximas também são conservadas durante 30 dias.';
$string['data_retention_heading'] = 'Retenção de dados';
$string['enable_edit'] = 'Ativar edição';
$string['eventstatsviewed'] = 'Estatísticas visualizadas';
$string['expiration_date'] = 'Data de expiração';
$string['expiration_date_desc'] = 'Selecione uma data de expiração para o token.';
$string['expiration_date_past'] = 'A data de expiração não pode estar no passado';
$string['expiration_section'] = 'Definições de expiração';
$string['inconsistency_token_should_be_permanent'] = 'Inconsistência: a configuração indica "sem expiração" mas o token expira realmente em {$a}. Aceda às definições do token para corrigir.';
$string['inconsistency_token_should_expire'] = 'Inconsistência: a configuração indica que o token deve expirar mas está atualmente definido como permanente. Aceda às definições do token para corrigir.';
$string['manage_token'] = 'Gerir token';
$string['modify_expiration_date'] = 'Modificar data de expiração do token';
$string['modify_expiration_instructions'] = 'Selecione uma nova data de expiração para o token. A data selecionada será aplicada a todos os tokens deste serviço.';
$string['modify_token'] = 'Modificar token';
$string['modify_token_instructions'] = 'Utilize este formulário para modificar o token do serviço web.';
$string['modify_validity_period'] = 'Modificar período de validade';
$string['modify_validity_period_instructions'] = 'Utilize o formulário abaixo para modificar o período de validade do token. O valor é expresso em dias.';
$string['new_token'] = 'Novo token';
$string['no_expiration'] = 'Token sem expiração';
$string['no_expiration_desc'] = 'Se ativado, o token nunca expira';
$string['no_expiration_label'] = 'Nunca fazer expirar este token';
$string['no_token'] = 'Nenhum token encontrado. Tente reinstalar o plugin.';
$string['no_token_configured'] = 'Nenhum token configurado. Reinstale o plugin.';
$string['no_tokens_found'] = 'Não foram encontrados tokens para este serviço. Reinstale o plugin.';
$string['pluginname'] = 'Statboard API';
$string['privacy:metadata:core_logging'] = 'O plugin regista as ações dos utilizadores através do sistema de registo do Moodle';
$string['privacy:metadata:external_tokens'] = 'Informação sobre os tokens de serviço web armazenados para aceder à API';
$string['privacy:metadata:external_tokens:token'] = 'O valor do token';
$string['privacy:metadata:external_tokens:userid'] = 'O ID do utilizador a quem o token pertence';
$string['privacy:metadata:external_tokens:validuntil'] = 'A data até à qual o token é válido';
$string['privacy:metadata:moodle_webservice'] = 'O plugin transmite dados externamente utilizando os serviços web do Moodle';
$string['privacy:metadata:moodle_webservice:token'] = 'O token do utilizador para autenticação com o serviço web';
$string['privacy:metadata:moodle_webservice:user_id'] = 'O ID do utilizador para autenticação com o serviço web';
$string['regenerate_token'] = 'Gerar novo token';
$string['save_changes'] = 'Guardar alterações';
$string['service_not_found'] = 'Serviço web não encontrado. Reinstale o plugin.';
$string['settings'] = 'Definições da Statboard API';
$string['su_statboard_api:managetokensettings'] = 'Gerir as definições do token da API';
$string['su_statboard_api:view'] = 'Ver estatísticas de utilização';
$string['su_token_admin'] = 'Token da API';
$string['task_aggregate_daily_stats'] = 'Agregação diária das estatísticas de início de sessão';
$string['task_aggregate_hourly_stats'] = 'Agregação horária de instantâneos de ligação';
$string['token'] = 'Token do serviço web';
$string['token_copied'] = 'Token copiado para a área de transferência!';
$string['token_desc'] = 'Utilize este token para aceder às estatísticas através da API';
$string['token_empty'] = 'O token não pode estar vazio';
$string['token_error'] = 'Erro ao atualizar o token';
$string['token_expiration_date'] = 'Data de expiração do token';
$string['token_expiration_date_desc'] = 'Selecione a data em que o token irá expirar. Esta data será aplicada a todos os tokens deste serviço.';
$string['token_expiration_disabled'] = 'A expiração do token foi desativada.';
$string['token_expiration_enabled'] = 'A expiração do token foi ativada.';
$string['token_expired'] = 'Token expirado';
$string['token_expires'] = 'Data de expiração';
$string['token_intro'] = 'Utilize este token para aceder à API de registos:';
$string['token_management'] = 'Gestão do token';
$string['token_management_desc'] = 'Utilize esta página para gerir as definições do token do serviço web.';
$string['token_no_expiration_info'] = 'O token está configurado para nunca expirar';
$string['token_not_found_db'] = 'Token não encontrado na base de dados. Reinstale o plugin.';
$string['token_page_title'] = 'Token da API para os registos do SU Statboard';
$string['token_placeholder'] = 'Introduza um novo token';
$string['token_regenerated'] = 'Token regenerado com sucesso';
$string['token_section'] = 'Token do serviço web';
$string['token_settings_title'] = 'Definições do token da API';
$string['token_update_exception'] = 'Erro ao atualizar o token';
$string['token_updated'] = 'Token atualizado com sucesso';
$string['token_valid'] = 'Token válido até';
$string['token_validity_period'] = 'Período de validade do token (dias)';
$string['token_validity_period_desc'] = 'Número de dias durante os quais o token será válido antes de expirar';
$string['update_expiration_date'] = 'Atualizar data de expiração';
$string['update_token'] = 'Atualizar token';
$string['update_validity_period'] = 'Atualizar período de validade';
$string['validity_days'] = 'Dias de validade';
$string['validity_period_error'] = 'Erro ao atualizar o período de validade';
$string['validity_period_invalid'] = 'O período de validade deve ser um número positivo';
$string['validity_period_updated'] = 'Período de validade atualizado com sucesso';
$string['view_token'] = 'Ver token da API';
