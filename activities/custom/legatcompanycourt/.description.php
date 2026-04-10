<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("LEGATCOMPANYCOURT_DESCR_NAME"),
    "DESCRIPTION" => Loc::getMessage("LEGATCOMPANYCOURT_DESCR_DESCR"),
    "TYPE" => "activity",
    "CLASS" => "legatcompanycourt",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "AdditionalInfo" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCOURT_DESCR_FIELD_STATUS"),
            "TYPE" => "string",
        ],
        "LegatCourtTotal" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCOURT_DESCR_FIELD_TOTAL"),
            "TYPE" => "string",
        ],
        "LegatCourtRole" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCOURT_DESCR_FIELD_ROLE"),
            "TYPE" => "string",
        ],
        "LegatCourtPage" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCOURT_DESCR_FIELD_PAGE"),
            "TYPE" => "string",
        ],
        "LegatCourtItemsJson" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCOURT_DESCR_FIELD_ITEMS_JSON"),
            "TYPE" => "string",
        ],
        "LegatCourtRawJson" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCOURT_DESCR_FIELD_RAW_JSON"),
            "TYPE" => "string",
        ],
        "LegatCourtClaimantText" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCOURT_DESCR_FIELD_CLAIMANT_TEXT"),
            "TYPE" => "string",
        ],
        "LegatCourtClaimantJson" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYCOURT_DESCR_FIELD_CLAIMANT_JSON"),
            "TYPE" => "string",
        ],
    ],
];
