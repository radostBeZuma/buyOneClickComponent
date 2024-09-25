<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

// проверяем установку модуля «Информационные блоки»
if (!CModule::IncludeModule('iblock')) {
    return;
}

// получаем массив всех типов инфоблоков для возможности выбора
$arIBlockType = CIBlockParameters::GetIBlockTypes();


// получаем массив инфоблоков
$arInfoBlocks = [];

$arFilterInfoBlocks = ['ACTIVE' => 'Y'];
$arOrderInfoBlocks = ['SORT' => 'ASC'];

if (!empty($arCurrentValues['IBLOCK_TYPE'])) {
    $arFilterInfoBlocks['TYPE'] = $arCurrentValues['IBLOCK_TYPE'];
}

$rsIBlock = CIBlock::GetList($arOrderInfoBlocks, $arFilterInfoBlocks);

while ($obIBlock = $rsIBlock->Fetch()) {
    $arInfoBlocks[$obIBlock['ID']] = '[' . $obIBlock['ID'] . '] ' . $obIBlock['NAME'];
}

// получаем список свойств
$arOrderProperties = ['SORT' => 'ASC'];
$arFilterProperties = ['ACTIVE' => 'Y'];

if (!empty($arCurrentValues['IBLOCK_ID'])) {
    $arFilterProperties['IBLOCK_ID'] = $arCurrentValues['IBLOCK_ID'];
}

$rsProperties = CIBlockProperty::GetList($arOrderProperties, $arFilterProperties);

while ($obProperty = $rsProperties->Fetch()) {
    $arProperties[$obProperty['ID']] = '[' . $obProperty['ID'] . '] ' . $obProperty['NAME'];
}



// настройки компонента, формируем массив $arParams
$arComponentParameters = [
    // основной массив с параметрами
    'PARAMETERS' => [
        // выбор типа инфоблока
        'IBLOCK_TYPE' => [                          // ключ массива $arParams в component.php
            'PARENT' => 'BASE',                     // название группы
            'NAME' => 'Выберите тип инфоблока',     // название параметра
            'TYPE' => 'LIST',                       // тип элемента управления, в котором будет устанавливаться параметр
            'VALUES' => $arIBlockType,              // входные значения
            'REFRESH' => 'Y',                       // перегружать настройки или нет после выбора (N/Y)
            'DEFAULT' => '',                        // значение по умолчанию
            'MULTIPLE' => 'N',                      // одиночное/множественное значение (N/Y)
        ],
        // выбор самого инфоблока
        'IBLOCK_ID' => [
            'PARENT' => 'BASE',
            'NAME' => 'Выберите родительский инфоблок',
            'TYPE' => 'LIST',
            'VALUES' => $arInfoBlocks,
            'REFRESH' => 'Y',
            "DEFAULT" => '',
            "ADDITIONAL_VALUES" => "Y",
        ],
        // выбор свойства
        'PROP_FOR_MENU' => [
            'PARENT' => 'BASE',
            'NAME' => 'Выберите свойство, по которму будет проводится отбор',
            'TYPE' => 'LIST',
            'VALUES' => $arProperties,
            'REFRESH' => 'Y',
            "DEFAULT" => '',
            "ADDITIONAL_VALUES" => "Y",
        ],
        'DATA_ARRAY' => [
            'PARENT' => 'BASE',
            'NAME' => 'Массив зависимости',
            'TYPE' => 'STRING',
        ]

        // настройки кэширования
        /*'CACHE_TIME' => [
            'DEFAULT' => 3600
        ],*/
    ],
];