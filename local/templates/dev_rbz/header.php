<?php if ( ! defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<!DOCTYPE html>
<html>
<head>
    <?php $APPLICATION->showHead(); ?>
    <title><?php $APPLICATION->showTitle(); ?></title>
    <?php
    use Bitrix\Main\Page\Asset;

    Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . '/assets/reset.css');

    // подключаем строки
    Asset::getInstance()->addString('<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">');
    Asset::getInstance()->addString('<link href="//fonts.googleapis.com/css?family=Monda" rel="stylesheet" type="text/css">');
    ?>
</head>
<body>
<div id="panel"><?php $APPLICATION->showPanel(); /* панель управления */ ?></div>
<header style="border: 1px solid black;">header</header>
<main>