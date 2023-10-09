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
    'create'           => 'Создать рабочий процесс',
    'workflows'        => 'Рабочие процессы',
    'title'            => 'Название',
    'description'      => 'Описание',
    'created'          => 'Рабочий процесс успешно создан',
    'updated'          => 'Рабочий процесс успешно изменен',
    'deleted'          => 'Рабочий процесс успешно удален',
    'when'             => 'Когда',
    'then'             => 'Затем',
    'field_change_to'  => 'На',
    'total_executions' => 'Выполнено: :total',
    'info'             => 'Инструмент рабочих процессов автоматизирует ваши процессы продаж. Внутренние процессы, которые можно автоматизировать, включают создание действий, отправку электронных писем, запуск HTTP-запросов и т. д.',
    'validation'       => [
        'invalid_webhook_url' => 'URL-адрес веб-хука не должен начинаться с «https://» или «http://».',
    ],
    'actions' => [
        'webhook'          => 'Триггер вебхука',
        'webhook_url_info' => 'Должен быть полным, действительным, общедоступным URL-адресом.',
    ],
    'fields' => [
        'with_header_name'  => 'С названием заголовка (необязательно)',
        'with_header_value' => 'Со значением заголовка (необязательно)',
        'for_owner'         => 'Кому: Ответственный',
        'dates'             => [
            'due_at'    => 'Срок в',
            'now'       => 'Со сроком исполнения: на данный момент',
            'in_1_day'  => 'Со сроком исполнения: в течение дня',
            'in_2_days' => 'Со сроком: через два дня',
            'in_3_days' => 'Со сроком: через три дня',
            'in_4_days' => 'Со сроком: через четыре дня',
            'in_5_days' => 'Со сроком: через пять дней',
        ],
    ],
];
