<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require_once dirname(__DIR__) . '/LegatByClient.php';

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

class CBPlegatcompanydirectors extends BaseActivity
{
    private const PATH = '/api2/by/directors';

    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Title' => '',
            'UNP' => '',
            'PAGE' => '',
            'REQUEST_STATUS' => null,
            'TOTAL' => null,
            'DIRECTORS_PAGE' => null,
            'DIRECTORS_ITEMS' => null,
            'DIRECTORS_ACTIVE_ONLY' => null,
            'DIRECTORS_RAW_JSON' => null,
            'DIRECTORS_LIST_TEXT' => null,
            'DIRECTORS_ACTIVE_LIST_TEXT' => null,
        ];

        $this->SetPropertiesTypes([
            'UNP' => ['Type' => FieldType::STRING],
            'PAGE' => ['Type' => FieldType::INT],
            'REQUEST_STATUS' => ['Type' => FieldType::STRING],
            'TOTAL' => ['Type' => FieldType::STRING],
            'DIRECTORS_PAGE' => ['Type' => FieldType::STRING],
            'DIRECTORS_ITEMS' => ['Type' => FieldType::STRING],
            'DIRECTORS_ACTIVE_ONLY' => ['Type' => FieldType::STRING],
            'DIRECTORS_RAW_JSON' => ['Type' => FieldType::STRING],
            'DIRECTORS_LIST_TEXT' => ['Type' => FieldType::TEXT],
            'DIRECTORS_ACTIVE_LIST_TEXT' => ['Type' => FieldType::TEXT],
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
        $this->preparedProperties['DIRECTORS_PAGE'] = '';
        $this->preparedProperties['DIRECTORS_ITEMS'] = '';
        $this->preparedProperties['DIRECTORS_ACTIVE_ONLY'] = '';
        $this->preparedProperties['DIRECTORS_RAW_JSON'] = '';
        $this->preparedProperties['DIRECTORS_LIST_TEXT'] = '';
        $this->preparedProperties['DIRECTORS_ACTIVE_LIST_TEXT'] = '';

        $unp = trim((string)$this->UNP);
        $page = $this->normalizePage($this->PAGE);
        $this->preparedProperties['DIRECTORS_PAGE'] = (string)$page;

        if ($unp === '') {
            $msg = Loc::getMessage('LEGATCOMPANYDIRECTORS_ERR_EMPTY_UNP');
            return $this->applyError($errors, 'EMPTY_UNP', $msg);
        }

        $apiKey = LegatByClient::getApiKey();
        if ($apiKey === '') {
            $msg = Loc::getMessage('LEGATCOMPANYDIRECTORS_ERR_NO_API_KEY');
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
            $msg = Loc::getMessage('LEGATCOMPANYDIRECTORS_ERR_API', ['#MSG#' => (string)$data['error']]);
            return $this->applyError($errors, 'API_ERROR', $msg);
        }

        $total = $data['total'] ?? null;
        $this->preparedProperties['TOTAL'] = $total === null || $total === '' ? '' : (string)$total;

        $directors = $data['directors'] ?? null;
        $rows = \is_array($directors) ? $directors : [];
        $normalized = $this->normalizeDirectors($rows);
        $activeOnly = array_values(array_filter($normalized, static function (array $item): bool {
            return (int)($item['active'] ?? 0) === 1;
        }));

        $this->preparedProperties['DIRECTORS_ITEMS'] = LegatByClient::jsonUtf8($normalized);
        $this->preparedProperties['DIRECTORS_ACTIVE_ONLY'] = LegatByClient::jsonUtf8($activeOnly);
        $this->preparedProperties['DIRECTORS_RAW_JSON'] = LegatByClient::jsonUtf8($rows);
        $this->preparedProperties['DIRECTORS_LIST_TEXT'] = $this->formatDirectorsText($normalized);
        $this->preparedProperties['DIRECTORS_ACTIVE_LIST_TEXT'] = $this->formatDirectorsText($activeOnly);
        $this->preparedProperties['REQUEST_STATUS'] = Loc::getMessage('LEGATCOMPANYDIRECTORS_OK');

        $this->log(
            $this->preparedProperties['REQUEST_STATUS']
            . ' UNP=' . $this->maskUnp($unp)
            . ' PAGE=' . $this->preparedProperties['DIRECTORS_PAGE']
        );

        return $errors;
    }

    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        return [
            'UNP' => [
                'Name' => Loc::getMessage('LEGATCOMPANYDIRECTORS_FIELD_UNP'),
                'FieldName' => 'unp',
                'Type' => FieldType::STRING,
                'Required' => true,
                'Options' => [],
            ],
            'PAGE' => [
                'Name' => Loc::getMessage('LEGATCOMPANYDIRECTORS_FIELD_PAGE'),
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
     *     name: string,
     *     date_begin: ?string,
     *     date_end: ?string,
     *     date_update: ?string,
     *     reestr_id: int,
     *     reestr_name: string,
     *     active: int
     * }>
     */
    private function normalizeDirectors(array $rows): array
    {
        $items = [];
        foreach ($rows as $row) {
            if (!\is_array($row)) {
                continue;
            }

            $reestrId = (int)($row['reestr_id'] ?? 0);
            $items[] = [
                'name' => trim((string)($row['name'] ?? '')),
                'date_begin' => LegatByClient::normalizeLegatDate($row['date_begin'] ?? null),
                'date_end' => LegatByClient::normalizeLegatDate($row['date_end'] ?? null),
                'date_update' => LegatByClient::normalizeLegatDate($row['date_update'] ?? null),
                'reestr_id' => $reestrId,
                'reestr_name' => $this->mapReestrName($reestrId),
                'active' => ((string)($row['active'] ?? '0') === '1') ? 1 : 0,
            ];
        }

        return $items;
    }

    private function mapReestrName(int $reestrId): string
    {
        $map = [
            1 => 'ЕГР',
            2 => 'финансовая отчетность',
            4 => 'ГИАС',
        ];
        return $map[$reestrId] ?? '';
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private function formatDirectorsText(array $items): string
    {
        if ($items === []) {
            return '';
        }
        $blocks = [];
        foreach ($items as $item) {
            $active = (int)($item['active'] ?? 0) === 1;
            $activeLabel = $active
                ? Loc::getMessage('LEGATCOMPANYDIRECTORS_ACTIVE_YES')
                : Loc::getMessage('LEGATCOMPANYDIRECTORS_ACTIVE_NO');

            $name = trim((string)($item['name'] ?? ''));
            $reestrId = (int)($item['reestr_id'] ?? 0);
            $reestrName = trim((string)($item['reestr_name'] ?? ''));
            $reestrLine = $reestrName !== ''
                ? Loc::getMessage('LEGATCOMPANYDIRECTORS_LINE_REESTR', [
                    '#ID#' => (string)$reestrId,
                    '#NAME#' => $reestrName,
                ])
                : ($reestrId !== 0
                    ? Loc::getMessage('LEGATCOMPANYDIRECTORS_LINE_REESTR_ID', ['#ID#' => (string)$reestrId])
                    : null);

            $dateBegin = $item['date_begin'] ?? null;
            $dateEnd = $item['date_end'] ?? null;
            $dateUpdate = $item['date_update'] ?? null;

            $parts = [
                $name !== '' ? Loc::getMessage('LEGATCOMPANYDIRECTORS_LINE_NAME', ['#NAME#' => $name]) : null,
                $dateBegin !== null
                    ? Loc::getMessage('LEGATCOMPANYDIRECTORS_LINE_DATE_BEGIN', ['#DATE#' => (string)$dateBegin])
                    : null,
                $dateEnd !== null
                    ? Loc::getMessage('LEGATCOMPANYDIRECTORS_LINE_DATE_END', ['#DATE#' => (string)$dateEnd])
                    : null,
                $dateUpdate !== null
                    ? Loc::getMessage('LEGATCOMPANYDIRECTORS_LINE_DATE_UPDATE', ['#DATE#' => (string)$dateUpdate])
                    : null,
                $reestrLine,
                Loc::getMessage('LEGATCOMPANYDIRECTORS_LINE_ACTIVE', ['#ACTIVE#' => $activeLabel]),
            ];

            $line = implode("\n", array_filter($parts, static fn($p) => $p !== null && trim((string)$p) !== ''));
            if ($line !== '') {
                $blocks[] = $line;
            }
        }

        return implode("\n\n", $blocks);
    }

    /**
     * @param array{ok: bool, httpCode: int, data: ?array, error: ?string} $result
     */
    private function applyHttpJsonError(ErrorCollection $errors, array $result): ErrorCollection
    {
        if (($result['error'] ?? '') === 'http') {
            $msg = Loc::getMessage('LEGATCOMPANYDIRECTORS_ERR_HTTP', ['#CODE#' => (string)$result['httpCode']]);
            return $this->applyError($errors, 'HTTP_ERROR', $msg);
        }

        $msg = Loc::getMessage('LEGATCOMPANYDIRECTORS_ERR_JSON');
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
