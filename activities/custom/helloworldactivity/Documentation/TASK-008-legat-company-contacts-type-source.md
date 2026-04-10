# TASK-008: Legat — Контакты по типу и источнику (contactsTypeSource)

**Дата создания:** 2026-04-06 00:00 (UTC+3, Брест)  
**Статус:** Новая  
**Приоритет:** Высокий  
**Исполнитель:** Bitrix24 Программист (Vanilla JS)

## Описание
Создать новое кастомное activity для бизнес-процессов Битрикс24, которое получает контакты компании с типом контакта и типом источника через метод Legat `GET /api2/by/contactsTypeSource` по УНП и возвращает нормализованные данные в выходные поля activity.

## Контекст
Метод `contactsTypeSource()` возвращает список контактов с классификацией:
- по типу контакта (`contact_type`);
- по типу источника (`reestr_type`).

Нужно реализовать отдельное activity по аналогии с уже существующими задачами Legat (`data`, `contacts`, `court`, `okved`, `address`, `names`, `directors`), с обязательной обработкой пагинации и сохранением полной структуры ответа.

Ключ API должен оставаться скрытым от UI:
- приоритетно из `local/legat_api_key.php`;
- fallback: `Option::get('main', 'legat_by_data_api_key')`.

## Модули и компоненты
- `local/activities/custom/legatcompanycontactstypesource/.description.php` — метаданные activity
- `local/activities/custom/legatcompanycontactstypesource/legatcompanycontactstypesource.php` — основной класс activity
- `local/activities/custom/legatcompanycontactstypesource/lang/ru/.description.php` — локализация описания
- `local/activities/custom/legatcompanycontactstypesource/lang/ru/legatcompanycontactstypesource.php` — локализация полей и ошибок
- `local/activities/custom/legatcompanycontactstypesource/README.md` — документация по использованию
- `local/activities/custom/LegatByClient.php` — переиспользуемый клиент Legat
- `local/legat_api_key.php` — источник ключа API

## Зависимости
- Доступность API Legat: `https://api.legat.by/api2/by/contactsTypeSource`
- Наличие ключа API (`key`)
- Наличие УНП (`unp`) во входных данных БП
- Поддержка пагинации через `page`
- Механизм custom activity Битрикс24

## Ступенчатые подзадачи
1. Создать activity `legatcompanycontactstypesource` по образцу существующих activity Legat.
2. Настроить `.description.php` (входы/выходы/имя activity/категория).
3. Добавить локализацию (`lang/ru`) для подписей и сообщений ошибок.
4. Реализовать входные параметры:
   - `UNP` (обязательный),
   - `PAGE` (необязательный, integer).
5. Реализовать получение API-ключа (файл + fallback Option).
6. Выполнить GET-запрос к `https://api.legat.by/api2/by/contactsTypeSource` с параметрами `key`, `unp`, `page`.
7. Реализовать обработку ошибок: пустой УНП, отсутствие ключа, HTTP-ошибка, невалидный JSON, `error` в ответе API.
8. Преобразовать `contacts[]` в нормализованную структуру для выходов activity.
9. Добавить справочные поля:
   - расшифровка `contact_type`,
   - расшифровка `reestr_type`.
10. Добавить служебные выходы `REQUEST_STATUS`, `TOTAL`, `PAGE`.
11. Добавить логирование без утечки `key`.
12. Обновить README и провести ручное тестирование в БП.

## API-методы (Legat)
- `GET /api2/by/contactsTypeSource`
- Полный URL: `https://api.legat.by/api2/by/contactsTypeSource`

Параметры запроса:
- `key` (string, required) — ключ доступа
- `unp` (string, required) — учетный номер (УНП)
- `page` (integer, optional) — страница

Передача ключа:
- GET-параметр `key`, либо
- HTTP Header `key: {$api_key}`.

Пример запроса:
`https://api.legat.by/api2/by/contactsTypeSource?unp=692172505&key=***`

## Набор возвращаемых полей
Верхний уровень ответа:
- `error`
- `total`
- `contacts`

По каждой записи `contacts[]`:
- `contact` — контакт
- `contact_type` — тип контакта:
  - `1` — телефон
  - `2` — факс
  - `3` — email
  - `4` — сайт
- `reestr_type` — тип источника:
  - `1` — контакты субъекта из торгового реестра
  - `2` — контакты объекта торгового реестра
  - `3` — тендерные закупки
  - `4` — объекты бытового обслуживания
  - `5` — БЕЛТПП
  - `6` — интернет-источники
  - `7` — реестр сертификатов
  - `8` — реестр лизинговых организаций
  - `9` — реестр программ для ЭВМ государств ЕАЭС
  - `10` — ЕГР

## Пример ответа сервера
```json
{
  "error": null,
  "total": 6,
  "contacts": [
    {
      "contact": "tender@liftmann.by",
      "contact_type": 3,
      "reestr_type": 3
    },
    {
      "contact": "+375293956987",
      "contact_type": 1,
      "reestr_type": 3
    }
  ]
}
```

## Технические требования
- Платформа: Bitrix24 custom activity.
- Реализация: PHP, без внешних JS-фреймворков.
- Ключ API не хранить в репозитории и не выводить в интерфейс.
- Поддержать множественные записи контактов, включая повторяющиеся значения с разными источниками.
- Обязательно добавить человекочитаемую расшифровку кодов `contact_type` и `reestr_type`.
- Поле `REQUEST_STATUS` обязательно в любом исходе (успех/ошибка).

## Предлагаемая структура выходных полей
- `REQUEST_STATUS` — статус выполнения (`Успешно` / сообщение ошибки)
- `TOTAL` — общее количество записей
- `PAGE` — обработанная страница
- `CONTACTS_ITEMS` — список контактов (массив/JSON) с кодами и расшифровками
- `CONTACTS_GROUPED_BY_TYPE` — опционально сгруппированный вывод по типу контакта
- `CONTACTS_GROUPED_BY_SOURCE` — опционально сгруппированный вывод по источнику
- `CONTACTS_RAW_JSON` — опционально сырой JSON ответа

## Критерии приёмки
- [ ] Activity появляется в конструкторе БП и запускается без ошибок.
- [ ] При валидном УНП выполняется запрос к `GET /api2/by/contactsTypeSource`.
- [ ] Обрабатывается необязательный параметр `PAGE`.
- [ ] Корректно возвращаются поля `contact`, `contact_type`, `reestr_type`.
- [ ] Коды `contact_type` и `reestr_type` корректно расшифровываются.
- [ ] Ошибки API/сети отображаются в `REQUEST_STATUS`.
- [ ] Ключ API не отображается в UI и логах.

## Тестирование
1. Запуск activity с валидным УНП без `PAGE`.
2. Запуск activity с валидным УНП и `PAGE=2`.
3. Проверка `TOTAL`, `CONTACTS_ITEMS`, расшифровок типов и источников.
4. Проверка дубликатов контактов с разными `reestr_type`.
5. Сценарий с пустым/невалидным УНП.
6. Сценарий без ключа API.
7. Сценарий недоступности API (таймаут/5xx).

## История правок
- 2026-04-06 (UTC+3, Брест): Создана постановка задачи для метода `contactsTypeSource()` (документ TASK-008).
