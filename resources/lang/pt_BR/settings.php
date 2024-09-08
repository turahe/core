<?php
/*
 * This source code is the proprietary and confidential information of
 * Nur Wachid. You may not disclose, copy, distribute,
 *  or use this code without the express written permission of
 * Nur Wachid.
 *
 * Copyright (c) 2022-2023.
 *
 *
 */

return [
    'settings' => 'Configurações',
    'updated' => 'Configurações Atualizadas',
    'general_settings' => 'Configurações Gerais',
    'company_information' => 'Informações da Empresa',
    'update_user_account_info' => 'A atualização dessas configurações não afetará as configurações de sua conta de usuário, pois essas configurações são gerais. Atualize as mesmas configurações em sua conta de usuário se desejar atualizar essas opções.',
    'general' => 'Geral',
    'system' => 'Sistema',
    'system_email' => 'Conta de E-mail do Sistema',
    'system_email_configured' => 'Conta configurada por outro usuário',
    'system_email_info' => 'Selecione a conta de e-mail conectada à caixa de entrada que será usada para enviar e-mails relacionados ao sistema, como usuário atribuído ao contato, lembrete de vencimento da atividade, convites do usuário etc.',
    'choose_logo' => 'Escolha a logomarca',
    'date_format' => 'Formato de Data',
    'time_format' => 'Formato de Horário',
    'privacy_policy_info' => 'Se você não possui política de privacidade, pode configurar uma aqui, veja a política de privacidade no seguinte URL: :url',
    'phones' => [
        'require_calling_prefix' => 'Exigir prefixo de chamada em números de telefone',
        'require_calling_prefix_info' => 'A maioria das integrações de chamadas exige que os números de telefone estejam no formato E.164. A ativação dessa opção garantirá que nenhum número de telefone seja inserido sem um prefixo de chamada específico do país.',
    ],
    'recaptcha' => [
        'recaptcha' => 'reCaptcha',
        'site_key' => 'Chave do Site',
        'secret_key' => 'Chave Secreta',
        'ignored_ips' => 'Endereços IP Ignorados',
        'ignored_ips_info' => 'Digite os endereços IP separados por vírgula que você deseja que o reCaptcha ignore a validação.',
    ],
    'security' => [
        'security' => 'Segurança',
        'disable_password_forgot' => 'Desativar recurso de esquecimento de senha',
        'disable_password_forgot_info' => 'Quando ativado, o recurso de senha esquecida será desativado.',
        'block_bad_visitors' => 'Bloquear visitantes ruins',
        'block_bad_visitors_info' => 'Se ativado, uma lista de agentes de usuário, endereços IP e referenciadores inválidos será verificada para cada visitante convidado.',
    ],
    'first_day_of_week' => 'Semana Começa Em',
    'tools' => [
        'tools' => 'Ferramentas',
        'run' => 'Executar ferramenta',
        'executed' => 'Ação executada com sucesso',
        'json-language' => 'Gerar arquivos de idioma JSON',
        'clear-cache' => 'Limpar o cache do aplicativo',
        'storage-link' => 'Crie um link simbólico de "public/storage" para "storage/app/public"',
        'migrate' => 'Executar as migrações do banco de dados',
        'optimize' => 'Armazene em cache os arquivos de inicialização do aplicativo, como configuração e rotas.',
        'seed-mailables' => 'Distribua os modelos de e-mail do aplicativo',
    ],
    'integrations' => [
        'twilio' => [
            'create_app' => 'Criar Aplicativo',
            'disconnect' => 'Desconectar Integração',
            'number' => 'Selecione o número Twilio da sua conta que será usado para fazer e receber chamadas.',
            'app' => 'Crie um aplicativo que lidará com o início de novas chamadas e chamadas recebidas.',
        ],
    ],
    'translator' => [
        'translator' => 'Tradutor',
        'new_locale' => 'Nova Localidade',
        'create_new_locale' => 'Criar Nova Localidade',
        'locale_name' => 'Nome da Localidade',
        'changes_not_saved' => 'Você não salvou as traduções para este grupo, para evitar perder suas traduções, salve o grupo clicando no botão Salvar localizado na parte inferior da página.',
    ],
];
