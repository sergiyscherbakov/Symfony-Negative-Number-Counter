# Symfony Negative Number Counter

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white) ![Symfony](https://img.shields.io/badge/Symfony-7.3-000000?style=for-the-badge&logo=symfony&logoColor=white) ![PostgreSQL](https://img.shields.io/badge/PostgreSQL-17-336791?style=for-the-badge&logo=postgresql&logoColor=white) ![Doctrine](https://img.shields.io/badge/Doctrine-3-FC6A31?style=for-the-badge&logo=doctrine&logoColor=white) ![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white) ![Twig](https://img.shields.io/badge/Twig-3-339933?style=for-the-badge&logo=twig&logoColor=white) ![Composer](https://img.shields.io/badge/Composer-2.0-885630?style=for-the-badge&logo=composer&logoColor=white)

## Автор

**Розробник:** Сергій Щербаков
**Email:** sergiyscherbakov@ukr.net
**Telegram:** @s_help_2010

### 💰 Підтримати розробку
Задонатити на каву USDT (BINANCE SMART CHAIN):
**`0xDFD0A23d2FEd7c1ab8A0F9A4a1F8386832B6f95A`**

---

Веб-застосунок на Symfony 7.3 + PostgreSQL 17 для підрахунку кількості від'ємних елементів у числових масивах.

## Що це за проєкт?

Цей застосунок дозволяє:
- Вводити числові масиви через кому або генерувати випадкові числа
- Автоматично підраховувати кількість від'ємних чисел у масиві
- Зберігати результати у базі даних PostgreSQL
- Переглядати історію попередніх обчислень

## Вимоги до системи

### Необхідне ПЗ:
- **PHP:** >= 8.2.12
- **Composer:** >= 2.8.11
- **PostgreSQL:** >= 17.0
- **Веб-сервер:** PHP Built-in Server або Apache/Nginx

### Перевірка встановлених версій:
```bash
php --version          # PHP 8.2.12
composer --version     # Composer 2.8.11
psql --version        # PostgreSQL 17.6
```

## Встановлення та налаштування

### Крок 1: Клонування репозиторію

```bash
git clone https://github.com/sergiyscherbakov/Symfony-Negative-Number-Counter.git
cd Symfony-Negative-Number-Counter
cd negative-counter
```

### Крок 2: Встановлення залежностей

```bash
composer install
```

### Крок 3: Налаштування підключення до PostgreSQL

Відредагуйте файл `.env` у директорії `negative-counter/`:

```env
DATABASE_URL="postgresql://postgres:1234@127.0.0.1:5432/negative_counter?serverVersion=17&charset=utf8"
```

**Параметри підключення:**
- **Користувач:** postgres
- **Пароль:** 1234 (замініть на свій пароль)
- **Хост:** 127.0.0.1
- **Порт:** 5432
- **База даних:** negative_counter
- **Версія PostgreSQL:** 17

### Крок 4: Створення бази даних

```bash
php bin/console doctrine:database:create --if-not-exists
```

**Очікуваний результат:**
```
Created database "negative_counter" for connection named default
```

### Крок 5: Виконання міграцій

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

Це створить необхідні таблиці у базі даних.

### Крок 6: Запуск веб-сервера

```bash
php -S localhost:8000 -t public
```

**Відкрийте у браузері:** http://localhost:8000/

## Перевірка роботи

### Використання застосунку:

1. Відкрийте http://localhost:8000/ у браузері
2. Введіть числа через кому, наприклад: `-5, 10, -3, 7, -1, 0, 15`
3. Натисніть "Обчислити"
4. Результат з'явиться в таблиці історії

### Альтернатива - Генератор випадкових чисел:

1. У правій колонці вкажіть кількість чисел (1-100)
2. Вкажіть мінімум та максимум (наприклад: -50 до 50)
3. Натисніть "Згенерувати числа"
4. Автоматично згенеровані числа з'являться у полі введення

## Перевірка даних у PostgreSQL

### Підключення до psql вручну:

```bash
psql -U postgres
```

Введіть пароль (за замовчуванням: `1234`)

### Команди для перевірки бази даних:

```sql
-- Підключитися до бази даних
\c negative_counter

-- Показати всі таблиці
\dt

-- Показати структуру таблиці number_array
\d number_array

-- Показати всі записи
SELECT * FROM number_array;

-- Показати останні 10 записів
SELECT * FROM number_array ORDER BY created_at DESC LIMIT 10;

-- Показати записи з найбільшою кількістю від'ємних чисел
SELECT * FROM number_array ORDER BY negative_count DESC LIMIT 5;

-- Підрахувати загальну кількість записів
SELECT COUNT(*) FROM number_array;

-- Вийти з psql
\q
```

### Використання Symfony Console для запитів до БД:

```bash
# Виконати SQL запит через Symfony
php bin/console doctrine:query:sql "SELECT * FROM number_array"

# Підрахувати кількість записів
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM number_array"

# Показати останні 5 записів
php bin/console doctrine:query:sql "SELECT * FROM number_array ORDER BY created_at DESC LIMIT 5"
```

## Структура бази даних

### Таблиця: number_array

| Колонка         | Тип       | Опис                              |
|-----------------|-----------|-----------------------------------|
| id              | SERIAL    | Первинний ключ (автоінкремент)    |
| numbers         | TEXT      | Числа через кому                  |
| negative_count  | INTEGER   | Кількість від'ємних чисел         |
| created_at      | TIMESTAMP | Дата та час створення             |

### Приклад даних:

```sql
INSERT INTO number_array (numbers, negative_count, created_at)
VALUES ('34, 39, 28, -23, -27, -32', 3, '2025-10-24 12:20:26');
```

## Що зберігається у БД?

Кожен раз, коли ви обчислюєте кількість від'ємних чисел, у базі даних PostgreSQL створюється новий запис з наступною інформацією:

1. **numbers** - вхідний масив чисел у текстовому форматі (наприклад: "34, 39, 28, -23, -27, -32")
2. **negative_count** - результат обчислення (кількість від'ємних чисел)
3. **created_at** - точна дата та час створення запису

### Приклад перевірки:

```bash
# 1. Запустіть застосунок
php -S localhost:8000 -t public

# 2. У іншому терміналі підключіться до PostgreSQL
psql -U postgres -d negative_counter

# 3. Виконайте запит
SELECT id, numbers, negative_count, created_at FROM number_array ORDER BY created_at DESC LIMIT 5;

# Ви побачите щось подібне:
#  id |           numbers            | negative_count |     created_at
# ----+------------------------------+----------------+---------------------
#   5 | 34, 39, 28, -23, -27, -32    |              3 | 2025-10-24 12:20:26
#   4 | -5, 10, -3, 7, -1, 0, 15     |              3 | 2025-10-24 12:15:10
#   3 | 1, 2, 3, -4, -5              |              2 | 2025-10-24 12:10:05
```

## Корисні команди

### Запуск та зупинка

```bash
# Запустити сервер
php -S localhost:8000 -t public

# Зупинити сервер: Ctrl+C
```

### Робота з базою даних

```bash
# Створити базу даних
php bin/console doctrine:database:create

# Видалити базу даних
php bin/console doctrine:database:drop --force

# Створити міграцію
php bin/console doctrine:migrations:diff

# Виконати міграції
php bin/console doctrine:migrations:migrate

# Відкотити останню міграцію
php bin/console doctrine:migrations:migrate prev
```

### Очистка кешу

```bash
php bin/console cache:clear
```

### Дебаг

```bash
# Список всіх маршрутів
php bin/console debug:router

# Інформація про конкретний маршрут
php bin/console debug:router app_home
```

## Troubleshooting (Вирішення проблем)

### Проблема 1: "Database does not exist"

**Рішення:**
```bash
php bin/console doctrine:database:create
```

### Проблема 2: "Unable to connect to PostgreSQL"

**Перевірте:**
1. Чи запущений PostgreSQL сервер
2. Чи правильні дані у `.env` (логін, пароль, порт)
3. Чи існує користувач postgres з паролем 1234

**Створити користувача:**
```sql
-- Підключитися як суперкористувач
psql -U postgres

-- Створити нового користувача
CREATE USER postgres WITH PASSWORD '1234';
ALTER USER postgres WITH SUPERUSER;
```

### Проблема 3: Порт 8000 зайнятий

**Перевірити:**
```bash
netstat -ano | findstr :8000
```

**Використати інший порт:**
```bash
php -S localhost:8080 -t public
```

### Проблема 4: Помилка міграції

**Рішення:**
```bash
# Видалити базу даних
php bin/console doctrine:database:drop --force

# Створити заново
php bin/console doctrine:database:create

# Виконати міграції
php bin/console doctrine:migrations:migrate --no-interaction
```

## Технології

- **Backend:** Symfony 7.3 (PHP 8.2)
- **База даних:** PostgreSQL 17
- **ORM:** Doctrine ORM
- **Frontend:** Bootstrap 5.3, Twig templates
- **JavaScript:** Vanilla JS (генератор випадкових чисел)

## Структура проєкту

```
symfony-negative-number-counter/
├── negative-counter/           # Symfony застосунок
│   ├── config/                 # Конфігураційні файли
│   ├── migrations/             # Міграції бази даних
│   ├── public/                 # Публічна директорія
│   ├── src/                    # Вихідний код
│   │   ├── Controller/         # Контролери
│   │   ├── Entity/             # Моделі даних (ORM)
│   │   ├── Form/               # Форми
│   │   └── Repository/         # Репозиторії для роботи з БД
│   ├── templates/              # Twig шаблони
│   ├── .env                    # Змінні середовища
│   └── composer.json           # Залежності PHP
├── comands for bd.txt          # Команди для роботи з PostgreSQL
└── README.md                   # Ця документація
```

## Корисні посилання

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
- [Bootstrap 5 Docs](https://getbootstrap.com/docs/5.3/)
- [PostgreSQL Manual](https://www.postgresql.org/docs/)

## Ліцензія

Proprietary - навчальний проєкт
