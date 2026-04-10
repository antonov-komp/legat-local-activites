<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("LEGATCOMPANYDIRECTORS_DESCR_NAME"),
    "DESCRIPTION" => Loc::getMessage("LEGATCOMPANYDIRECTORS_DESCR_DESCR"),
    "TYPE" => "activity",
    "CLASS" => "legatcompanydirectors",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "REQUEST_STATUS" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYDIRECTORS_DESCR_FIELD_STATUS"),
            "TYPE" => "string",
        ],
        "TOTAL" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYDIRECTORS_DESCR_FIELD_TOTAL"),
            "TYPE" => "string",
        ],
        "DIRECTORS_PAGE" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYDIRECTORS_DESCR_FIELD_PAGE"),
            "TYPE" => "string",
        ],
        "DIRECTORS_ITEMS" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYDIRECTORS_DESCR_FIELD_ITEMS"),
            "TYPE" => "string",
        ],
        "DIRECTORS_ACTIVE_ONLY" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYDIRECTORS_DESCR_FIELD_ACTIVE_ONLY"),
            "TYPE" => "string",
        ],
        "DIRECTORS_RAW_JSON" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYDIRECTORS_DESCR_FIELD_RAW_JSON"),
            "TYPE" => "string",
        ],
        "DIRECTORS_LIST_TEXT" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYDIRECTORS_DESCR_FIELD_LIST_TEXT"),
            "TYPE" => "string",
        ],
        "DIRECTORS_ACTIVE_LIST_TEXT" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYDIRECTORS_DESCR_FIELD_ACTIVE_LIST_TEXT"),
            "TYPE" => "string",
        ],
    ],
];
