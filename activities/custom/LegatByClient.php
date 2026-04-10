<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\HttpClient;

/**
 * Общие вызовы API api.legat.by для кастомных activity.
 */
final class LegatByClient
{
    public const BASE = 'https://api.legat.by';

    public static function getApiKey(): string
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

    /**
     * @return array{ok: bool, httpCode: int, data: ?array, raw: string, error: ?string}
     */
    public static function getJson(string $path, array $queryParams): array
    {
        $url = self::BASE . $path;
        $http = new HttpClient([
            'socketTimeout' => 30,
            'streamTimeout' => 30,
        ]);
        $http->setHeader('Accept', 'application/json', true);

        $urlWithQuery = $url . '?' . http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986);
        $body = $http->get($urlWithQuery);
        $status = (int)$http->getStatus();
        $raw = is_string($body) ? $body : '';

        if ($body === false || $status < 200 || $status >= 300) {
            return [
                'ok' => false,
                'httpCode' => $status,
                'data' => null,
                'raw' => $raw,
                'error' => 'http',
            ];
        }

        $data = json_decode($raw, true);
        if (!\is_array($data)) {
            return [
                'ok' => false,
                'httpCode' => $status,
                'data' => null,
                'raw' => $raw,
                'error' => 'json',
            ];
        }

        return [
            'ok' => true,
            'httpCode' => $status,
            'data' => $data,
            'raw' => $raw,
            'error' => null,
        ];
    }

    public static function normalizeLegatDate($value): ?string
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

    /**
     * @param mixed $value
     */
    public static function jsonUtf8($value): string
    {
        if (!\is_array($value) && !\is_object($value)) {
            return '';
        }
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $json !== false ? $json : '';
    }
}
