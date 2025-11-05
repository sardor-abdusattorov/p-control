# Права для менеджеров (Manager Permissions)

## Какие права нужно дать менеджерам для виджетов

### 1. ApplicationWidget (Заявки)
Виджет заявок показывается если у пользователя есть **хотя бы одно** из этих прав:
- `view application` - просмотр заявок
- `view all applications` - просмотр всех заявок
- `create application` - создание заявок

**Рекомендуемые права для менеджеров:**
```php
$manager = Role::findByName('manager');
$manager->givePermissionTo([
    'view application',
    'create application',
    'update application',
    'delete application',
    'submit application',
]);
```

---

### 2. ContractWidget (Контракты)
Виджет контрактов показывается если у пользователя есть **хотя бы одно** из этих прав:
- `view contract` - просмотр контрактов
- `view all contracts` - просмотр всех контрактов
- `create contract` - создание контрактов

**Рекомендуемые права для менеджеров:**
```php
$manager = Role::findByName('manager');
$manager->givePermissionTo([
    'view contract',
    'create contract',
    'update contract',
    'delete contract',
    'submit contract',
]);
```

---

### 3. ProductWidget (Продукты)
Виджет продуктов показывается если у пользователя есть **хотя бы одно** из этих прав:
- `view products` - просмотр продуктов
- `manage products` - управление продуктами

**Рекомендуемые права для менеджеров:**
```php
$manager = Role::findByName('manager');
$manager->givePermissionTo([
    'view products',
    // Опционально, если нужно полное управление:
    // 'manage products',
    // 'create products',
    // 'update products',
    // 'delete products',
]);
```

---

## Быстрая настройка через Tinker

```bash
php artisan tinker
```

Затем выполнить:

```php
// Получить роль менеджера
$manager = Spatie\Permission\Models\Role::findByName('manager');

// Дать все права для заявок
$manager->givePermissionTo([
    'view application',
    'create application',
    'update application',
    'delete application',
    'submit application',
]);

// Дать все права для контрактов
$manager->givePermissionTo([
    'view contract',
    'create contract',
    'update contract',
    'delete contract',
    'submit contract',
]);

// Дать права на просмотр продуктов
$manager->givePermissionTo('view products');

// Проверить что права назначены
$manager->permissions->pluck('name');
```

---

## Что показывает каждый виджет

### ApplicationWidget
- Общее количество заявок
- Новые заявки (только для superadmin и manager)
- В процессе
- Одобренные
- Отклонённые
- Кнопка "View All" → переход на страницу заявок

### ContractWidget
- Общее количество контрактов
- Новые контракты (только для superadmin и manager)
- В процессе
- Одобренные
- Отклонённые
- Кнопка "View All" → переход на страницу контрактов

### ProductWidget
- Общее количество продуктов пользователя
- Активные продукты
- Неактивные продукты
- Список последних 5 продуктов с деталями:
  - Название
  - Категория и бренд
  - Серийный номер (S/N)
  - Инвентарный номер (INV)
- Кнопка "View All" → переход на страницу продуктов

---

## Примечания

1. **Менеджеры видят только свои данные:**
   - Свои заявки
   - Свои контракты
   - Свои продукты

2. **Superadmin видит всё** (встроенная логика в виджетах)

3. **Для других ролей** (lawyer, accountant, etc.) - виджеты работают согласно логике Approvals

4. **Если у пользователя нет ни одного требуемого права** - виджет не показывается

---

## Проверка прав

Проверить какие виджеты видит пользователь:

```bash
php artisan widgets:check USER_ID
```

Эта команда покажет:
- ✓ Какие права есть у пользователя
- ✓ Какие виджеты будут показаны
- ✓ Сколько данных в каждом виджете
