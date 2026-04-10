<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("LEGATCOMPANYADDRESS_DESCR_NAME"),
    "DESCRIPTION" => Loc::getMessage("LEGATCOMPANYADDRESS_DESCR_DESCR"),
    "TYPE" => "activity",
    "CLASS" => "legatcompanyaddress",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "AdditionalInfo" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYADDRESS_DESCR_FIELD_STATUS"),
            "TYPE" => "string",
        ],
        "LegatAddressTotal" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYADDRESS_DESCR_FIELD_TOTAL"),
            "TYPE" => "string",
        ],
        "LegatAddressList" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYADDRESS_DESCR_FIELD_LIST"),
            "TYPE" => "string",
        ],
        "LegatAddressJson" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYADDRESS_DESCR_FIELD_JSON"),
            "TYPE" => "string",
        ],
    ],
];
