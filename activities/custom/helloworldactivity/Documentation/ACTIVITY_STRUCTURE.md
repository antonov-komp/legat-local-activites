# Структура файлов кастомного действия

> 📖 Основано на главе «Разработка» и «Модуль Бизнес-процессы» из [Книги разработчика Битрикс24](https://github.com/gromdron/bx24devbook)

---

## 1. Полная структура директории действия

```
local/activities/custom/<имя_действия>/
│
├── .description.php              # ← Метаданные действия
├── <имя_действия>.php            # ← Основной PHP-класс с логикой
│
└── lang/                         # ← Директория локализации
    └── ru/                       # ← Русская локализация
        ├── .description.php      # ← Перевод описания
        └── <имя_действия>.php    # ← Перевод полей и сообщений
```

---

## 2. Файл `.description.php`

### 2.1 Назначение

Определяет **метаданные** действия: как оно отображается в конструкторе БП, какой класс отвечает за логику, какие значения возвращает.

### 2.2 Полный шаблон

```php
<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use \Bitrix\Main\Localization\Loc;

$arActivityDescription = [
    // ── Основная информация ──────────────────────────────
    "NAME" => Loc::getMessage("MYACTIVITY_DESCR_NAME"),        // Название в конструкторе
    "DESCRIPTION" => Loc::getMessage("MYACTIVITY_DESCR_DESCR"), // Описание в конструкторе
    
    // ── Технические параметры ────────────────────────────
    "TYPE" => "activity",           // Тип: activity | workflow | sequential
    "CLASS" => "CBPMyActivity",     // Имя PHP-класса
    "JSCLASS" => "BizProcActivity", // JS-класс для конструктора
    "CATEGORY" => [
        "ID" => "other",            // Категория действия
    ],
    
    // ── Возвращаемые значения ────────────────────────────
    "RETURN" => [
        "ResultVar" => [
            "NAME" => Loc::getMessage("MYACTIVITY_DESCR_FIELD_RESULT"),
            "TYPE" => "string",     // string | int | double | bool | date | user
        ],
        // Можно указать несколько возвращаемых значений
    ],
    
    // ── Дополнительные настройки ─────────────────────────
    // "IS_MODIFIED" => true,       // Флаг модификации
    // "NEED_CLOSING" => true,      // Требует закрытия
];
```

### 2.3 Описание полей

| Поле | Обязательно | Описание |
|------|-------------|----------|
| `NAME` | ✅ | Отображаемое название действия |
| `DESCRIPTION` | ✅ | Краткое описание |
| `TYPE` | ✅ | Тип действия (обычно `activity`) |
| `CLASS` | ✅ | Имя PHP-класса (должен совпадать с файлом) |
| `JSCLASS` | ✅ | JS-класс (обычно `BizProcActivity`) |
| `CATEGORY` | ✅ | Категория в палитре конструктора |
| `RETURN` | ❌ | Возвращаемые значения |

---

## 3. Основной PHP-файл `<имя_действия>.php`

### 3.1 Назначение

Содержит **класс действия** — всю бизнес-логику: обработку параметров, выполнение, возврат значений.

### 3.2 Полный шаблон класса

```php
<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Bizproc\Activity\BaseActivity;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Bizproc\Activity\PropertiesDialog;

class CBPMyActivity extends BaseActivity
{
    /**
     * Конструктор действия
     * 
     * @param string $name Имя действия
     */
    public function __construct($name)
    {
        parent::__construct($name);

        // ── Определение свойств ─────────────────────────
        $this->arProperties = [
            'Title' => '',              // Обязательное свойство
            'Param1' => '',             // Ваш параметр 1
            'Param2' => null,           // Ваш параметр 2
            'Result' => null,           // Возвращаемое значение
        ];

        // ── Определение типов свойств ───────────────────
        $this->SetPropertiesTypes([
            'Param1' => [
                'Type' => FieldType::STRING,
            ],
            'Param2' => [
                'Type' => FieldType::INT,
            ],
            'Result' => [
                'Type' => FieldType::STRING,
            ],
        ]);
    }

    /**
     * Возвращает путь к файлу действия
     * 
     * @return string
     */
    protected static function getFileName(): string
    {
        return __FILE__;
    }

    /**
     * Основная логика выполнения действия
     * 
     * @return ErrorCollection
     */
    protected function internalExecute(): ErrorCollection
    {
        $errors = parent::internalExecute();

        // ── Ваша логика здесь ──────────────────────────
        $result = $this->processData($this->Param1, $this->Param2);
        
        // ── Установка возвращаемого значения ────────────
        $this->preparedProperties['Result'] = $result;
        
        // ── Логирование ────────────────────────────────
        $this->log('Результат: ' . $result);

        return $errors;
    }

    /**
     * Конфигурация диалога настройки действия
     * 
     * @param PropertiesDialog|null $dialog
     * @return array[]
     */
    public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
    {
        $map = [
            'Param1' => [
                'Name' => Loc::getMessage('MYACTIVITY_FIELD_PARAM1'),
                'FieldName' => 'param1',
                'Type' => FieldType::STRING,
                'Required' => true,
                'Default' => Loc::getMessage('MYACTIVITY_DEFAULT_PARAM1'),
                'Options' => [],
                'Description' => Loc::getMessage('MYACTIVITY_DESC_PARAM1'),
            ],
            'Param2' => [
                'Name' => Loc::getMessage('MYACTIVITY_FIELD_PARAM2'),
                'FieldName' => 'param2',
                'Type' => FieldType::INT,
                'Required' => false,
                'Default' => 0,
                'Options' => [],
            ],
        ];
        
        return $map;
    }

    /**
     * Пример вспомогательного метода
     */
    private function processData($param1, $param2): string
    {
        return $param1 . ' (значение: ' . $param2 . ')';
    }
}
```

### 3.3 Разбор ключевых методов

#### `__construct($name)`

**Вызывается:** при создании экземпляра действия  
**Назначение:** инициализация свойств и определение их типов

```php
public function __construct($name)
{
    parent::__construct($name);
    
    // Свойства по умолчанию
    $this->arProperties = [
        'Title' => '',
        'MyProperty' => 'default_value',
    ];
    
    // Типы свойств
    $this->SetPropertiesTypes([
        'MyProperty' => ['Type' => FieldType::STRING],
    ]);
}
```

#### `getFileName()`

**Вызывается:** системой для определения пути к файлу  
**Назначение:** должен вернуть `__FILE__`

```php
protected static function getFileName(): string
{
    return __FILE__;
}
```

#### `internalExecute()`

**Вызывается:** при выполнении действия в БП  
**Назначение:** **основная бизнес-логика**

```php
protected function internalExecute(): ErrorCollection
{
    $errors = parent::internalExecute();
    
    // 1. Получение параметров
    $param = $this->MyProperty;
    
    // 2. Выполнение логики
    $result = some_function($param);
    
    // 3. Установка возвращаемого значения
    $this->preparedProperties['ReturnValue'] = $result;
    
    // 4. Логирование
    $this->log('Выполнено: ' . $result);
    
    // 5. (опционально) Добавление ошибок
    if ($result === false) {
        $errors[] = new \Bitrix\Main\Error('Ошибка обработки');
    }
    
    return $errors;
}
```

#### `getPropertiesDialogMap()`

**Вызывается:** при открытии диалога настройки действия  
**Назначение:** определение полей для ввода параметров

```php
public static function getPropertiesDialogMap(?PropertiesDialog $dialog = null): array
{
    return [
        'PropertyName' => [
            'Name' => 'Название поля',
            'FieldName' => 'property_name',
            'Type' => FieldType::STRING,
            'Required' => true,
            'Default' => 'default',
            'Options' => [],
            'Description' => 'Подсказка',
        ],
    ];
}
```

---

## 4. Файлы локализации

### 4.1 `lang/ru/.description.php`

Перевод названия и описания действия:

```php
<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$MESS['MYACTIVITY_DESCR_NAME']  = 'Моё действие';
$MESS['MYACTIVITY_DESCR_DESCR'] = 'Описание моего действия';
$MESS['MYACTIVITY_DESCR_FIELD_RESULT'] = 'Результат выполнения';
```

### 4.2 `lang/ru/<имя_действия>.php`

Перевод полей диалога и сообщений:

```php
<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$MESS['MYACTIVITY_FIELD_PARAM1'] = 'Параметр 1';
$MESS['MYACTIVITY_FIELD_PARAM2'] = 'Параметр 2';
$MESS['MYACTIVITY_DEFAULT_PARAM1'] = 'Значение по умолчанию';
$MESS['MYACTIVITY_DESC_PARAM1'] = 'Описание параметра';
$MESS['MYACTIVITY_LOG_MESSAGE'] = 'Действие выполнено: #RESULT#';
```

---

## 5. Именование и соглашения

### 5.1 Именование файлов и классов

| Элемент | Правило | Пример |
|---------|---------|--------|
| Директория | `lowercase_with_underscores` | `my_custom_activity` |
| PHP-файл | `<имя>.php` | `my_custom_activity.php` |
| Класс | `CBP<Imya>` | `CBPMyCustomActivity` |
| Описание | `.description.php` | `.description.php` |

### 5.2 Префиксы и суффиксы

```
Класс действия:    CBP<Name>Activity
Файл описания:     .description.php
Языковой файл:     <name>.php
```

### 5.3 Пространство имён

Кастомные действия **не используют** namespace — это требование системы автозагрузки Битрикс.

---

## 6. Проверка работоспособности

### 6.1 Чек-лист перед установкой

- [ ] `.description.php` существует и корректен
- [ ] PHP-класс назван правильно и наследует `BaseActivity`
- [ ] `getFileName()` возвращает `__FILE__`
- [ ] `internalExecute()` реализован
- [ ] `getPropertiesDialogMap()` возвращает массив
- [ ] Языковые файлы заполнены
- [ ] Возвращаемые значения описаны в `.description.php`

### 6.2 Установка действия

1. Скопируйте директорию действия в:
   ```
   local/activities/custom/<имя_действия>/
   ```

2. Очистите кеш Битрикс (опционально):
   ```php
   BXClearCache(true);
   ```

3. Перейдите в конструктор бизнес-процессов → действие должно появиться в палитре

---

## 7. Типичные ошибки

### 7.1 Действие не появляется в палитре

**Причины:**
- Неверный `CLASS` в `.description.php`
- Класс не наследует `BaseActivity`
- Ошибка синтаксиса в PHP-файле

**Решение:**
- Проверьте логи PHP (`bitrix/modules/main/php_interface/logs/`)
- Проверьте совпадение имени класса

### 7.2 Поля диалога не отображаются

**Причины:**
- `getPropertiesDialogMap()` не возвращает массив
- Неверный формат массива

**Решение:**
- Убедитесь, что каждый элемент имеет `Name`, `FieldName`, `Type`

### 7.3 Возвращаемое значение пустое

**Причины:**
- Не описано в `.description.php`
- Не установлено в `preparedProperties`

**Решение:**
- Добавьте в `RETURN` секцию `.description.php`
- Установите `$this->preparedProperties['VarName'] = $value`
