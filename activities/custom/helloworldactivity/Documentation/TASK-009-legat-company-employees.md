# TASK-009: Legat — Среднесписочная численность работников (employees)

**Дата создания:** 2026-04-06 00:00 (UTC+3, Брест)  
**Статус:** Новая  
**Приоритет:** Высокий  
**Исполнитель:** Bitrix24 Программист (Vanilla JS)

## Описание
Создать новое кастомное activity для бизнес-процессов Битрикс24, которое получает сведения о среднесписочной численности работников компании через метод Legat `GET /api2/by/employees` по УНП и возвращает данные в выходные поля activity.

## Контекст
Метод `employees()` возвращает исторические значения численности по годам.  
По спецификации Legat информация доступна только для субъектов, по которым ранее выполнялся запрос в ОАИС.

Нужно добавить отдельное activity в линейке Legat-методов (`data`, `contacts`, `court`, `okved`, `address`, `names`, `directors`, `contactsTypeSource`) без изменения уже реализованных activity.

Ключ API должен оставаться скрытым от UI:
- приоритетно из `local/legat_api_key.php`;
- fallback: `Option::get('main', 'legat_by_data_api_key')`.

## Модули и компоненты
- `local/activities/custom/legatcompanyemployees/.description.php` — метаданные activity
- `local/activities/custom/legatcompanyemployees/legatcompanyemployees.php` — основной класс activity
- `local/activities/custom/legatcompanyemployees/lang/ru/.description.php` — локализация описания
- `local/activities/custom/legatcompanyemployees/lang/ru/legatcompanyemployees.php` — локализация полей и ошибок
- `local/activities/custom/legatcompanyemployees/README.md` — документация по использованию
- `local/activities/custom/LegatByClient.php` — переиспользуемый клиент Legat
- `local/legat_api_key.php` — источник ключа API

## Зависимости
- Доступность API Legat: `https://api.legat.by/api2/by/employees`
- Наличие ключа API (`key`)
- Наличие УНП (`unp`) во входных данных БП
- Условие доступности данных в Legat (предварительные запросы в ОАИС)
- Механизм custom activity Битрикс24

## Ступенчатые подзадачи
1. Создать activity `legatcompanyemployees` по образцу существующих activity Legat.
2. Настроить `.description.php` с входом `UNP` и выходными полями.
3. Добавить локализацию (`lang/ru`) для названий и ошибок.
4. Реализовать получение API-ключа (файл + fallback Option).
5. Выполнить GET-запрос к `https://api.legat.by/api2/by/employees` с параметрами `key`, `unp`.
6. Реализовать обработку ошибок: пустой УНП, отсутствие ключа, HTTP-ошибка, невалидный JSON, `error` в ответе API.
7. Преобразовать `employees[]` в нормализованный выход (список записей по годам).
8. Добавить служебные поля `REQUEST_STATUS`, `TOTAL`.
9. Добавить дополнительные агрегаты для БП (опционально: последнее значение, максимум, минимум).
10. Добавить логирование без утечки чувствительных данных.
11. Обновить README и провести ручное тестирование в БП.

## API-методы (Legat)
- `GET /api2/by/employees`
- Полный URL: `https://api.legat.by/api2/by/employees`

Параметры запроса:
- `key` (string, required) — ключ доступа
- `unp` (string, required) — учетный номер

Передача ключа:
- GET-параметр `key`, либо
- HTTP Header `key: {$api_key}`.

Пример запроса:
`https://api.legat.by/api2/by/employees?unp=692172505&key=***`

## Набор возвращаемых полей
Верхний уровень ответа:
- `error`
- `total`
- `employees`

По каждой записи `employees[]`:
- `strength` — численность
- `year` — год
- `date` — дата обновления/фиксации значения

## Пример ответа сервера
```json
{
  "error": null,
  "total": 5,
  "employees": [
    {
      "strength": 40,
      "year": 2025,
      "date": "2025-12-04"
    },
    {
      "strength": 24,
      "year": 2024,
      "date": "2024-12-31"
    }
  ]
}
```

## Технические требования
- Платформа: Bitrix24 custom activity.
- Реализация: PHP, без внешних JS-фреймворков.
- Ключ API не хранить в репозитории и не выводить в интерфейс.
- Поддержать сценарий отсутствия данных (например, субъект не обработан в ОАИС): корректный статус и пустой список.
- Значения `strength` и `year` возвращать в стабильных типах (число/строка по договоренности, зафиксировать в README).
- Поле `REQUEST_STATUS` обязательно в любом сценарии (успех/ошибка).

## Предлагаемая структура выходных полей
- `REQUEST_STATUS` — статус выполнения (`Успешно` / сообщение ошибки)
- `TOTAL` — общее количество записей
- `EMPLOYEES_ITEMS` — список записей (массив/JSON): `strength`, `year`, `date`
- `EMPLOYEES_LATEST` — последняя доступная запись (опционально)
- `EMPLOYEES_MAX_STRENGTH` — максимальная численность (опционально)
- `EMPLOYEES_MIN_STRENGTH` — минимальная численность (опционально)
- `EMPLOYEES_RAW_JSON` — опционально сырой JSON ответа

## Критерии приёмки
- [ ] Activity появляется в конструкторе БП и запускается без ошибок.
- [ ] При валидном УНП выполняется запрос к `GET /api2/by/employees`.
- [ ] Корректно возвращаются поля `strength`, `year`, `date`.
- [ ] Сценарий отсутствия данных обрабатывается без падения (пустой список + корректный статус).
- [ ] Ошибки API/сети отображаются в `REQUEST_STATUS`.
- [ ] Ключ API не отображается в UI и логах.

## Тестирование
1. Запуск activity с валидным УНП с непустым списком `employees`.
2. Проверка `TOTAL` и структуры `EMPLOYEES_ITEMS`.
3. Проверка корректности агрегатов (`EMPLOYEES_LATEST`, `MAX`, `MIN`) при их реализации.
4. Проверка кейса с отсутствием данных (если доступен тестовый УНП).
5. Сценарий с пустым/невалидным УНП.
6. Сценарий без ключа API.
7. Сценарий недоступности API (таймаут/5xx).

## История правок
- 2026-04-06 (UTC+3, Брест): Создана постановка задачи для метода `employees()` (документ TASK-009).
