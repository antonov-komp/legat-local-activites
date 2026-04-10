# Документация: Кастомные действия бизнес-процессов Битрикс24

> 📚 **Основа основ:** [Книга разработчика Битрикс24](https://github.com/gromdron/bx24devbook)

Данный раздел содержит документацию по разработке, структуре и принципам работы кастомных действий (activities) для бизнес-процессов Битрикс24.

---

## 📖 Структура документации

| Файл | Описание |
|------|----------|
| [ARCHITECTURE.md](./ARCHITECTURE.md) | Архитектура бизнес-процессов и действий: как всё устроено изнутри |
| [ACTIVITY_STRUCTURE.md](./ACTIVITY_STRUCTURE.md) | Структура файлов кастомного действия, назначение каждого файла |
| [HELLOWORLD_EXAMPLE.md](./HELLOWORLD_EXAMPLE.md) | Разбор действия `helloworldactivity` — как работает, что делает |
| [ROADMAP.md](./ROADMAP.md) | Планы развития: что будет реализовано дальше |

---

## 🏗️ Краткое введение

### Что такое бизнес-процесс?

Бизнес-процесс (БП) — это последовательность **действий** (activities), выполняемых системой в определённом порядке. БП привязываются к сущностям (лиды, сделки, контакты, задачи, документы и т.д.) и позволяют автоматизировать бизнес-логику.

### Что такое действие (activity)?

**Действие** — это атомарный элемент бизнес-процесса. Каждое действие:
- Имеет **входные параметры** (properties)
- Выполняет **логику** в методе `internalExecute()`
- Может **возвращать значения** для использования в последующих действиях

### Где хранятся кастомные действия?

```
local/activities/custom/<имя_действия>/
├── .description.php          # Описание действия
├── <имя_действия>.php        # Основной PHP-класс
└── lang/ru/                  # Локализация
    ├── .description.php
    └── <имя_действия>.php
```

---

## 🔑 Ключевые понятия из Книги разработчика

### 1. Базовый класс действия

Все кастомные действия наследуются от `Bitrix\Bizproc\Activity\BaseActivity`:

```php
class CBPMyActivity extends BaseActivity
{
    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'Title' => '',
            // ваши свойства
        ];
        $this->SetPropertiesTypes([
            // типы свойств
        ]);
    }
}
```

### 2. Файл описания `.description.php`

Определяет метаданные действия:

```php
$arActivityDescription = [
    "NAME" => "Название действия",
    "DESCRIPTION" => "Описание",
    "TYPE" => "activity",
    "CLASS" => "MyActivityClassName",
    "JSCLASS" => "BizProcActivity",
    "CATEGORY" => ["ID" => "other"],
    "RETURN" => [
        "ResultVar" => [
            "NAME" => "Имя возвращаемого значения",
            "TYPE" => "string",
        ],
    ],
];
```

### 3. Типы полей (FieldType)

| Константа | Тип данных | Описание |
|-----------|-----------|----------|
| `FieldType::STRING` | строка | Однострочное текстовое поле |
| `FieldType::TEXT` | текст | Многострочное текстовое поле |
| `FieldType::INT` | число | Целое число |
| `FieldType::DOUBLE` | число | Дробное число |
| `FieldType::BOOL` | булево | Да/Нет |
| `FieldType::SELECT` | список | Выпадающий список |
| `FieldType::USER` | пользователь | Выбор пользователя |

### 4. Метод `internalExecute()`

Главный метод — здесь выполняется основная логика действия:

```php
protected function internalExecute(): ErrorCollection
{
    $errors = parent::internalExecute();
    
    // Ваша логика здесь
    $this->preparedProperties['ResultVar'] = 'Результат';
    
    return $errors;
}
```

### 5. Метод `getPropertiesDialogMap()`

Определяет поля диалога настройки действия:

```php
public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
{
    return [
        'PropertyName' => [
            'Name' => 'Название поля',
            'FieldName' => 'property_name',
            'Type' => FieldType::STRING,
            'Required' => true,
            'Default' => 'значение по умолчанию',
        ],
    ];
}
```

---

## 📚 Полезные ссылки

- [Книга разработчика Битрикс24 (GitHub)](https://github.com/gromdron/bx24devbook)
- [Документация 1С-Битрикс: Бизнес-процессы](https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=99)
- [D7 Kernel](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=98)
