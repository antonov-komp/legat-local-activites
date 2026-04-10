# Legat. Приказное производство / суд

Activity для БП Битрикс24.

- **УНП** (обязательно);
- **Тип стороны** (необязательно): `1` — компания взыскатель, `2` — компания должник;
- **Страница** (необязательно): целое число `>= 1`, по умолчанию используется `1`.

Запрос: `GET https://api.legat.by/api2/by/court`

Выходы:
- статус запроса;
- `total` (количество записей);
- примененный тип стороны;
- обработанная страница;
- нормализованный список дел (`LegatCourtItemsJson`, JSON);
- сырой JSON-ответ API;
- `claimant` в текстовом и JSON-виде.

Формат `LegatCourtItemsJson` (элемент):
- `role`
- `case_number`
- `date`
- `decision`
- `sum`
- `claimant_name`
- `claimant_unp`
- `debtor_name`
- `debtor_unp`
- `court_region`
- `judge`
- `year`
- `month`
- `is_refusal`
