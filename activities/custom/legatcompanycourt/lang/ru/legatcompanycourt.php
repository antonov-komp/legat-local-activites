<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$MESS['LEGATCOMPANYCOURT_FIELD_UNP'] = 'УНП';
$MESS['LEGATCOMPANYCOURT_FIELD_TYPE'] = 'Тип стороны (необязательно)';
$MESS['LEGATCOMPANYCOURT_FIELD_PAGE'] = 'Страница (необязательно, целое ≥ 1)';
$MESS['LEGATCOMPANYCOURT_TYPE_ALL'] = 'Не указывать';
$MESS['LEGATCOMPANYCOURT_TYPE_1'] = '1 — компания взыскатель';
$MESS['LEGATCOMPANYCOURT_TYPE_2'] = '2 — компания должник';
$MESS['LEGATCOMPANYCOURT_ERR_EMPTY_UNP'] = 'Не указан УНП.';
$MESS['LEGATCOMPANYCOURT_ERR_BAD_TYPE'] = 'Некорректный тип стороны: допустимо 1 или 2, либо пусто.';
$MESS['LEGATCOMPANYCOURT_ERR_BAD_PAGE'] = 'Некорректная страница: укажите целое число ≥ 1 или оставьте пустым.';
$MESS['LEGATCOMPANYCOURT_ERR_NO_API_KEY'] = 'Не задан ключ API Legat: local/legat_api_key.php или Option main.legat_by_data_api_key.';
$MESS['LEGATCOMPANYCOURT_ERR_HTTP'] = 'Ошибка HTTP при обращении к Legat (код: #CODE#).';
$MESS['LEGATCOMPANYCOURT_ERR_JSON'] = 'Некорректный JSON в ответе Legat.';
$MESS['LEGATCOMPANYCOURT_ERR_API'] = 'Ошибка API Legat: #MSG#';
$MESS['LEGATCOMPANYCOURT_OK'] = 'Запрос к Legat выполнен успешно.';
$MESS['LEGATCOMPANYCOURT_EMPTY_CLAIMANT'] = '(нет данных, claimant = null)';
