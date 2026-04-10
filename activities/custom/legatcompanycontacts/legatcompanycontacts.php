<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require_once dirname(__DIR__) . '/LegatByClient.php';

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

class CBPlegatcompanycontacts extends BaseActivity
{
    private const PATH = '/api2/by/contacts';

    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Title' => '',
            'UNP' => '',
            'AdditionalInfo' => null,
            'LegatPhones' => null,
            'LegatFax' => null,
            'LegatEmails' => null,
            'LegatSites' => null,
            'LegatContactsJson' => null,
        ];

        $this->SetPropertiesTypes([
            'UNP' => ['Type' => FieldType::STRING],
            'AdditionalInfo' => ['Type' => FieldType::STRING],
            'LegatPhones' => ['Type' => FieldType::STRING],
            'LegatFax' => ['Type' => FieldType::STRING],
            'LegatEmails' => ['Type' => FieldType::STRING],
            'LegatSites' => ['Type' => FieldType::STRING],
            'LegatContactsJson' => ['Type' => FieldType::STRING],
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
        $this->preparedProperties['LegatPhones'] = '';
        $this->preparedProperties['LegatFax'] = '';
        $this->preparedProperties['LegatEmails'] = '';
        $this->preparedProperties['LegatSites'] = '';
        $this->preparedProperties['LegatContactsJson'] = '';

        $unp = trim((string)$this->UNP);
        if ($unp === '') {
            $msg = Loc::getMessage('LEGATCOMPANYCONTACTS_ERR_EMPTY_UNP');
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'EMPTY_UNP');
            return $errors;
        }

        $apiKey = LegatByClient::getApiKey();
        if ($apiKey === '') {
            $msg = Loc::getMessage('LEGATCOMPANYCONTACTS_ERR_NO_API_KEY');
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
            $msg = Loc::getMessage('LEGATCOMPANYCONTACTS_ERR_API', ['#MSG#' => (string)$data['error']]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'API_ERROR');
            $this->log($msg);
            return $errors;
        }

        $contacts = $data['contacts'] ?? null;
        if (!\is_array($contacts)) {
            $contacts = [];
        }

        $this->preparedProperties['AdditionalInfo'] = Loc::getMessage('LEGATCOMPANYCONTACTS_OK');
        $this->preparedProperties['LegatPhones'] = $this->formatStringList($contacts['phone'] ?? null);
        $fax = $contacts['fax'] ?? null;
        $this->preparedProperties['LegatFax'] = ($fax === null || $fax === '') ? '' : trim((string)$fax);
        $this->preparedProperties['LegatEmails'] = $this->formatStringList($contacts['email'] ?? null);
        $this->preparedProperties['LegatSites'] = $this->formatStringList($contacts['site'] ?? null);
        $this->preparedProperties['LegatContactsJson'] = LegatByClient::jsonUtf8($contacts);

        $this->log($this->preparedProperties['AdditionalInfo'] . ' UNP=' . $unp);

        return $errors;
    }

    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        return [
            'UNP' => [
                'Name' => Loc::getMessage('LEGATCOMPANYCONTACTS_FIELD_UNP'),
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
            $msg = Loc::getMessage('LEGATCOMPANYCONTACTS_ERR_HTTP', ['#CODE#' => (string)$result['httpCode']]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'HTTP_ERROR');
            $this->log($msg);
            return $errors;
        }

        $msg = Loc::getMessage('LEGATCOMPANYCONTACTS_ERR_JSON');
        $this->preparedProperties['AdditionalInfo'] = $msg;
        $errors[] = new \Bitrix\Main\Error($msg, 'INVALID_JSON');
        return $errors;
    }

    /**
     * @param mixed $list
     */
    private function formatStringList($list): string
    {
        if (!\is_array($list) || $list === []) {
            return '';
        }
        $lines = [];
        foreach ($list as $item) {
            if ($item === null || $item === '') {
                continue;
            }
            $lines[] = trim((string)$item);
        }
        return implode("\n", $lines);
    }
}
