# TASK-005: Legat — Юридические адреса (address)

**Дата создания:** 2026-04-06 00:00 (UTC+3, Брест)  
**Статус:** Завершена  
**Приоритет:** Высокий  
**Исполнитель:** Bitrix24 Программист (Vanilla JS)

## Описание
Реализовано кастомное activity `legatcompanyaddress`: сведения о юридических адресах компании из ЕГР по УНП через `GET /api2/by/address`.

## Модули и компоненты
- `local/activities/custom/legatcompanyaddress/` — activity
- `local/activities/custom/LegatByClient.php` — общий HTTP-клиент и ключ API

## API
- `GET https://api.legat.by/api2/by/address?unp=...&key=...`
- Ответ: `error`, `total`, `address[]` с полями `address`, `postcode`, `soato`, `date`, `date_end`, `active` (1/0).

## Критерии приёмки
- [x] Activity в каталоге `local/activities/custom/legatcompanyaddress`
- [x] Ключ API не в UI
- [x] Обработка ошибок HTTP/JSON/API
- [x] Даты `0000-00-00` не выводятся как валидные даты в текстовом блоке

## История правок
- 2026-04-06 (UTC+3, Брест): Добавлена реализация и TASK-005.
- 2026-04-06 (UTC+3, Брест): Задача переведена в статус «Завершена».
