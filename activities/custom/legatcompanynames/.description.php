<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("LEGATCOMPANYNAMES_DESCR_NAME"),
    "DESCRIPTION" => Loc::getMessage("LEGATCOMPANYNAMES_DESCR_DESCR"),
    "TYPE" => "activity",
    "CLASS" => "legatcompanynames",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "AdditionalInfo" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYNAMES_DESCR_FIELD_STATUS"),
            "TYPE" => "string",
        ],
        "LegatNamesTotal" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYNAMES_DESCR_FIELD_TOTAL"),
            "TYPE" => "string",
        ],
        "LegatNamesList" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYNAMES_DESCR_FIELD_LIST"),
            "TYPE" => "string",
        ],
        "LegatNamesJson" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYNAMES_DESCR_FIELD_JSON"),
            "TYPE" => "string",
        ],
    ],
];
