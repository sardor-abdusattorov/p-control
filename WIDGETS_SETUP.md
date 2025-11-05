# Widget System - Setup Guide

## Установка прав для виджетов

### Шаг 1: Добавить права в базу данных

Запустить seeder для добавления прав products:

```bash
php artisan db:seed --class=ProductPermissionsSeeder
```

Это создаст следующие права:
- `view products` - просмотр продуктов
- `manage products` - управление продуктами
- `create products` - создание продуктов
- `update products` - редактирование продуктов
- `delete products` - удаление продуктов

### Шаг 2: Назначить права пользователю или роли

#### Вариант A: Через Tinker

```bash
php artisan tinker
```

Затем выполнить:

```php
// Назначить права роли
$role = Spatie\Permission\Models\Role::findByName('manager');
$role->givePermissionTo('view products');
$role->givePermissionTo('manage products');

// ИЛИ назначить права конкретному пользователю
$user = App\Models\User::find(1); // Замените 1 на нужный ID
$user->givePermissionTo('view products');
```

#### Вариант B: Через админ-панель
Если у вас есть UI для управления правами - назначьте права через интерфейс.

### Шаг 3: Проверить настройку виджетов

```bash
php artisan widgets:check
```

Или для конкретного пользователя:

```bash
php artisan widgets:check 1
```

Эта команда покажет:
- ✓ Есть ли права в базе
- ✓ Назначены ли права пользователю
- ✓ Есть ли продукты в базе
- ✓ Работает ли WidgetService
- ✓ Сколько виджетов вернется для пользователя

## Как работает ProductWidget

- **Виджет показывается**, если у пользователя есть хотя бы одно из прав:
  - `view products` ИЛИ
  - `manage products`

- **Superadmin** видит все продукты всех пользователей
- **Обычные пользователи** видят только свои продукты

## Как добавить новый виджет

1. Создайте класс, наследующий `App\Services\Widgets\AbstractWidget`
2. Реализуйте обязательные методы:
   - `getData()` - возвращает данные виджета
   - `getTitle()` - название виджета
   - `getType()` - тип виджета (уникальный идентификатор)
3. Опционально определите:
   - `$permissions` - массив прав доступа
   - `getIcon()` - иконка виджета
   - `getColor()` - цвет темы виджета
4. Добавьте класс в `$availableWidgets` в `App\Services\WidgetService`

## Troubleshooting

### Виджеты не появляются?

1. **Проверьте права**:
   ```bash
   php artisan widgets:check
   ```

2. **Убедитесь что права назначены пользователю/роли**

3. **Проверьте что есть продукты в базе данных**

4. **Очистите кэш**:
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan view:clear
   ```

5. **Проверьте консоль браузера на ошибки JavaScript**

### Виджет пустой?

- Убедитесь что у пользователя есть продукты (поле `user_id` в таблице `products`)
- Для superadmin проверьте что есть продукты вообще в базе
