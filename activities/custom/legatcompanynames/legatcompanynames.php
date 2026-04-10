<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require_once dirname(__DIR__) . '/LegatByClient.php';

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

class CBPlegatcompanynames extends BaseActivity
{
    private const PATH = '/api2/by/names';

    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Title' => '',
            'UNP' => '',
            'AdditionalInfo' => null,
            'LegatNamesTotal' => null,
            'LegatNamesList' => null,
            'LegatNamesJson' => null,
        ];

        $this->SetPropertiesTypes([
            'UNP' => ['Type' => FieldType::STRING],
            'AdditionalInfo' => ['Type' => FieldType::STRING],
            'LegatNamesTotal' => ['Type' => FieldType::STRING],
            'LegatNamesList' => ['Type' => FieldType::STRING],
            'LegatNamesJson' => ['Type' => FieldType::STRING],
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
        $this->preparedProperties['LegatNamesTotal'] = '';
        $this->preparedProperties['LegatNamesList'] = '';
        $this->preparedProperties['LegatNamesJson'] = '';

        $unp = trim((string)$this->UNP);
        if ($unp === '') {
            $msg = Loc::getMessage('LEGATCOMPANYNAMES_ERR_EMPTY_UNP');
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'EMPTY_UNP');
            return $errors;
        }

        $apiKey = LegatByClient::getApiKey();
        if ($apiKey === '') {
            $msg = Loc::getMessage('LEGATCOMPANYNAMES_ERR_NO_API_KEY');
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
            $msg = Loc::getMessage('LEGATCOMPANYNAMES_ERR_API', ['#MSG#' => (string)$data['error']]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'API_ERROR');
            $this->log($msg);
            return $errors;
        }

        $this->preparedProperties['AdditionalInfo'] = Loc::getMessage('LEGATCOMPANYNAMES_OK');
        $total = $data['total'] ?? null;
        $this->preparedProperties['LegatNamesTotal'] = $total === null || $total === '' ? '' : (string)$total;

        $list = $data['names'] ?? null;
        $listArr = \is_array($list) ? $list : [];
        $this->preparedProperties['LegatNamesJson'] = LegatByClient::jsonUtf8($listArr);
        $this->preparedProperties['LegatNamesList'] = $this->formatNamesList($listArr);

        $this->log($this->preparedProperties['AdditionalInfo'] . ' UNP=' . $unp);

        return $errors;
    }

    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        return [
            'UNP' => [
                'Name' => Loc::getMessage('LEGATCOMPANYNAMES_FIELD_UNP'),
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
            $msg = Loc::getMessage('LEGATCOMPANYNAMES_ERR_HTTP', ['#CODE#' => (string)$result['httpCode']]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'HTTP_ERROR');
            $this->log($msg);
            return $errors;
        }

        $msg = Loc::getMessage('LEGATCOMPANYNAMES_ERR_JSON');
        $this->preparedProperties['AdditionalInfo'] = $msg;
        $errors[] = new \Bitrix\Main\Error($msg, 'INVALID_JSON');
        return $errors;
    }

    /**
     * @param list<mixed> $rows
     */
    private function formatNamesList(array $rows): string
    {
        if ($rows === []) {
            return '';
        }
        $blocks = [];
        foreach ($rows as $row) {
            if (!\is_array($row)) {
                continue;
            }

            $full = trim((string)($row['full'] ?? ''));
            $date = LegatByClient::normalizeLegatDate($row['date'] ?? null);
            $dateEnd = LegatByClient::normalizeLegatDate($row['date_end'] ?? null);
            $active = $row['active'] ?? null;
            $activeLabel = $active === 1 || $active === '1'
                ? Loc::getMessage('LEGATCOMPANYNAMES_ACTIVE_YES')
                : (($active === 0 || $active === '0') ? Loc::getMessage('LEGATCOMPANYNAMES_ACTIVE_NO') : '');

            $parts = [
                $full !== '' ? Loc::getMessage('LEGATCOMPANYNAMES_LINE_FULL', ['#FULL#' => $full]) : null,
                $date !== null ? Loc::getMessage('LEGATCOMPANYNAMES_LINE_DATE', ['#DATE#' => $date]) : null,
                $dateEnd !== null ? Loc::getMessage('LEGATCOMPANYNAMES_LINE_DATE_END', ['#DATE#' => $dateEnd]) : null,
                $activeLabel !== '' ? Loc::getMessage('LEGATCOMPANYNAMES_LINE_ACTIVE', ['#ACTIVE#' => $activeLabel]) : null,
            ];

            $line = implode("\n", array_filter($parts, static fn($p) => $p !== null && trim((string)$p) !== ''));
            if ($line !== '') {
                $blocks[] = $line;
            }
        }

        return implode("\n\n", $blocks);
    }
}
