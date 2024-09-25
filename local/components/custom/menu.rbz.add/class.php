<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {die();}


use \Bitrix\Main\Error;
use \Bitrix\Main\ErrorCollection;

class CMenuRbzAdd extends CBitrixComponent
{
    protected $errorCollection;

    // если в компоненте используется контруктор,
    // тогда необходимо также вызвать родительский конруктор
    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->errorCollection = new ErrorCollection();
    }

    // В функции onPrepareComponentParams() принято обрабатывать массив $arParams
    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    public function getDataBD($IBlock)
    {

        // айблок есть вообще

        // если есть то собираем данные

        $dbRes = CIBlockElement::GetList();

        // если нет то собираем ошибки

        $this->errorCollection->setError(
            new Error(
                'SOMETHING_IS_WRONG',
                // Добавьте ERROR_CODE, чтобы задать тип ошибки и обработать ошибку этого типа по своему усмотрению
                'test'
            )
        );

    }

    // executeComponent() выполняет основной код компонента в котором происходит заполнение массива $this->arResult
    public function executeComponent()
    {
        \CModule::IncludeModule("iblock");

        $dataArray = unserialize($this->arParams['~DATA_ARRAY']);

        $filteredList = $this->parseArData($dataArray);

        $dataSectionsElements = $this->getSectionsAndElements($filteredList);

        $this->buildADependency($dataArray, $dataSectionsElements);

        $this->arResult = $dataArray;

        $this->includeComponentTemplate();
    }
    protected function buildADependency(&$arEnum, $arSearch, $lvl = 1)
    {
        foreach ($arEnum as &$item) {
            $idItem = $item['ID'];

            if ($item['TYPE'] === 'S') {
                $nameFilter = 'SECTIONS';
                $url = 'SECTION_PAGE_URL';
            } else {
                $nameFilter = 'ELEMENTS';
                $url = 'DETAIL_PAGE_URL';
            }

            if (
                !empty($arSearch[$nameFilter][$idItem])
                && is_array($arSearch[$nameFilter][$idItem])
            ) {
                $item['NAME'] = $arSearch[$nameFilter][$idItem]['NAME'];
                $item['URL'] = $arSearch[$nameFilter][$idItem][$url];
            }

            $item['LVL'] = $lvl;

            if (
                !empty($item['CHILD'])
                && is_array($item['CHILD'])
            ) {
                $this->buildADependency($item['CHILD'], $arSearch, $lvl + 1);
            }

        }

    }

    // перебираю весь массив
    protected function processElements($array, &$result)
    {
        foreach ($array as $item) {
            if ($item['TYPE'] == 'S') {
                $result['SECTIONS']['ID'][] = $item['ID'];
                $result['SECTIONS']['IBLOCKS_ID'][] = $item['IBLOCK_ID'];
            } else {
                $result['ELEMENTS']['ID'][] = $item['ID'];
                $result['ELEMENTS']['IBLOCKS_ID'][] = $item['IBLOCK_ID'];
            }

            if (
                !empty($item['CHILD'])
                && is_array($item['CHILD'])
            ) {
                $this->processElements($item['CHILD'], $result);
            }
        }
    }

    protected function getArrayDepth($array) {
        $max_depth = 1;

        foreach ($array as $value) {
            if (is_array($value)) {
                $depth = $this->getArrayDepth($value) + 1;

                if ($depth > $max_depth) {
                    $max_depth = $depth;
                }
            }
        }

        echo $max_depth;

        return $max_depth;
    }

    // формирую данные для фильтра поиска по бд
    protected function parseArData($data)
    {
        $result = [];

        $this->processElements($data, $result);

        // cгенерировать ошибку пустого парсинга

        // сделать уникальными айди инфоблоков в массиве элементов и разделов
        foreach ($result as &$item) {
            if (isset($item['IBLOCKS_ID']) && is_array($item['IBLOCKS_ID'])) {
                $item['IBLOCKS_ID'] = array_unique($item['IBLOCKS_ID']);
            }
        }

        return $result;
    }

    protected function getSectionsAndElements($filteredList)
    {
        $result = [];

        $sections = [];
        $elements = [];

        if (
            !empty($filteredList['SECTIONS'])
            && is_array($filteredList['SECTIONS'])
        ) {
            $sections = $this->getSectionByFilter($filteredList['SECTIONS']['ID'], $filteredList['SECTIONS']['IBLOCKS_ID']);
        }

        if (
            !empty($filteredList['ELEMENTS'])
            && is_array($filteredList['ELEMENTS'])
        ) {
            $elements = $this->getElementsByFilter($filteredList['ELEMENTS']['ID'], $filteredList['ELEMENTS']['IBLOCKS_ID']);
        }

        if (!empty($sections)) {
            $result['SECTIONS'] = $sections;
        }

        if (!empty($elements)) {
            $result['ELEMENTS'] = $elements;
        }

        return $result;
    }

    protected function getSectionByFilter($arID, $arIBlockID)
    {
        $sections = [];

        $obSection = \CIBlockSection::GetList([], ['IBLOCK_ID' => $arIBlockID, 'ID' => $arID, 'ACTIVE' => 'Y'], false, ['IBLOCK_ID', 'ID', 'NAME', 'SECTION_PAGE_URL'], false);

        while ($section = $obSection->GetNext(false, false)) {
            $sections[$section['ID']] = $section;
        }

        return $sections;
    }

    protected function getElementsByFilter($arID, $arIBlockID)
    {
        $elements = [];

        $obElement = \CIBlockElement::GetList([], ['IBLOCK_ID' => $arIBlockID, 'ID' => $arID], false, false, ['IBLOCK_ID', 'ID', 'NAME', 'DETAIL_PAGE_URL']);

        while ($element = $obElement->GetNext(false, false)) {
            $elements[$element['ID']] = $element;
        }

        return $elements;
    }



    private function getPropertyCode($propID, $iBlockID)
    {

        $code = '';
        $obProperty = \CIBlockProperty::GetByID(intval($propID), $iBlockID);

        if($arProp = $obProperty->GetNext(false, false)) {
            $code = $arProp['CODE'];
        }

        return $code;
    }

    private function getItemsByFilter($iBlockID, $iBlockType, $propertyCode)
    {
        $items = [];

        $propertyCodeForFilter = 'PROPERTY_' . $propertyCode;

        // arOrder, arFilter, arGroupBy, arNavStartParams, arSelectFields
        $obElement = \CIBlockElement::GetList([], ['IBLOCK_ID' => 2], false, false, []);


        $this->arResult['code'] = $propertyCodeForFilter;


        while($arElements = $obElement->Fetch()) {
            //$this->arResult['ITEMS'][] = $arElements;
        }
    }

    /*protected function setLevel(&$array, $lvl = 1)
    {
        foreach ($array as &$item) {
            $item['LVL'] = $lvl;

            if (
                !empty($item['CHILD'])
                && is_array($item['CHILD'])
            ) {
                $this->setLevel($item['CHILD'], $lvl + 1);
            }
        }

    }*/

    private function data() {


        $data1 = [
            'Cантехника' => [
                'Сантехника_1' => [
                    'Сантехника_2' => 'Сантехника_5'
                ],
                'Сантехника_3' => '',
                'Сантехника_4' => ''
            ],
            'Разветвитель' => '',
            'Холодильники' => 'Холодильники_1',
        ];

        $data2 = [
            1 => [2 => [3 => 4], 5 => '', 6 => ''],
            7 => '',
            8 => 9
        ];

        $data4 = [
            'Сантехника' => [
                'Холодильники_1' => 'Сантехника_3',
                'Холодильники' => '',
            ],
            'Сантехника_4' => 'Сантехника_5'
        ];

        // s1->e7->e3
        // s1->[e7->e3, e4->[s2->e2]]

        $data3 = [
            [
                'ID' => 1,
                'IBLOCK_ID' => 1,
                'TYPE' => 'S',
                'CHILD' => [
                    [
                        'ID' => 7,
                        'IBLOCK_ID' => 1,
                        'TYPE' => 'S',
                        'CHILD' => [
                            [
                                'ID' => 3,
                                'IBLOCK_ID' => 1,
                                'TYPE' => 'S',
                                'CHILD' => [],
                            ],
                        ]
                    ],
                    [
                        'ID' => 6,
                        'IBLOCK_ID' => 1,
                        'TYPE' => 'S',
                        'CHILD' => [],
                    ]
                ]
            ],
            [
                'ID' => 4,
                'IBLOCK_ID' => 1,
                'TYPE' => 'S',
                'CHILD' => [
                    [
                        'ID' => 3,
                        'IBLOCK_ID' => 1,
                        'TYPE' => 'E',
                        'CHILD' => [],
                    ]
                ]
            ]
        ];

        $this->arResult['DATA_TEXT'] = $data3;
        /*$this->arResult['DATA_NUM'] = $data2;*/
    }

    private function getMixedList($iBlockID)
    {
        $sections = [];
        $elements = [];
        $mixedList = [];

        $obSection = \CIBlockSection::GetList([], ['IBLOCK_ID' => $iBlockID], false, ['IBLOCK_ID', 'ID', 'NAME', 'SECTION_PAGE_URL'], false);

        while ($section = $obSection->GetNext(false, false)) {
            $sections[] = $section;
        }

        $obElement = \CIBlockElement::GetList([], ['IBLOCK_ID' => $iBlockID], false, false, ['IBLOCK_ID', 'ID', 'NAME', 'DETAIL_PAGE_URL']);

        while ($element = $obElement->GetNext(false, false)) {
            $elements[] = $element;
        }

        $mixedList = array_merge($sections, $elements);

        return $mixedList;
    }

    // Если хотя бы у одного элемента выбрано свойство оно автоматически отображается в меню

    // Проверить подключен ли модуль инфоблоков, если нет то обработать ошибку
    // Собрать все элементы с выбранным свойством
    // Сформировать массив данных и отдать в темплейт

    // Есть элементы которые без раздела они автоматически становятся главными

    // Если у элемента есть раздел тогда выбираются




    // Собираем все айди разделов и их имена, также добавляем к разделам 0, чтобы проверять и без разедла элементы

    // После этого пробегаемся по массиву разделов и собираем для каждого элементы с свойством заданным,
    // если таких нет, тогда просто удаляем его из данного массива

    // и в этот же момент собираем данные в массив для вывода

    /* section
         section
            section
                items
    */


    // У элементов и разделов может быть одинаковый ID !!!!!!!


}