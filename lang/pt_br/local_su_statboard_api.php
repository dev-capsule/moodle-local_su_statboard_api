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

$string['back_to_settings'] = 'Voltar para as configurações';
$string['cachedef_statboard_max'] = 'Máximo diário de conexões do dashboard (30 dias)';
$string['cachedef_statboard_quiz'] = 'Questionários concluídos hoje';
$string['cachedef_statboard_totals'] = 'Totais do dashboard (usuários, cursos)';
$string['copy_token'] = 'Copiar token';
$string['cron_info_desc'] = 'Este plugin utiliza duas tarefas agendadas. A tarefa diária é executada todas as noites às 00:05 e calcula o número de logins distintos do dia anterior, armazenando o resultado em uma tabela de resumo. A tarefa horária é executada a cada hora às HH:01 e calcula um instantâneo de conexão para a hora anterior. Ambas as tarefas removem automaticamente as entradas obsoletas.';
$string['cron_info_heading'] = 'Tarefas agendadas';
$string['current_expiration'] = 'Expiração atual';
$string['current_token'] = 'Token atual';
$string['data_retention_desc'] = 'Os instantâneos horários de conexão são retidos por 30 dias consecutivos (máximo 720 linhas). O gráfico do dashboard permite navegar nos últimos 30 dias através do seletor de data. As estatísticas diárias de conexões máximas também são retidas por 30 dias.';
$string['data_retention_heading'] = 'Retenção de dados';
$string['enable_edit'] = 'Habilitar edição';
$string['eventstatsviewed'] = 'Estatísticas visualizadas';
$string['expiration_date'] = 'Data de expiração';
$string['expiration_date_desc'] = 'Selecione uma data de expiração para o token.';
$string['expiration_date_past'] = 'A data de expiração não pode estar no passado';
$string['expiration_section'] = 'Configurações de expiração';
$string['inconsistency_token_should_be_permanent'] = 'Inconsistência: a configuração diz "sem expiração" mas o token na verdade expira em {$a}. Vá nas configurações do token para corrigir.';
$string['inconsistency_token_should_expire'] = 'Inconsistência: a configuração diz que o token deve expirar mas está atualmente definido como permanente. Vá nas configurações do token para corrigir.';
$string['manage_token'] = 'Gerenciar token';
$string['modify_expiration_date'] = 'Modificar data de expiração do token';
$string['modify_expiration_instructions'] = 'Selecione uma nova data de expiração para o token. A data selecionada será aplicada a todos os tokens deste serviço.';
$string['modify_token'] = 'Modificar token';
$string['modify_token_instructions'] = 'Use este formulário para modificar o token do serviço web.';
$string['modify_validity_period'] = 'Modificar período de validade';
$string['modify_validity_period_instructions'] = 'Use o formulário abaixo para modificar o período de validade do token. O valor é expresso em dias.';
$string['new_token'] = 'Novo token';
$string['no_expiration'] = 'Token sem expiração';
$string['no_expiration_desc'] = 'Se habilitado, o token nunca expirará';
$string['no_expiration_label'] = 'Nunca expirar este token';
$string['no_token'] = 'Nenhum token encontrado. Tente reinstalar o plugin.';
$string['no_token_configured'] = 'Nenhum token configurado. Reinstale o plugin.';
$string['no_tokens_found'] = 'Nenhum token encontrado para este serviço. Reinstale o plugin.';
$string['pluginname'] = 'Statboard API';
$string['privacy:metadata:core_logging'] = 'O plugin registra as ações dos usuários por meio do sistema de logs do Moodle';
$string['privacy:metadata:external_tokens'] = 'Informações sobre os tokens de serviço web armazenados para acessar a API';
$string['privacy:metadata:external_tokens:token'] = 'O valor do token';
$string['privacy:metadata:external_tokens:userid'] = 'O ID do usuário ao qual o token pertence';
$string['privacy:metadata:external_tokens:validuntil'] = 'A data até a qual o token é válido';
$string['privacy:metadata:moodle_webservice'] = 'O plugin transmite dados externamente usando os serviços web do Moodle';
$string['privacy:metadata:moodle_webservice:token'] = 'O token do usuário para autenticação com o serviço web';
$string['privacy:metadata:moodle_webservice:user_id'] = 'O ID do usuário para autenticação com o serviço web';
$string['regenerate_token'] = 'Gerar novo token';
$string['save_changes'] = 'Salvar alterações';
$string['service_not_found'] = 'Serviço web não encontrado. Reinstale o plugin.';
$string['settings'] = 'Configurações da Statboard API';
$string['su_statboard_api:managetokensettings'] = 'Gerenciar as configurações do token da API';
$string['su_statboard_api:view'] = 'Ver estatísticas de uso';
$string['su_token_admin'] = 'Token da API';
$string['task_aggregate_daily_stats'] = 'Agregação diária das estatísticas de login';
$string['task_aggregate_hourly_stats'] = 'Agregação horária de instantâneos de conexão';
$string['token'] = 'Token do serviço web';
$string['token_copied'] = 'Token copiado para a área de transferência!';
$string['token_desc'] = 'Use este token para acessar estatísticas por meio da API';
$string['token_empty'] = 'O token não pode estar vazio';
$string['token_error'] = 'Erro ao atualizar o token';
$string['token_expiration_date'] = 'Data de expiração do token';
$string['token_expiration_date_desc'] = 'Selecione a data em que o token irá expirar. Essa data será aplicada a todos os tokens deste serviço.';
$string['token_expiration_disabled'] = 'A expiração do token foi desabilitada.';
$string['token_expiration_enabled'] = 'A expiração do token foi habilitada.';
$string['token_expired'] = 'Token expirado';
$string['token_expires'] = 'Data de expiração';
$string['token_intro'] = 'Use este token para acessar a API de logs:';
$string['token_management'] = 'Gerenciamento do token';
$string['token_management_desc'] = 'Use esta página para gerenciar as configurações do token do serviço web.';
$string['token_no_expiration_info'] = 'O token está configurado para nunca expirar';
$string['token_not_found_db'] = 'Token não encontrado no banco de dados. Reinstale o plugin.';
$string['token_page_title'] = 'Token da API para os logs do SU Statboard';
$string['token_placeholder'] = 'Insira um novo token';
$string['token_regenerated'] = 'Token regenerado com sucesso';
$string['token_section'] = 'Token do serviço web';
$string['token_settings_title'] = 'Configurações do token da API';
$string['token_update_exception'] = 'Erro ao atualizar o token';
$string['token_updated'] = 'Token atualizado com sucesso';
$string['token_valid'] = 'Token válido até';
$string['token_validity_period'] = 'Período de validade do token (dias)';
$string['token_validity_period_desc'] = 'Número de dias em que o token será válido antes de expirar';
$string['update_expiration_date'] = 'Atualizar data de expiração';
$string['update_token'] = 'Atualizar token';
$string['update_validity_period'] = 'Atualizar período de validade';
$string['validity_days'] = 'Dias de validade';
$string['validity_period_error'] = 'Erro ao atualizar o período de validade';
$string['validity_period_invalid'] = 'O período de validade deve ser um número positivo';
$string['validity_period_updated'] = 'Período de validade atualizado com sucesso';
$string['view_token'] = 'Ver token da API';
