<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    'WORD' => [
        'PARENT' => 'BASE',
        'NAME' => 'Слово для отображения',
        'TYPE' => 'STRING',
    ],
    'PERCENT_WORD_DISPLAY' => [
        'PARENT' => 'BASE',
        'NAME' => 'Процент показа слова',
        'TYPE' => 'STRING',
    ],
    'NUM_PLATES' => [
        'PARENT' => 'BASE',
        'NAME' => 'Количество плиток',
        'TYPE' => 'STRING',
    ],
    'NUM_PARTS_WORD' => [
        'PARENT' => 'BASE',
        'NAME' => 'Количество частей слова',
        'TYPE' => 'STRING',
    ],
    'SECRET_KEY' => [
        'PARENT' => 'BASE',
        'NAME' => 'Секретный ключ',
        'TYPE' => 'STRING',
    ]
];