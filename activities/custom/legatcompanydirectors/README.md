# Legat. Руководители компании

Activity для БП: `GET /api2/by/directors` по УНП.

Входы:
- `UNP` (обязательный)
- `PAGE` (необязательный, по умолчанию `1`)

Выходы:
- `REQUEST_STATUS`
- `TOTAL`
- `DIRECTORS_PAGE`
- `DIRECTORS_ITEMS` (нормализованный JSON)
- `DIRECTORS_ACTIVE_ONLY` (только `active = 1`, JSON)
- `DIRECTORS_RAW_JSON` (сырой массив `directors`, JSON)
- `DIRECTORS_LIST_TEXT` (тот же состав записей, человекочитаемый текст)
- `DIRECTORS_ACTIVE_LIST_TEXT` (только активные записи, текст)
