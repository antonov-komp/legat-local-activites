<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("LEGATCOMPANYOKVED_DESCR_NAME"),
    "DESCRIPTION" => Loc::getMessage("LEGATCOMPANYOKVED_DESCR_DESCR"),
    "TYPE" => "activity",
    "CLASS" => "legatcompanyokved",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "AdditionalInfo" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYOKVED_DESCR_FIELD_STATUS"),
            "TYPE" => "string",
        ],
        "LegatOkvedTotal" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYOKVED_DESCR_FIELD_TOTAL"),
            "TYPE" => "string",
        ],
        "LegatOkvedList" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYOKVED_DESCR_FIELD_LIST"),
            "TYPE" => "string",
        ],
        "LegatOkvedJson" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYOKVED_DESCR_FIELD_JSON"),
            "TYPE" => "string",
        ],
    ],
];
