# Legat. Контакты по типу и источнику

Activity для БП: `GET /api2/by/contactsTypeSource` по УНП.

Входы:
- `UNP` (обязательный)
- `PAGE` (необязательный, по умолчанию `1`)

Выходы:
- `REQUEST_STATUS`
- `TOTAL`
- `PAGE`
- `CONTACTS_ITEMS` (нормализованный JSON с расшифровками)
- `CONTACTS_GROUPED_BY_TYPE` (JSON)
- `CONTACTS_GROUPED_BY_SOURCE` (JSON)
- `CONTACTS_RAW_JSON` (сырой массив `contacts`, JSON)
- `CONTACTS_LIST_TEXT` (все контакты блоками текста)
- `CONTACTS_BY_TYPE_TEXT` (секции по типу контакта)
- `CONTACTS_BY_SOURCE_TEXT` (секции по источнику)
