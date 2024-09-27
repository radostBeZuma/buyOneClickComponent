<?php

require_once($_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_before.php');

global $APPLICATION;

$APPLICATION->ShowAjaxHead();

$APPLICATION->IncludeComponent(
    'custom:buy.one.click',
    '.default',
    [
        'PRODUCT_ID' => intval($_REQUEST['PRODUCT_ID']),
        'PERSON_TYPE_ID' => 1,
        'AJAX_MODE'=> 'Y'
    ]
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

