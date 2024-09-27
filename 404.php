<?
// подключение файла обработки адресов urlrewrite.php
include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

// установка HTTP статуса 404
CHTTP::SetStatus("404 Not Found");
@define("ERROR_404","Y");
// скрывает боковую панель на странице
define("HIDE_SIDEBAR", true);

// подключение header.php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

// установка заголовка страницы
$APPLICATION->SetTitle("Страница не найдена");?>

    <! -- начало содержимого страницы 404.php -->
    <div class="bx-404-container">
        <div class="bx-404-block"><img src="<?=SITE_DIR?>images/404.png" alt=""></div>
        <div class="bx-404-text-block">Неправильно набран адрес, <br>или такой страницы на сайте больше не существует.</div>
        <div class="">Вернитесь на <a href="<?=SITE_DIR?>">главную</a> или воспользуйтесь картой сайта.</div>
    </div>
    <div class="map-columns row">
        <div class="col-sm-10 col-sm-offset-1">
            <div class="bx-maps-title">Карта сайта:</div>
        </div>
    </div>

    <div class="col-sm-offset-2 col-sm-4">
        <div class="bx-map-title"><i class="fa fa-leanpub"></i> Каталог</div>

    </div>

    <div class="col-sm-offset-1 col-sm-4">
        <div class="bx-map-title"><i class="fa fa-info-circle"></i> О магазине</div>
        // подключение компонента Карта сайта
        <?
        $APPLICATION->IncludeComponent(
            "bitrix:main.map",
            ".default",
            array(
                "CACHE_TYPE" => "A",
                "CACHE_TIME" => "36000000",
                "SET_TITLE" => "N",
                "LEVEL" => "3",
                "COL_NUM" => "2",
                "SHOW_DESCRIPTION" => "Y",
                "COMPONENT_TEMPLATE" => ".default"
            ),
            false
        );?>
    </div>
    <! -- окончание содержимого страницы 404.php -->

    <! --  подключение файла footer.php -->
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>