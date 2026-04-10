<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTS_DESCR_NAME"),
    "DESCRIPTION" => Loc::getMessage("LEGATCOMPANYCONTACTS_DESCR_DESCR"),
    "TYPE" => "activity",
    "CLASS" => "legatcompanycontacts",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "AdditionalInfo" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTS_DESCR_FIELD_STATUS"),
            "TYPE" => "string",
        ],
        "LegatPhones" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTS_DESCR_FIELD_PHONES"),
            "TYPE" => "string",
        ],
        "LegatFax" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTS_DESCR_FIELD_FAX"),
            "TYPE" => "string",
        ],
        "LegatEmails" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTS_DESCR_FIELD_EMAILS"),
            "TYPE" => "string",
        ],
        "LegatSites" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTS_DESCR_FIELD_SITES"),
            "TYPE" => "string",
        ],
        "LegatContactsJson" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTS_DESCR_FIELD_JSON"),
            "TYPE" => "string",
        ],
    ],
];
