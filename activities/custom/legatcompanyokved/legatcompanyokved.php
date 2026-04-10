<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require_once dirname(__DIR__) . '/LegatByClient.php';

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

class CBPlegatcompanyokved extends BaseActivity
{
    private const PATH = '/api2/by/okved';

    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Title' => '',
            'UNP' => '',
            'AdditionalInfo' => null,
            'LegatOkvedTotal' => null,
            'LegatOkvedList' => null,
            'LegatOkvedJson' => null,
        ];

        $this->SetPropertiesTypes([
            'UNP' => ['Type' => FieldType::STRING],
            'AdditionalInfo' => ['Type' => FieldType::STRING],
            'LegatOkvedTotal' => ['Type' => FieldType::STRING],
            'LegatOkvedList' => ['Type' => FieldType::STRING],
            'LegatOkvedJson' => ['Type' => FieldType::STRING],
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
        $this->preparedProperties['LegatOkvedTotal'] = '';
        $this->preparedProperties['LegatOkvedList'] = '';
        $this->preparedProperties['LegatOkvedJson'] = '';

        $unp = trim((string)$this->UNP);
        if ($unp === '') {
            $msg = Loc::getMessage('LEGATCOMPANYOKVED_ERR_EMPTY_UNP');
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'EMPTY_UNP');
            return $errors;
        }

        $apiKey = LegatByClient::getApiKey();
        if ($apiKey === '') {
            $msg = Loc::getMessage('LEGATCOMPANYOKVED_ERR_NO_API_KEY');
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'NO_API_KEY');
            return $errors;
        }

        $result = LegatByClient::getJson(self::PATH, [
            'unp' => $unp,
            'key' => $apiKey,
        ]);

        if (!$result['ok'] || $result['data'] === null) {
            return $this->applyHttpJsonError($errors, $result);
        }

        $data = $result['data'];
        if (!empty($data['error'])) {
            $msg = Loc::getMessage('LEGATCOMPANYOKVED_ERR_API', ['#MSG#' => (string)$data['error']]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'API_ERROR');
            $this->log($msg);
            return $errors;
        }

        $this->preparedProperties['AdditionalInfo'] = Loc::getMessage('LEGATCOMPANYOKVED_OK');
        $total = $data['total'] ?? null;
        $this->preparedProperties['LegatOkvedTotal'] = $total === null || $total === '' ? '' : (string)$total;

        $list = $data['okved'] ?? null;
        $listArr = \is_array($list) ? $list : [];
        $this->preparedProperties['LegatOkvedJson'] = LegatByClient::jsonUtf8($listArr);
        $this->preparedProperties['LegatOkvedList'] = $this->formatOkvedList($listArr);

        $this->log($this->preparedProperties['AdditionalInfo'] . ' UNP=' . $unp);

        return $errors;
    }

    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        return [
            'UNP' => [
                'Name' => Loc::getMessage('LEGATCOMPANYOKVED_FIELD_UNP'),
                'FieldName' => 'unp',
                'Type' => FieldType::STRING,
                'Required' => true,
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
            $msg = Loc::getMessage('LEGATCOMPANYOKVED_ERR_HTTP', ['#CODE#' => (string)$result['httpCode']]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'HTTP_ERROR');
            $this->log($msg);
            return $errors;
        }

        $msg = Loc::getMessage('LEGATCOMPANYOKVED_ERR_JSON');
        $this->preparedProperties['AdditionalInfo'] = $msg;
        $errors[] = new \Bitrix\Main\Error($msg, 'INVALID_JSON');
        return $errors;
    }

    /**
     * @param list<mixed> $rows
     */
    private function formatOkvedList(array $rows): string
    {
        if ($rows === []) {
            return '';
        }
        $blocks = [];
        foreach ($rows as $i => $row) {
            if (!\is_array($row)) {
                continue;
            }
            $code = $row['code'] ?? '';
            $name = $row['name'] ?? '';
            $begin = LegatByClient::normalizeLegatDate($row['date_begin'] ?? null);
            $end = LegatByClient::normalizeLegatDate($row['date_end'] ?? null);
            $active = $row['active'] ?? null;
            $activeLabel = $active === 1 || $active === '1'
                ? Loc::getMessage('LEGATCOMPANYOKVED_ACTIVE_YES')
                : (($active === 0 || $active === '0') ? Loc::getMessage('LEGATCOMPANYOKVED_ACTIVE_NO') : '');

            $parts = [
                Loc::getMessage('LEGATCOMPANYOKVED_LINE_CODE', ['#CODE#' => (string)$code]),
                $name !== '' ? (string)$name : null,
                $begin !== null
                    ? Loc::getMessage('LEGATCOMPANYOKVED_LINE_BEGIN', ['#DATE#' => $begin])
                    : null,
                $end !== null
                    ? Loc::getMessage('LEGATCOMPANYOKVED_LINE_END', ['#DATE#' => $end])
                    : null,
                $activeLabel !== '' ? Loc::getMessage('LEGATCOMPANYOKVED_LINE_ACTIVE', ['#ACTIVE#' => $activeLabel]) : null,
            ];
            $line = implode("\n", array_filter($parts, static fn($p) => $p !== null && trim((string)$p) !== ''));
            if ($line !== '') {
                $blocks[] = $line;
            }
        }
        return implode("\n\n", $blocks);
    }
}
