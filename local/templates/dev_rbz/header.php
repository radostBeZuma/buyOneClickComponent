<?php

use Bitrix\Main\Page\Asset;

if ( ! defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;
?>

<!DOCTYPE html>
<html>
<head>
    <?php $APPLICATION->showHead(); ?>
    <title><?php $APPLICATION->showTitle(); ?></title>
    <?php


    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/assets/css/reset.css');
    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/assets/css/modal.css');

    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/assets/js/classes/modalCustom.js');

    Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/assets/js/main.js');

    // подключаем строки
    Asset::getInstance()->addString('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">');
    Asset::getInstance()->addString('<link href="//fonts.googleapis.com/css?family=Monda" rel="stylesheet" type="text/css">');
    ?>
</head>
<body>
<div id="panel"><?php $APPLICATION->showPanel(); /* панель управления */ ?></div>
<header style="border: 1px solid black;">header</header>
<main>