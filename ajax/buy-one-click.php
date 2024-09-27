<?php

require_once($_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_before.php');

global $APPLICATION;

$APPLICATION->ShowAjaxHead();

/** Что узнал из пет проекта */
/**
 * 1) Js ванильный не может вставлять по нормальному теги script,
 * они превращаются просто в html который не отрабатывает 
 * */

// сделать кнопку закрытия модалки и чтобы по оверлею уже закрывалось
// переходить к компоненту
// показать товар который покупает и сумму
// мб даже превью было классно показать

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

