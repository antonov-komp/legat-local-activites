<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\Activity\PropertiesDialog;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\HttpClient;

class CBPlegatcompanyinfo extends BaseActivity
{
    private const API_URL = 'https://api.legat.by/api2/by/data';

    /** @var string[] */
    private static array $outputPropertyKeys = [
        'AdditionalInfo',
        'LegatGeneral',
        'LegatTaxAuthority',
        'LegatRegistration',
        'LegatStatusBlock',
        'LegatFundBlock',
        'LegatCourtsBlock',
        'LegatProcurementBlock',
        'LegatSalesBlock',
        'LegatConsumerAndChecksBlock',
        'LegatBankruptBlock',
        'LegatDebtBlock',
        'LegatFinanceBlock',
        'LegatRiskRegistersBlock',
        'LegatLicensesBlock',
        'LegatCountersBlock',
    ];

    public function __construct($name)
    {
        parent::__construct($name);

        $props = [
            'Title' => '',
            'UNP' => '',
        ];
        foreach (self::$outputPropertyKeys as $key) {
            $props[$key] = null;
        }
        $this->arProperties = $props;

        $types = [
            'UNP' => ['Type' => FieldType::STRING],
        ];
        foreach (self::$outputPropertyKeys as $key) {
            $types[$key] = ['Type' => FieldType::STRING];
        }
        $this->SetPropertiesTypes($types);
    }

    protected static function getFileName(): string
    {
        return __FILE__;
    }

    protected function internalExecute(): ErrorCollection
    {
        $errors = parent::internalExecute();

        $unp = trim((string)$this->UNP);
        foreach (self::$outputPropertyKeys as $key) {
            $this->preparedProperties[$key] = '';
        }

        if ($unp === '') {
            $this->preparedProperties['AdditionalInfo'] = Loc::getMessage('LEGATCOMPANYINFO_ERR_EMPTY_UNP');
            $errors[] = new \Bitrix\Main\Error(Loc::getMessage('LEGATCOMPANYINFO_ERR_EMPTY_UNP'), 'EMPTY_UNP');
            return $errors;
        }

        $apiKey = self::getApiKey();
        if ($apiKey === '') {
            $msg = Loc::getMessage('LEGATCOMPANYINFO_ERR_NO_API_KEY');
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'NO_API_KEY');
            return $errors;
        }

        $http = new HttpClient([
            'socketTimeout' => 30,
            'streamTimeout' => 30,
        ]);
        $http->setHeader('Accept', 'application/json', true);

        $url = self::API_URL . '?' . http_build_query([
                'unp' => $unp,
                'key' => $apiKey,
            ], '', '&', PHP_QUERY_RFC3986);

        $body = $http->get($url);
        $status = $http->getStatus();

        if ($body === false || $status < 200 || $status >= 300) {
            $msg = Loc::getMessage('LEGATCOMPANYINFO_ERR_HTTP', ['#CODE#' => (string)$status]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'HTTP_ERROR');
            $this->log($msg);
            return $errors;
        }

        $data = json_decode((string)$body, true);
        if (!is_array($data)) {
            $msg = Loc::getMessage('LEGATCOMPANYINFO_ERR_JSON');
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'INVALID_JSON');
            return $errors;
        }

        if (!empty($data['error'])) {
            $msg = Loc::getMessage('LEGATCOMPANYINFO_ERR_API', ['#MSG#' => (string)$data['error']]);
            $this->preparedProperties['AdditionalInfo'] = $msg;
            $errors[] = new \Bitrix\Main\Error($msg, 'API_ERROR');
            $this->log($msg);
            return $errors;
        }

        $this->preparedProperties['AdditionalInfo'] = Loc::getMessage('LEGATCOMPANYINFO_OK');
        $this->fillOutputFromPayload($data);
        $this->log($this->preparedProperties['AdditionalInfo'] . ' UNP=' . $unp);

        return $errors;
    }

    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        return [
            'UNP' => [
                'Name' => Loc::getMessage('LEGATCOMPANYINFO_ACTIVITY_FIELD_UNP'),
                'FieldName' => 'unp',
                'Type' => FieldType::STRING,
                'Required' => true,
                'Options' => [],
            ],
        ];
    }

    private static function getApiKey(): string
    {
        $keyFile = $_SERVER['DOCUMENT_ROOT'] . '/local/legat_api_key.php';
        if (is_file($keyFile)) {
            include_once $keyFile;
        }

        if (\defined('LEGAT_BY_DATA_API_KEY')) {
            $defined = (string)\constant('LEGAT_BY_DATA_API_KEY');
            if ($defined !== '') {
                return $defined;
            }
        }
        return (string)Option::get('main', 'legat_by_data_api_key', '');
    }

    private function fillOutputFromPayload(array $data): void
    {
        $d = $data['details'] ?? null;
        $this->preparedProperties['LegatGeneral'] = $this->formatGeneral(is_array($d) ? $d : []);
        $this->preparedProperties['LegatTaxAuthority'] = $this->formatTaxAuthority(is_array($d) ? $d : []);
        $this->preparedProperties['LegatRegistration'] = $this->formatRegistration(is_array($d) ? $d : []);
        $this->preparedProperties['LegatStatusBlock'] = $this->formatStatuses(is_array($d) ? $d : []);
        $this->preparedProperties['LegatFundBlock'] = $this->formatFund(is_array($d) ? $d : []);
        $this->preparedProperties['LegatCourtsBlock'] = $this->formatCourtsAndActional($data);
        $this->preparedProperties['LegatProcurementBlock'] = $this->formatProcurement($data);
        $this->preparedProperties['LegatSalesBlock'] = $this->formatSales($data['sales'] ?? null);
        $this->preparedProperties['LegatConsumerAndChecksBlock'] = $this->formatConsumerAndKgk($data);
        $this->preparedProperties['LegatBankruptBlock'] = $this->formatBankruptAndLiquidation($data);
        $this->preparedProperties['LegatDebtBlock'] = $this->formatDebt($data['debt'] ?? null);
        $this->preparedProperties['LegatFinanceBlock'] = $this->formatFinance($data['finance'] ?? null);
        $this->preparedProperties['LegatRiskRegistersBlock'] = $this->formatRiskRegisters($data);
        $this->preparedProperties['LegatLicensesBlock'] = $this->formatLicensesBlock($data);
        $this->preparedProperties['LegatCountersBlock'] = $this->formatCounters($data);
    }

    private function formatGeneral(array $d): string
    {
        $lines = [
            $this->line('Тип субъекта', $this->mapSubjectType($d['type'] ?? null)),
            $this->line('УНП', $d['unp'] ?? null),
            $this->line('Краткое наименование', $d['short'] ?? null),
            $this->line('Полное наименование', $d['full'] ?? null),
            $this->line('Юридический адрес', $d['address'] ?? null),
            $this->line('ОКПО', $d['okpo'] ?? null),
            $this->line('Почтовый индекс', $d['post_code'] ?? null),
            $this->line('Микро/мало (Белстат)', $this->mapOrganizationSize($d['organization'] ?? null)),
        ];
        return $this->joinLines($lines);
    }

    private function formatTaxAuthority(array $d): string
    {
        $lines = [
            $this->line('Код налоговой инспекции', $d['insp_code'] ?? null),
            $this->line('Наименование инспекции', $d['insp_name'] ?? null),
        ];
        return $this->joinLines($lines);
    }

    private function formatRegistration(array $d): string
    {
        $lines = [
            $this->line('Дата постановки на налоговый учёт', $this->normalizeDate($d['add_date'] ?? null)),
            $this->line('Дата государственной регистрации', $this->normalizeDate($d['date_reg'] ?? null)),
            $this->line('Регистрирующий орган', $d['reg_name'] ?? null),
            $this->line('Запрет на отчуждение доли', $this->boolRu($d['alienation'] ?? null)),
            $this->line('Информация о преобразовании ИП', $this->scalarOrJson($d['transformation'] ?? null)),
        ];
        return $this->joinLines($lines);
    }

    private function formatStatuses(array $d): string
    {
        $lines = [
            $this->line('Статус из реестра плательщиков МНС', $d['status_mns'] ?? null),
            $this->line('Идентификатор статуса МНС', $d['status_id'] ?? null),
            $this->line('Дата изменения состояния (МНС)', $this->normalizeDate($d['del_date'] ?? null)),
            $this->line('Статус ЕГР', $d['status_egr'] ?? null),
            $this->line('Идентификатор статуса ЕГР', $d['status_egr_id'] ?? null),
            $this->line('Дата исключения из ЕГР', $this->normalizeDate($d['del_date_egr'] ?? null)),
            $this->line('Ликвидация (пояснение)', $d['likv'] ?? null),
        ];
        return $this->joinLines($lines);
    }

    private function formatFund(array $d): string
    {
        $fund = $d['fund'] ?? null;
        if (!is_array($fund) || $fund === []) {
            return $this->joinLines([$this->line('ФСЗН', null)]);
        }
        $lines = [];
        foreach ($fund as $i => $row) {
            if (!is_array($row)) {
                continue;
            }
            $prefix = count($fund) > 1 ? 'Запись ' . ((int)$i + 1) . ': ' : '';
            $lines[] = $prefix . implode('; ', array_filter([
                    $this->part('УНПФ', $row['unpf'] ?? null),
                    $this->part('Категория плательщика', $this->mapPayerCategory($row['type'] ?? null)),
                    $this->part('Код подразделения ФСЗН', $row['fund_code'] ?? null),
                    $this->part('Подразделение ФСЗН', $row['fund_name'] ?? null),
                    $this->part('Дата постановки на учёт в ФСЗН', $this->normalizeDate($row['fund_date'] ?? null)),
                ], static fn($v) => $v !== null && $v !== ''));
        }
        return $this->joinLines($lines);
    }

    private function formatCourtsAndActional(array $data): string
    {
        $c = $data['courts'] ?? null;
        $a = $data['actional'] ?? null;
        $lines = [];
        if (is_array($c)) {
            $lines[] = $this->line('Приказное: заявления компании (взыскание с должника)', $c['claimant'] ?? null);
            $lines[] = $this->line('Приказное: заявления к компании', $c['debtor'] ?? null);
        }
        if (is_array($a)) {
            $lines[] = $this->line('Исковое: дел в роли истца', $a['ist'] ?? null);
            $lines[] = $this->line('Исковое: дел в роли ответчика', $a['otv'] ?? null);
            $lines[] = $this->line('Исковое: прочее', $a['dr'] ?? null);
        }
        return $this->joinLines($lines);
    }

    private function formatProcurement(array $data): string
    {
        $ag = $data['agreements'] ?? null;
        $ct = $data['contracts'] ?? null;
        $zk = $data['zakupki'] ?? null;
        $lines = [];
        if (is_array($ag)) {
            $lines[] = $this->line('Архив закупок: поставщик', $ag['supplier'] ?? null);
            $lines[] = $this->line('Архив закупок: заказчик', $ag['customer'] ?? null);
            $lines[] = $this->line('Архив закупок: участник (не выбран поставщиком)', $ag['other'] ?? null);
        }
        if (is_array($ct)) {
            $lines[] = $this->line('Реестр договоров: поставщик', $ct['provider'] ?? null);
            $lines[] = $this->line('Реестр договоров: заказчик', $ct['client'] ?? null);
        }
        if (is_array($zk)) {
            $lines[] = $this->line('Госзакупки: объявленные', $zk['declared'] ?? null);
            $lines[] = $this->line('Госзакупки: подведение итогов', $zk['results'] ?? null);
            $lines[] = $this->line('Госзакупки: завершённые', $zk['end'] ?? null);
        }
        return $this->joinLines($lines);
    }

    private function formatSales($sales): string
    {
        if (!is_array($sales) || $sales === []) {
            return $this->joinLines([$this->line('Торговые объекты', null)]);
        }
        $lines = [];
        foreach ($sales as $row) {
            if (!is_array($row)) {
                continue;
            }
            $lines[] = implode('; ', array_filter([
                    $this->part('Вид объекта', $row['objects'] ?? null),
                    $this->part('Идентификатор', $row['object_id'] ?? null),
                    $this->part('Количество', $row['count'] ?? null),
                ], static fn($v) => $v !== null && $v !== ''));
        }
        return $this->joinLines($lines);
    }

    private function formatConsumerAndKgk(array $data): string
    {
        $cons = $data['consumer'] ?? null;
        $kgk = $data['kgk'] ?? null;
        $lines = [];
        if (is_array($cons)) {
            $lines[] = $this->line('Бытовое обслуживание: объекты', $cons['obo'] ?? null);
            $lines[] = $this->line('Бытовое обслуживание: субъекты', $cons['sbo'] ?? null);
        }
        if (is_array($kgk)) {
            $lines[] = $this->line('Проверки: планируемые', $kgk['plan'] ?? null);
            $lines[] = $this->line('Проверки: завершённые', $kgk['end'] ?? null);
        }
        return $this->joinLines($lines);
    }

    private function formatBankruptAndLiquidation(array $data): string
    {
        $b = $data['bankrupt'] ?? null;
        $l = $data['liquidation'] ?? null;
        $lines = [];
        if (is_array($b)) {
            $lines[] = $this->line('Банкротство: показатель сведений', $b['status'] ?? null);
            $lines[] = $this->line('Банкротство: индикатор дела', $b['file_status'] ?? null);
        }
        $lines[] = $this->line('Сведения о ликвидации', $this->scalarOrJson($l));
        return $this->joinLines($lines);
    }

    private function formatDebt($debt): string
    {
        if ($debt === null) {
            return $this->joinLines([$this->line('Задолженность (даты)', null)]);
        }
        if (is_array($debt)) {
            $dates = [];
            foreach ($debt as $item) {
                if ($item !== null && $item !== '') {
                    $dates[] = (string)$item;
                }
            }
            return $this->joinLines([$this->line('Задолженность (даты на 1-е число месяца)', $dates === [] ? null : implode(', ', $dates))]);
        }
        return $this->joinLines([$this->line('Задолженность', (string)$debt)]);
    }

    private function formatFinance($fin): string
    {
        if (!is_array($fin)) {
            return $this->joinLines([$this->line('Финансовая отчётность', null)]);
        }
        $lines = [
            $this->line('Отчётный год', $fin['year'] ?? null),
            $this->line('Выручка', $fin['proceeds'] ?? null),
            $this->line('Чистая прибыль', $fin['profit_all'] ?? null),
        ];
        return $this->joinLines($lines);
    }

    private function formatRiskRegisters(array $data): string
    {
        $lines = [
            $this->line('ФСЗН (задолженность)', $this->scalarOrJson($data['fszn'] ?? null)),
            $this->line('НГБ (повышенный риск)', $this->scalarOrJson($data['ngb'] ?? null)),
            $this->line('Банковские гарантии (НБРБ)', $this->scalarOrJson($data['nbrb'] ?? null)),
            $this->line('Аренда госимущества (Минск/Могилёв/Брест)', $this->scalarOrJson($data['tenant'] ?? null)),
            $this->line('Реестр недобросовестных поставщиков', $this->scalarOrJson($data['unscrupulous'] ?? null)),
            $this->line('Резидентство в СЭЗ', $this->scalarOrJson($data['sez'] ?? null)),
        ];
        return $this->joinLines($lines);
    }

    private function formatLicensesBlock(array $data): string
    {
        $lic = $data['lic'] ?? null;
        $lines = [];
        if (is_array($lic)) {
            $lines[] = $this->line('Лицензии', $this->scalarOrJson($lic['license'] ?? null));
            $lines[] = $this->line('Разрешения', $this->scalarOrJson($lic['permission'] ?? null));
        }
        $lines[] = $this->line('Аттестаты соответствия', $this->scalarOrJson($data['att'] ?? null));
        $lines[] = $this->line('Членство в БелТПП', $this->scalarOrJson($data['beltpp'] ?? null));
        $lines[] = $this->line('Сертификаты и декларации', $this->scalarOrJson($data['cert'] ?? null));
        return $this->joinLines($lines);
    }

    private function formatCounters(array $data): string
    {
        $lines = [
            $this->line('Товарные знаки (кол-во)', $data['signs'] ?? null),
            $this->line('Филиалы и представительства в РФ', $data['foreign_branch_rf'] ?? null),
            $this->line('Продукция в реестре промтоваров', $data['industrial_products'] ?? null),
            $this->line('ПО в реестре ПО', $data['soft_registry'] ?? null),
        ];
        return $this->joinLines($lines);
    }

    private function line(string $label, $value): string
    {
        $v = $this->emptyLabel($value);
        return $label . ': ' . $v;
    }

    private function part(string $label, $value): ?string
    {
        $v = $this->emptyLabel($value);
        if ($v === '—') {
            return null;
        }
        return $label . ': ' . $v;
    }

    private function joinLines(array $lines): string
    {
        $lines = array_values(array_filter(array_map('trim', $lines), static fn($s) => $s !== ''));
        return implode("\n", $lines);
    }

    private function emptyLabel($value): string
    {
        if ($value === null) {
            return '—';
        }
        if (is_string($value)) {
            $t = trim($value);
            return ($t === '' || $t === '0000-00-00') ? '—' : $t;
        }
        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }
        if (is_numeric($value)) {
            return (string)$value;
        }
        return '—';
    }

    private function normalizeDate($value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string)$value);
        if ($s === '' || $s === '0000-00-00') {
            return null;
        }
        return $s;
    }

    private function boolRu($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value)) {
            return $value ? 'Да' : 'Нет';
        }
        return (string)$value;
    }

    private function scalarOrJson($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value) || is_object($value)) {
            $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return $json !== false ? $json : null;
        }
        $s = trim((string)$value);
        return $s === '' ? null : $s;
    }

    private function mapSubjectType($type): ?string
    {
        if ($type === null || $type === '') {
            return null;
        }
        $map = [
            0 => 'Отсутствует в ЕГР',
            1 => 'Юридическое лицо',
            2 => 'Индивидуальный предприниматель',
        ];
        return $map[(int)$type] ?? (string)$type;
    }

    private function mapPayerCategory($type): ?string
    {
        if ($type === null || $type === '') {
            return null;
        }
        $map = [
            1 => 'Юридическое лицо',
            2 => 'ИП за себя',
            3 => 'ИП за наёмных работников',
        ];
        return $map[(int)$type] ?? (string)$type;
    }

    private function mapOrganizationSize($organization): ?string
    {
        if (!isset($organization) || $organization === '') {
            return null;
        }
        $map = [
            0 => 'Нет данных / не относится',
            1 => 'Микропредприятие',
            2 => 'Малое предприятие',
        ];
        return $map[(int)$organization] ?? (string)$organization;
    }
}
