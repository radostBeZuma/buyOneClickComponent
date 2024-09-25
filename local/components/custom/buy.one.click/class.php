<?php


use Bitrix\Main\Loader,
    Bitrix\Sale\Internals\OrderPropsTable;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class CBuyOneClickCustom extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams): array
    {
        return $arParams;
    }

    public static function getOrderFieldsByPersonType($personType)
    {
        $arOrderProps = [];

        $properties = OrderPropsTable::getList([
            'select' => [
                'ID',
                'NAME',
                'TYPE',
                'REQUIRED',
                'SORT',
                'CODE'
            ],
            'filter' => [
                'PERSON_TYPE_ID' => $personType,
                'ACTIVE' => 'Y'
            ],
        ]);

        while ($property = $properties->fetch()) {
            $arOrderProps[] = $property;
        }

        return $arOrderProps;
    }

    public function executeComponent()
    {
        Loader::includeModule('sale');

        $arOrderProps = $this->getOrderFieldsByPersonType($this->arParams['PERSON_TYPE_ID']);

        $this->arResult = [
            'ORDER_PROPS' => $arOrderProps,
        ];

        $this->includeComponentTemplate();
    }
}