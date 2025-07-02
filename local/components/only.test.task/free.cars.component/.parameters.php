<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    'GROUPS' => [
        'USER_CARD' => [
            'NAME' => 'Параметры карточки пользователя',
        ],
    ],
    'PARAMETERS' => [
        'USER_ID' => [
            'NAME' => 'Идентификатор пользователя',
            'TYPE' => 'NUMBER',
            'PARENT' => 'USER_CARD',
        ],
        'SHOW_EMAIL' => [
            'NAME' => 'Показывать email',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
            'PARENT' => 'USER_CARD',
        ],
    ],
];
?>