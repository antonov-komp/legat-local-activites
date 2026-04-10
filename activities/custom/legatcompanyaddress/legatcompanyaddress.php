<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

require_once dirname(__DIR__) . '/LegatByClient.php';

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

class CBPlegatcompanyaddress extends BaseActivity
{
    private const PATH = '/api2/by/address';

    public function __construct($name)
    {
        parent::__construct($name);

        $this->arProperties = [
            'Title' => '',
            'UNP' => '',
            'AdditionalInfo' => null,
            'LegatAddressTotal' => null,
            'LegatAddressList' => null,
            'LegatAddressJson' => null,
        ];

        $this->SetPropertiesTypes([
            'UNP' => ['Type' => FieldType::STRING],
            'AdditionalInfo' => ['Type' => FieldType::STRING],
            'LegatAddressTotal' => ['Type' => FieldType::STRING],
            'LegatAddressList' => ['Type' => FieldType::STRING],
            'LegatAddressJson' => ['Type' => FieldType::STRING],
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
        $this->preparedProperties['LegatAddressTotal'] = '';
        $this->preparedProperties['LegatAddressList'] = '';
        $this->preparedProperties['LegatAddressJson'] = '';

        $unp = trim((string)$this->UNP);
        if ($unp === '') {
            $msg = Loc::getMessage('LEGATCOMPANYADDRESS_ERR_EMPTY_UNP');
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'EMPTY_UNP');
            return $errors;
        }

        $apiKey = LegatByClient::getApiKey();
        if ($apiKey === '') {
            $msg = Loc::getMessage('LEGATCOMPANYADDRESS_ERR_NO_API_KEY');
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
            $msg = Loc::getMessage('LEGATCOMPANYADDRESS_ERR_API', ['#MSG#' => (string)$data['error']]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'API_ERROR');
            $this->log($msg);
            return $errors;
        }

        $this->preparedProperties['AdditionalInfo'] = Loc::getMessage('LEGATCOMPANYADDRESS_OK');
        $total = $data['total'] ?? null;
        $this->preparedProperties['LegatAddressTotal'] = $total === null || $total === '' ? '' : (string)$total;

        $list = $data['address'] ?? null;
        $listArr = \is_array($list) ? $list : [];
        $this->preparedProperties['LegatAddressJson'] = LegatByClient::jsonUtf8($listArr);
        $this->preparedProperties['LegatAddressList'] = $this->formatAddressList($listArr);

        $this->log($this->preparedProperties['AdditionalInfo'] . ' UNP=' . $unp);

        return $errors;
    }

    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        return [
            'UNP' => [
                'Name' => Loc::getMessage('LEGATCOMPANYADDRESS_FIELD_UNP'),
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
            $msg = Loc::getMessage('LEGATCOMPANYADDRESS_ERR_HTTP', ['#CODE#' => (string)$result['httpCode']]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'HTTP_ERROR');
            $this->log($msg);
            return $errors;
        }

        $msg = Loc::getMessage('LEGATCOMPANYADDRESS_ERR_JSON');
        $this->preparedProperties['AdditionalInfo'] = $msg;
        $errors[] = new \Bitrix\Main\Error($msg, 'INVALID_JSON');
        return $errors;
    }

    /**
     * @param list<mixed> $rows
     */
    private function formatAddressList(array $rows): string
    {
        if ($rows === []) {
            return '';
        }
        $blocks = [];
        foreach ($rows as $row) {
            if (!\is_array($row)) {
                continue;
            }
            $addr = $row['address'] ?? '';
            $post = $row['postcode'] ?? '';
            $soato = $row['soato'] ?? '';
            $date = LegatByClient::normalizeLegatDate($row['date'] ?? null);
            $dateEnd = LegatByClient::normalizeLegatDate($row['date_end'] ?? null);
            $active = $row['active'] ?? null;
            $activeLabel = $active === 1 || $active === '1'
                ? Loc::getMessage('LEGATCOMPANYADDRESS_ACTIVE_YES')
                : (($active === 0 || $active === '0') ? Loc::getMessage('LEGATCOMPANYADDRESS_ACTIVE_NO') : '');

            $parts = [
                $addr !== '' ? Loc::getMessage('LEGATCOMPANYADDRESS_LINE_ADDR', ['#A#' => (string)$addr]) : null,
                $post !== '' ? Loc::getMessage('LEGATCOMPANYADDRESS_LINE_POST', ['#P#' => (string)$post]) : null,
                $soato !== '' ? Loc::getMessage('LEGATCOMPANYADDRESS_LINE_SOATO', ['#S#' => (string)$soato]) : null,
                $date !== null ? Loc::getMessage('LEGATCOMPANYADDRESS_LINE_DATE_REG', ['#D#' => $date]) : null,
                $dateEnd !== null ? Loc::getMessage('LEGATCOMPANYADDRESS_LINE_DATE_END', ['#D#' => $dateEnd]) : null,
                $activeLabel !== '' ? Loc::getMessage('LEGATCOMPANYADDRESS_LINE_ACTIVE', ['#ACTIVE#' => $activeLabel]) : null,
            ];
            $block = implode("\n", array_filter($parts, static fn($p) => $p !== null && trim((string)$p) !== ''));
            if ($block !== '') {
                $blocks[] = $block;
            }
        }
        return implode("\n\n", $blocks);
    }
}
