<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require_once dirname(__DIR__) . '/LegatByClient.php';

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

class CBPlegatcompanycourt extends BaseActivity
{
    private const PATH = '/api2/by/court';

    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Title' => '',
            'UNP' => '',
            'CourtType' => '',
            'CourtPage' => '',
            'AdditionalInfo' => null,
            'LegatCourtTotal' => null,
            'LegatCourtRole' => null,
            'LegatCourtPage' => null,
            'LegatCourtItemsJson' => null,
            'LegatCourtRawJson' => null,
            'LegatCourtClaimantText' => null,
            'LegatCourtClaimantJson' => null,
        ];

        $this->SetPropertiesTypes([
            'UNP' => ['Type' => FieldType::STRING],
            'CourtType' => ['Type' => FieldType::STRING],
            'CourtPage' => ['Type' => FieldType::STRING],
            'AdditionalInfo' => ['Type' => FieldType::STRING],
            'LegatCourtTotal' => ['Type' => FieldType::STRING],
            'LegatCourtRole' => ['Type' => FieldType::STRING],
            'LegatCourtPage' => ['Type' => FieldType::STRING],
            'LegatCourtItemsJson' => ['Type' => FieldType::STRING],
            'LegatCourtRawJson' => ['Type' => FieldType::STRING],
            'LegatCourtClaimantText' => ['Type' => FieldType::STRING],
            'LegatCourtClaimantJson' => ['Type' => FieldType::STRING],
        ]);
    }

    protected static function getFileName(): string
    {
        return __FILE__;
    }

    protected function internalExecute(): ErrorCollection
    {
        $errors = parent::internalExecute();

        $this->preparedProperties['AdditionalInfo'] = '';
        $this->preparedProperties['LegatCourtTotal'] = '';
        $this->preparedProperties['LegatCourtRole'] = '';
        $this->preparedProperties['LegatCourtPage'] = '';
        $this->preparedProperties['LegatCourtItemsJson'] = '';
        $this->preparedProperties['LegatCourtRawJson'] = '';
        $this->preparedProperties['LegatCourtClaimantText'] = '';
        $this->preparedProperties['LegatCourtClaimantJson'] = '';

        $unp = trim((string)$this->UNP);
        if ($unp === '') {
            $msg = Loc::getMessage('LEGATCOMPANYCOURT_ERR_EMPTY_UNP');
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'EMPTY_UNP');
            return $errors;
        }

        $typeRaw = trim((string)$this->CourtType);
        if ($typeRaw !== '' && !\in_array($typeRaw, ['1', '2'], true)) {
            $msg = Loc::getMessage('LEGATCOMPANYCOURT_ERR_BAD_TYPE');
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'BAD_TYPE');
            return $errors;
        }

        $pageRaw = trim((string)$this->CourtPage);
        $page = 0;
        if ($pageRaw !== '') {
            if (!ctype_digit($pageRaw) || (int)$pageRaw < 1) {
                $msg = Loc::getMessage('LEGATCOMPANYCOURT_ERR_BAD_PAGE');
                $this->preparedProperties['AdditionalInfo'] = $msg;
                $errors[] = new \Bitrix\Main\Error($msg, 'BAD_PAGE');
                return $errors;
            }
            $page = (int)$pageRaw;
        }
        $effectivePage = $page > 0 ? $page : 1;

        $apiKey = LegatByClient::getApiKey();
        if ($apiKey === '') {
            $msg = Loc::getMessage('LEGATCOMPANYCOURT_ERR_NO_API_KEY');
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'NO_API_KEY');
            return $errors;
        }

        $query = [
            'unp' => $unp,
            'key' => $apiKey,
            'page' => $effectivePage,
        ];
        if ($typeRaw !== '') {
            $query['type'] = (int)$typeRaw;
        }

        $result = LegatByClient::getJson(self::PATH, $query);

        if (!$result['ok'] || $result['data'] === null) {
            return $this->applyHttpJsonError($errors, $result);
        }

        $data = $result['data'];
        if (!empty($data['error'])) {
            $msg = Loc::getMessage('LEGATCOMPANYCOURT_ERR_API', ['#MSG#' => (string)$data['error']]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'API_ERROR');
            $this->log($msg);
            return $errors;
        }

        $this->preparedProperties['AdditionalInfo'] = Loc::getMessage('LEGATCOMPANYCOURT_OK');
        $total = $data['total'] ?? null;
        $this->preparedProperties['LegatCourtTotal'] = $total === null || $total === '' ? '' : (string)$total;
        $this->preparedProperties['LegatCourtRole'] = $typeRaw;
        $this->preparedProperties['LegatCourtPage'] = (string)$effectivePage;
        $this->preparedProperties['LegatCourtRawJson'] = LegatByClient::jsonUtf8($data);

        $claimant = $data['claimant'] ?? null;
        if ($claimant === null) {
            $this->preparedProperties['LegatCourtClaimantJson'] = '';
        } elseif (\is_array($claimant) || \is_object($claimant)) {
            $this->preparedProperties['LegatCourtClaimantJson'] = LegatByClient::jsonUtf8($claimant);
        } else {
            $enc = json_encode($claimant, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $this->preparedProperties['LegatCourtClaimantJson'] = $enc !== false ? (string)$enc : '';
        }
        $normalizedItems = $this->normalizeCourtItems($claimant, $typeRaw);
        $this->preparedProperties['LegatCourtItemsJson'] = LegatByClient::jsonUtf8($normalizedItems);
        $this->preparedProperties['LegatCourtClaimantText'] = $this->formatClaimantHumanReadable($claimant);

        $this->log($this->preparedProperties['AdditionalInfo'] . ' UNP=' . $unp);

        return $errors;
    }

    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        return [
            'UNP' => [
                'Name' => Loc::getMessage('LEGATCOMPANYCOURT_FIELD_UNP'),
                'FieldName' => 'unp',
                'Type' => FieldType::STRING,
                'Required' => true,
                'Options' => [],
            ],
            'CourtType' => [
                'Name' => Loc::getMessage('LEGATCOMPANYCOURT_FIELD_TYPE'),
                'FieldName' => 'court_type',
                'Type' => FieldType::SELECT,
                'Required' => false,
                'Options' => [
                    ['' => Loc::getMessage('LEGATCOMPANYCOURT_TYPE_ALL')],
                    ['1' => Loc::getMessage('LEGATCOMPANYCOURT_TYPE_1')],
                    ['2' => Loc::getMessage('LEGATCOMPANYCOURT_TYPE_2')],
                ],
            ],
            'CourtPage' => [
                'Name' => Loc::getMessage('LEGATCOMPANYCOURT_FIELD_PAGE'),
                'FieldName' => 'court_page',
                'Type' => FieldType::STRING,
                'Required' => false,
                'Options' => [],
            ],
        ];
    }

    /**
     * @param array{ok: bool, httpCode: int, data: ?array, error: ?string} $result
     */
    private function applyHttpJsonError(ErrorCollection $errors, array $result): ErrorCollection
    {
        if (($result['error'] ?? '') === 'http') {
            $msg = Loc::getMessage('LEGATCOMPANYCOURT_ERR_HTTP', ['#CODE#' => (string)$result['httpCode']]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'HTTP_ERROR');
            $this->log($msg);
            return $errors;
        }

        $msg = Loc::getMessage('LEGATCOMPANYCOURT_ERR_JSON');
        $this->preparedProperties['AdditionalInfo'] = $msg;
        $errors[] = new \Bitrix\Main\Error($msg, 'INVALID_JSON');
        return $errors;
    }

    /**
     * @param mixed $claimant
     */
    private function formatClaimantHumanReadable($claimant): string
    {
        if ($claimant === null) {
            return Loc::getMessage('LEGATCOMPANYCOURT_EMPTY_CLAIMANT');
        }
        if (\is_scalar($claimant)) {
            return trim((string)$claimant);
        }
        if (\is_array($claimant)) {
            if ($claimant === []) {
                return '';
            }
            $isList = array_keys($claimant) === range(0, \count($claimant) - 1);
            if ($isList) {
                $blocks = [];
                foreach ($claimant as $i => $row) {
                    if (!\is_array($row)) {
                        $blocks[] = (string)$row;
                        continue;
                    }
                    $blocks[] = '— ' . $this->formatAssocRow($row);
                }
                return implode("\n\n", array_filter($blocks, static fn($s) => trim((string)$s) !== ''));
            }
            return $this->formatAssocRow($claimant);
        }

        return LegatByClient::jsonUtf8($claimant);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function formatAssocRow(array $row): string
    {
        $parts = [];
        foreach ($row as $k => $v) {
            if ($v === null || $v === '') {
                continue;
            }
            if (\is_array($v)) {
                $enc = LegatByClient::jsonUtf8($v);
                if ($enc !== '') {
                    $parts[] = (string)$k . ': ' . $enc;
                }
                continue;
            }
            $parts[] = (string)$k . ': ' . trim((string)$v);
        }
        return implode('; ', $parts);
    }

    /**
     * @param mixed $claimant
     * @return array<int, array<string, string>>
     */
    private function normalizeCourtItems($claimant, string $typeRaw): array
    {
        if ($claimant === null) {
            return [];
        }
        if (!\is_array($claimant)) {
            return [];
        }

        $rows = array_keys($claimant) === range(0, \count($claimant) - 1)
            ? $claimant
            : [$claimant];

        $items = [];
        foreach ($rows as $row) {
            if (!\is_array($row)) {
                continue;
            }
            $items[] = [
                'role' => $this->resolveRoleLabel($typeRaw),
                'case_number' => $this->pickScalar($row, ['number', 'case_number', 'num']),
                'date' => $this->normalizeDateString($this->pickScalar($row, ['date', 'dt', 'date_reg'])),
                'decision' => $this->pickScalar($row, ['decision', 'result', 'last_decision']),
                'sum' => $this->pickScalar($row, ['sum', 'summ', 'amount']),
                'claimant_name' => $this->pickScalar($row, ['claimant_name', 'vz_name', 'name_vz', 'claimant']),
                'claimant_unp' => $this->pickScalar($row, ['claimant_unp', 'vz_unp', 'unp_vz']),
                'debtor_name' => $this->pickScalar($row, ['debtor_name', 'db_name', 'name_db', 'debtor']),
                'debtor_unp' => $this->pickScalar($row, ['debtor_unp', 'db_unp', 'unp_db']),
                'court_region' => $this->pickScalar($row, ['region', 'court_region']),
                'judge' => $this->pickScalar($row, ['judge', 'sudya']),
                'year' => $this->pickScalar($row, ['year']),
                'month' => $this->pickScalar($row, ['month']),
                'is_refusal' => $this->pickScalar($row, ['is_refusal', 'refusal', 'reject', 'denied_acceptance']),
            ];
        }

        return $items;
    }

    private function resolveRoleLabel(string $typeRaw): string
    {
        if ($typeRaw === '1') {
            return Loc::getMessage('LEGATCOMPANYCOURT_TYPE_1');
        }
        if ($typeRaw === '2') {
            return Loc::getMessage('LEGATCOMPANYCOURT_TYPE_2');
        }
        return '';
    }

    /**
     * @param array<string, mixed> $row
     * @param array<int, string> $keys
     */
    private function pickScalar(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $row)) {
                continue;
            }
            $value = $row[$key];
            if ($value === null) {
                return '';
            }
            if (\is_array($value)) {
                $encoded = LegatByClient::jsonUtf8($value);
                return $encoded !== '' ? $encoded : '';
            }
            $trimmed = trim((string)$value);
            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return '';
    }

    private function normalizeDateString(string $value): string
    {
        return LegatByClient::normalizeLegatDate($value) ?? '';
    }
}
