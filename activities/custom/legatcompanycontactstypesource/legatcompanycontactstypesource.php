<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require_once dirname(__DIR__) . '/LegatByClient.php';

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

class CBPlegatcompanycontactstypesource extends BaseActivity
{
    private const PATH = '/api2/by/contactsTypeSource';

    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Title' => '',
            'UNP' => '',
            'PAGE' => '',
            'REQUEST_STATUS' => null,
            'TOTAL' => null,
            'CONTACTS_ITEMS' => null,
            'CONTACTS_GROUPED_BY_TYPE' => null,
            'CONTACTS_GROUPED_BY_SOURCE' => null,
            'CONTACTS_RAW_JSON' => null,
            'CONTACTS_LIST_TEXT' => null,
            'CONTACTS_BY_TYPE_TEXT' => null,
            'CONTACTS_BY_SOURCE_TEXT' => null,
        ];

        $this->SetPropertiesTypes([
            'UNP' => ['Type' => FieldType::STRING],
            'PAGE' => ['Type' => FieldType::INT],
            'REQUEST_STATUS' => ['Type' => FieldType::STRING],
            'TOTAL' => ['Type' => FieldType::STRING],
            'CONTACTS_ITEMS' => ['Type' => FieldType::STRING],
            'CONTACTS_GROUPED_BY_TYPE' => ['Type' => FieldType::STRING],
            'CONTACTS_GROUPED_BY_SOURCE' => ['Type' => FieldType::STRING],
            'CONTACTS_RAW_JSON' => ['Type' => FieldType::STRING],
            'CONTACTS_LIST_TEXT' => ['Type' => FieldType::TEXT],
            'CONTACTS_BY_TYPE_TEXT' => ['Type' => FieldType::TEXT],
            'CONTACTS_BY_SOURCE_TEXT' => ['Type' => FieldType::TEXT],
        ]);
    }

    protected static function getFileName(): string
    {
        return __FILE__;
    }

    protected function internalExecute(): ErrorCollection
    {
        $errors = parent::internalExecute();

        $this->preparedProperties['REQUEST_STATUS'] = '';
        $this->preparedProperties['TOTAL'] = '';
        $this->preparedProperties['PAGE'] = '';
        $this->preparedProperties['CONTACTS_ITEMS'] = '';
        $this->preparedProperties['CONTACTS_GROUPED_BY_TYPE'] = '';
        $this->preparedProperties['CONTACTS_GROUPED_BY_SOURCE'] = '';
        $this->preparedProperties['CONTACTS_RAW_JSON'] = '';
        $this->preparedProperties['CONTACTS_LIST_TEXT'] = '';
        $this->preparedProperties['CONTACTS_BY_TYPE_TEXT'] = '';
        $this->preparedProperties['CONTACTS_BY_SOURCE_TEXT'] = '';

        $unp = trim((string)$this->UNP);
        $page = $this->normalizePage($this->PAGE);
        $this->preparedProperties['PAGE'] = (string)$page;

        if ($unp === '') {
            $msg = Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_ERR_EMPTY_UNP');
            return $this->applyError($errors, 'EMPTY_UNP', $msg);
        }

        $apiKey = LegatByClient::getApiKey();
        if ($apiKey === '') {
            $msg = Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_ERR_NO_API_KEY');
            return $this->applyError($errors, 'NO_API_KEY', $msg);
        }

        $result = LegatByClient::getJson(self::PATH, [
            'unp' => $unp,
            'page' => $page,
            'key' => $apiKey,
        ]);

        if (!$result['ok'] || $result['data'] === null) {
            return $this->applyHttpJsonError($errors, $result);
        }

        $data = $result['data'];
        if (!empty($data['error'])) {
            $msg = Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_ERR_API', ['#MSG#' => (string)$data['error']]);
            return $this->applyError($errors, 'API_ERROR', $msg);
        }

        $total = $data['total'] ?? null;
        $this->preparedProperties['TOTAL'] = $total === null || $total === '' ? '' : (string)$total;

        $contactsRaw = $data['contacts'] ?? null;
        $contacts = \is_array($contactsRaw) ? $contactsRaw : [];

        $normalized = $this->normalizeContacts($contacts);
        $groupedByType = $this->groupBy($normalized, 'contact_type_name');
        $groupedBySource = $this->groupBy($normalized, 'reestr_type_name');

        $this->preparedProperties['CONTACTS_ITEMS'] = LegatByClient::jsonUtf8($normalized);
        $this->preparedProperties['CONTACTS_GROUPED_BY_TYPE'] = LegatByClient::jsonUtf8($groupedByType);
        $this->preparedProperties['CONTACTS_GROUPED_BY_SOURCE'] = LegatByClient::jsonUtf8($groupedBySource);
        $this->preparedProperties['CONTACTS_RAW_JSON'] = LegatByClient::jsonUtf8($contacts);
        $this->preparedProperties['CONTACTS_LIST_TEXT'] = $this->formatContactsListText($normalized);
        $this->preparedProperties['CONTACTS_BY_TYPE_TEXT'] = $this->formatContactsGroupedText($groupedByType, true);
        $this->preparedProperties['CONTACTS_BY_SOURCE_TEXT'] = $this->formatContactsGroupedText($groupedBySource, false);
        $this->preparedProperties['REQUEST_STATUS'] = Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_OK');

        $this->log(
            $this->preparedProperties['REQUEST_STATUS']
            . ' UNP=' . $this->maskUnp($unp)
            . ' PAGE=' . $this->preparedProperties['PAGE']
            . ' TOTAL=' . $this->preparedProperties['TOTAL']
        );

        return $errors;
    }

    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        return [
            'UNP' => [
                'Name' => Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_FIELD_UNP'),
                'FieldName' => 'unp',
                'Type' => FieldType::STRING,
                'Required' => true,
                'Options' => [],
            ],
            'PAGE' => [
                'Name' => Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_FIELD_PAGE'),
                'FieldName' => 'page',
                'Type' => FieldType::INT,
                'Required' => false,
                'Default' => 1,
                'Options' => [],
            ],
        ];
    }

    /**
     * @param list<mixed> $rows
     * @return list<array{
     *     contact: string,
     *     contact_type: int,
     *     contact_type_name: string,
     *     reestr_type: int,
     *     reestr_type_name: string
     * }>
     */
    private function normalizeContacts(array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            if (!\is_array($row)) {
                continue;
            }
            $contactType = (int)($row['contact_type'] ?? 0);
            $reestrType = (int)($row['reestr_type'] ?? 0);
            $items[] = [
                'contact' => trim((string)($row['contact'] ?? '')),
                'contact_type' => $contactType,
                'contact_type_name' => $this->mapContactType($contactType),
                'reestr_type' => $reestrType,
                'reestr_type_name' => $this->mapReestrType($reestrType),
            ];
        }
        return $items;
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return array<string, list<array<string, mixed>>>
     */
    private function groupBy(array $items, string $field): array
    {
        $grouped = [];
        foreach ($items as $item) {
            $key = trim((string)($item[$field] ?? ''));
            if ($key === '') {
                $key = Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_UNKNOWN');
            }
            if (!isset($grouped[$key]) || !\is_array($grouped[$key])) {
                $grouped[$key] = [];
            }
            $grouped[$key][] = $item;
        }
        return $grouped;
    }

    private function mapContactType(int $type): string
    {
        $map = [
            1 => 'телефон',
            2 => 'факс',
            3 => 'email',
            4 => 'сайт',
        ];
        return $map[$type] ?? '';
    }

    private function mapReestrType(int $type): string
    {
        $map = [
            1 => 'контакты субъекта из торгового реестра',
            2 => 'контакты объекта торгового реестра',
            3 => 'тендерные закупки',
            4 => 'объекты бытового обслуживания',
            5 => 'БЕЛТПП',
            6 => 'интернет-источники',
            7 => 'реестр сертификатов',
            8 => 'реестр лизинговых организаций',
            9 => 'реестр программ для ЭВМ государств ЕАЭС',
            10 => 'ЕГР',
        ];
        return $map[$type] ?? '';
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private function formatContactsListText(array $items): string
    {
        if ($items === []) {
            return '';
        }
        $blocks = [];
        foreach ($items as $item) {
            $contact = trim((string)($item['contact'] ?? ''));
            $ct = (int)($item['contact_type'] ?? 0);
            $ctName = trim((string)($item['contact_type_name'] ?? ''));
            $rt = (int)($item['reestr_type'] ?? 0);
            $rtName = trim((string)($item['reestr_type_name'] ?? ''));

            $typeLine = $ctName !== ''
                ? Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_LINE_TYPE', [
                    '#NAME#' => $ctName,
                    '#ID#' => (string)$ct,
                ])
                : Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_LINE_TYPE_ID', ['#ID#' => (string)$ct]);

            $srcLine = $rtName !== ''
                ? Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_LINE_SOURCE', [
                    '#NAME#' => $rtName,
                    '#ID#' => (string)$rt,
                ])
                : Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_LINE_SOURCE_ID', ['#ID#' => (string)$rt]);

            $parts = [
                $contact !== '' ? Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_LINE_CONTACT', ['#V#' => $contact]) : null,
                $typeLine,
                $srcLine,
            ];
            $block = implode("\n", array_filter($parts, static fn($p) => $p !== null && trim((string)$p) !== ''));
            if ($block !== '') {
                $blocks[] = $block;
            }
        }

        return implode("\n\n", $blocks);
    }

    /**
     * @param array<string, list<array<string, mixed>>> $grouped
     */
    private function formatContactsGroupedText(array $grouped, bool $showSourceAsSecondary): string
    {
        if ($grouped === []) {
            return '';
        }
        $sections = [];
        foreach ($grouped as $title => $list) {
            if (!\is_array($list) || $list === []) {
                continue;
            }
            $header = Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_GROUP_HEADER', ['#TITLE#' => (string)$title]);
            $lines = [$header];
            foreach ($list as $item) {
                if (!\is_array($item)) {
                    continue;
                }
                $contact = trim((string)($item['contact'] ?? ''));
                if ($contact === '') {
                    continue;
                }
                if ($showSourceAsSecondary) {
                    $rtName = trim((string)($item['reestr_type_name'] ?? ''));
                    $rt = (int)($item['reestr_type'] ?? 0);
                    $secondary = $rtName !== ''
                        ? $rtName . ' (' . $rt . ')'
                        : (string)$rt;
                    $lines[] = Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_BULLET_WITH_SECONDARY', [
                        '#CONTACT#' => $contact,
                        '#SECONDARY#' => $secondary,
                    ]);
                } else {
                    $icName = trim((string)($item['contact_type_name'] ?? ''));
                    $ic = (int)($item['contact_type'] ?? 0);
                    $secondary = $icName !== ''
                        ? $icName . ' (' . $ic . ')'
                        : (string)$ic;
                    $lines[] = Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_BULLET_WITH_SECONDARY', [
                        '#CONTACT#' => $contact,
                        '#SECONDARY#' => $secondary,
                    ]);
                }
            }
            if (count($lines) > 1) {
                $sections[] = implode("\n", $lines);
            }
        }

        return implode("\n\n", $sections);
    }

    /**
     * @param array{ok: bool, httpCode: int, data: ?array, error: ?string} $result
     */
    private function applyHttpJsonError(ErrorCollection $errors, array $result): ErrorCollection
    {
        if (($result['error'] ?? '') === 'http') {
            $msg = Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_ERR_HTTP', ['#CODE#' => (string)$result['httpCode']]);
            return $this->applyError($errors, 'HTTP_ERROR', $msg);
        }

        $msg = Loc::getMessage('LEGATCOMPANYCONTACTSTYPESOURCE_ERR_JSON');
        return $this->applyError($errors, 'INVALID_JSON', $msg);
    }

    private function normalizePage($value): int
    {
        $s = trim((string)$value);
        if ($s === '') {
            return 1;
        }
        $page = (int)$s;
        return $page > 0 ? $page : 1;
    }

    private function maskUnp(string $unp): string
    {
        $len = strlen($unp);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return str_repeat('*', $len - 4) . substr($unp, -4);
    }

    private function applyError(ErrorCollection $errors, string $code, string $message): ErrorCollection
    {
        $this->preparedProperties['REQUEST_STATUS'] = $message;
        $errors[] = new \Bitrix\Main\Error($message, $code);
        $this->log($message);
        return $errors;
    }
}
