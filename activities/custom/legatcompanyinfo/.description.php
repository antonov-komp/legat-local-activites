<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_NAME"),
    "DESCRIPTION" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_DESCR"),
    "TYPE" => "activity",
    "CLASS" => "legatcompanyinfo",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => [
        "ID" => "other",
    ],
    "RETURN" => [
        "AdditionalInfo" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_ADDITIONAL_INFO"),
            "TYPE" => "string",
        ],
        "LegatGeneral" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_GENERAL"),
            "TYPE" => "string",
        ],
        "LegatTaxAuthority" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_TAX_AUTHORITY"),
            "TYPE" => "string",
        ],
        "LegatRegistration" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_REGISTRATION"),
            "TYPE" => "string",
        ],
        "LegatStatusBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_STATUS"),
            "TYPE" => "string",
        ],
        "LegatFundBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_FUND"),
            "TYPE" => "string",
        ],
        "LegatCourtsBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_COURTS"),
            "TYPE" => "string",
        ],
        "LegatProcurementBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_PROCUREMENT"),
            "TYPE" => "string",
        ],
        "LegatSalesBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_SALES"),
            "TYPE" => "string",
        ],
        "LegatConsumerAndChecksBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_CONSUMER_KGK"),
            "TYPE" => "string",
        ],
        "LegatBankruptBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_BANKRUPT"),
            "TYPE" => "string",
        ],
        "LegatDebtBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_DEBT"),
            "TYPE" => "string",
        ],
        "LegatFinanceBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_FINANCE"),
            "TYPE" => "string",
        ],
        "LegatRiskRegistersBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_RISK_REGISTERS"),
            "TYPE" => "string",
        ],
        "LegatLicensesBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_LICENSES"),
            "TYPE" => "string",
        ],
        "LegatCountersBlock" => [
            "NAME" => Loc::getMessage("LEGATCOMPANYINFO_DESCR_FIELD_COUNTERS"),
            "TYPE" => "string",
        ],
    ],
];
