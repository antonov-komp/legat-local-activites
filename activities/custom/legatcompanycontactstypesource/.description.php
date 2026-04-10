<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_NAME"),
    "DESCRIPTION" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_DESCR"),
    "TYPE" => "activity",
    "CLASS" => "legatcompanycontactstypesource",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "REQUEST_STATUS" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_FIELD_STATUS"),
            "TYPE" => "string",
        ],
        "TOTAL" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_FIELD_TOTAL"),
            "TYPE" => "string",
        ],
        "PAGE" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_FIELD_PAGE"),
            "TYPE" => "string",
        ],
        "CONTACTS_ITEMS" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_FIELD_ITEMS"),
            "TYPE" => "string",
        ],
        "CONTACTS_GROUPED_BY_TYPE" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_FIELD_GROUP_TYPE"),
            "TYPE" => "string",
        ],
        "CONTACTS_GROUPED_BY_SOURCE" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_FIELD_GROUP_SOURCE"),
            "TYPE" => "string",
        ],
        "CONTACTS_RAW_JSON" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_FIELD_RAW_JSON"),
            "TYPE" => "string",
        ],
        "CONTACTS_LIST_TEXT" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_FIELD_LIST_TEXT"),
            "TYPE" => "string",
        ],
        "CONTACTS_BY_TYPE_TEXT" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_FIELD_BY_TYPE_TEXT"),
            "TYPE" => "string",
        ],
        "CONTACTS_BY_SOURCE_TEXT" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCONTACTSTYPESOURCE_DESCR_FIELD_BY_SOURCE_TEXT"),
            "TYPE" => "string",
        ],
    ],
];
