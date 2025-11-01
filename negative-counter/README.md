# Negative Number Counter - README для розробників

Веб-застосунок на Symfony 7.3 + PostgreSQL 17 для підрахунку кількості від'ємних елементів у числових масивах.

---

## 📋 Зміст

1. [Вимоги до системи](#вимоги-до-системи)
2. [Встановлення та налаштування](#встановлення-та-налаштування)
3. [Структура проєкту](#структура-проєкту)
4. [Покрокова розробка](#покрокова-розробка)
5. [Технічна документація](#технічна-документація)
6. [Команди для роботи](#команди-для-роботи)
7. [Troubleshooting](#troubleshooting)

---

## 🔧 Вимоги до системи

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

---

## 🚀 Встановлення та налаштування

### Крок 1: Створення проєкту Symfony

```bash
# Перейти в робочу директорію
cd "C:\Users\Сергей\Documents\2025\Аспірантура ЗНТУ 124\Symfony"

# Створити новий проєкт Symfony (skeleton)
composer create-project symfony/skeleton negative-counter

# Перейти в директорію проєкту
cd negative-counter
```

**Що відбулося:**
- Створено базову структуру Symfony
- Встановлено Symfony Flex для автоматичного налаштування
- Встановлено мінімальний набір залежностей

### Крок 2: Встановлення webapp pack

```bash
# Встановити повний набір компонентів для веб-застосунку
composer require webapp
```

**Встановлені пакети:**
- `symfony/form` - робота з формами
- `symfony/validator` - валідація даних
- `symfony/twig-bundle` - шаблонізатор
- `symfony/asset-mapper` - управління фронтенд-ресурсами
- `symfony/security-bundle` - безпека
- `doctrine/doctrine-bundle` - ORM для роботи з БД
- `doctrine/doctrine-migrations-bundle` - міграції БД
- `symfony/maker-bundle` - генератор коду

### Крок 3: Налаштування підключення до PostgreSQL

**Файл:** `.env`

```env
# Знайти рядок DATABASE_URL та змінити на:
DATABASE_URL="postgresql://postgres:1234@127.0.0.1:5432/negative_counter?serverVersion=17&charset=utf8"
```

**Параметри підключення:**
- **Користувач:** postgres
- **Пароль:** 1234
- **Хост:** 127.0.0.1
- **Порт:** 5432
- **База даних:** negative_counter
- **Версія:** PostgreSQL 17

### Крок 4: Створення бази даних

```bash
# Створити базу даних через Symfony
php bin/console doctrine:database:create --if-not-exists
```

**Результат:**
```
Created database "negative_counter" for connection named default
```

---

## 📂 Структура проєкту

```
negative-counter/
│
├── config/                          # Конфігураційні файли
│   ├── packages/
│   │   └── doctrine.yaml           # Налаштування Doctrine ORM
│   └── routes.yaml                 # Маршрути (автоматичні через атрибути)
│
├── migrations/                      # Міграції бази даних
│   └── Version20251024100217.php   # Створення таблиці number_array
│
├── public/                          # Публічна директорія (веб-сервер)
│   └── index.php                   # Точка входу
│
├── src/                            # Вихідний код застосунку
│   ├── Controller/
│   │   └── NegativeCounterController.php
│   │
│   ├── Entity/
│   │   └── NumberArray.php
│   │
│   ├── Form/
│   │   └── NumberArrayType.php
│   │
│   └── Repository/
│       └── NumberArrayRepository.php
│
├── templates/                       # Twig шаблони
│   ├── base.html.twig              # Базовий шаблон
│   └── negative_counter/
│       └── index.html.twig         # Головна сторінка
│
├── var/                            # Тимчасові файли, кеш, логи
├── vendor/                         # Залежності Composer
├── .env                            # Змінні середовища
├── composer.json                   # Залежності проєкту
└── composer.lock                   # Зафіксовані версії залежностей
```

---

## 🔨 Покрокова розробка

### Етап 1: Створення Entity (Модель даних)

**Файл:** `src/Entity/NumberArray.php`

**Створення:**
```bash
# Можна створити через MakerBundle:
php bin/console make:entity NumberArray

# Або створити вручну (як зроблено в проєкті)
```

**Що міститься:**
```php
<?php

namespace App\Entity;

use App\Repository\NumberArrayRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NumberArrayRepository::class)]
class NumberArray
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $numbers = null;

    #[ORM\Column]
    private ?int $negativeCount = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    // ... конструктор та методи
}
```

**Опис полів:**
- `id` - первинний ключ (автоінкремент)
- `numbers` - текстове поле для збереження чисел через кому
- `negativeCount` - кількість від'ємних елементів
- `createdAt` - дата та час створення запису

**Важливі методи:**
1. `getNumbersArray()` - перетворює рядок у масив чисел
2. `setNumbersArray()` - перетворює масив у рядок
3. `calculateNegativeCount()` - підраховує від'ємні числа

### Етап 2: Створення Repository

**Файл:** `src/Repository/NumberArrayRepository.php`

```php
<?php

namespace App\Repository;

use App\Entity\NumberArray;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class NumberArrayRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NumberArray::class);
    }

    public function save(NumberArray $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
```

**Призначення:**
- Робота з базою даних через Doctrine ORM
- Методи для збереження, видалення, пошуку записів

### Етап 3: Створення форми

**Файл:** `src/Form/NumberArrayType.php`

```php
<?php

namespace App\Form;

use App\Entity\NumberArray;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class NumberArrayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numbers', TextareaType::class, [
                'label' => 'Введіть числа (через кому)',
                'attr' => [
                    'rows' => 5,
                    'placeholder' => 'Наприклад: -5, 10, -3, 7, -1, 0, 15',
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Будь ласка, введіть числа'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^-?\d+(\.\d+)?(,\s*-?\d+(\.\d+)?)*$/',
                        'message' => 'Введіть числа через кому'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NumberArray::class,
        ]);
    }
}
```

**Особливості:**
- Поле типу `TextareaType` для введення багатьох чисел
- Валідація через `Assert\NotBlank` (обов'язкове поле)
- Валідація через `Assert\Regex` (формат: числа через кому)
- Регулярний вираз перевіряє цілі та дробові числа

### Етап 4: Створення контролера

**Файл:** `src/Controller/NegativeCounterController.php`

```php
<?php

namespace App\Controller;

use App\Entity\NumberArray;
use App\Form\NumberArrayType;
use App\Repository\NumberArrayRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NegativeCounterController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        NumberArrayRepository $repository
    ): Response {
        $numberArray = new NumberArray();
        $form = $this->createForm(NumberArrayType::class, $numberArray);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Обчислюємо кількість від'ємних елементів
            $numberArray->calculateNegativeCount();

            // Зберігаємо в базу даних
            $entityManager->persist($numberArray);
            $entityManager->flush();

            $this->addFlash('success', 'Результат збережено в базу даних!');

            return $this->redirectToRoute('app_home');
        }

        // Отримуємо всі збережені результати
        $results = $repository->findBy([], ['createdAt' => 'DESC'], 10);

        return $this->render('negative_counter/index.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
        ]);
    }
}
```

**Маршрут:** `#[Route('/', name: 'app_home')]`
- URL: http://localhost:8000/
- Назва маршруту: app_home

**Логіка роботи:**
1. Створюється новий об'єкт `NumberArray`
2. Створюється форма, прив'язана до об'єкта
3. Форма обробляє POST запит
4. Якщо форма валідна:
   - Викликається метод `calculateNegativeCount()`
   - Об'єкт зберігається в БД
   - Додається flash-повідомлення
   - Redirect на ту ж саму сторінку
5. Завантажується історія (останні 10 записів)
6. Рендериться Twig шаблон

### Етап 5: Створення шаблонів Twig

#### 5.1. Базовий шаблон

**Файл:** `templates/base.html.twig`

**Структура:**
```twig
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{% block title %}Калькулятор від'ємних чисел{% endblock %}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    {% block stylesheets %}
        <style>
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 20px 0;
            }
            /* ... інші стилі ... */
        </style>
    {% endblock %}
</head>
<body>
    <div class="container">
        {% block body %}{% endblock %}
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    {% block javascripts %}
        {% block importmap %}{{ importmap('app') }}{% endblock %}
    {% endblock %}
</body>
</html>
```

**Використані CDN:**
- Bootstrap 5.3.0 CSS
- Bootstrap Icons 1.11.0
- Bootstrap 5.3.0 JS (включає Popper.js)

#### 5.2. Головна сторінка

**Файл:** `templates/negative_counter/index.html.twig`

**Компоненти:**
1. **Заголовок сторінки** - градієнтний header з іконкою
2. **Flash повідомлення** - успішне збереження
3. **Форма введення** (ліва колонка):
   - Текстове поле для чисел
   - Підказка з прикладом
   - Кнопка "Обчислити"
4. **Генератор випадкових чисел** (права колонка):
   - Поле "Кількість чисел" (1-100)
   - Поле "Мінімум"
   - Поле "Максимум"
   - Кнопка "Згенерувати числа"
   - Кнопка "Очистити"
5. **Інформаційна панель** (права колонка):
   - Інструкція з використання
   - Приклад введення
6. **Історія обчислень** (таблиця):
   - Дата та час
   - Введені числа
   - Кількість від'ємних (badge)

**JavaScript функціонал:**
```javascript
function generateNumbers() {
    const count = parseInt(document.getElementById('count').value);
    const min = parseInt(document.getElementById('min').value);
    const max = parseInt(document.getElementById('max').value);

    // Валідація
    if (count < 1 || count > 100) {
        alert('Кількість чисел має бути від 1 до 100');
        return;
    }

    if (min >= max) {
        alert('Мінімальне значення має бути менше максимального');
        return;
    }

    // Генерація
    const numbers = [];
    for (let i = 0; i < count; i++) {
        const randomNumber = Math.floor(Math.random() * (max - min + 1)) + min;
        numbers.push(randomNumber);
    }

    // Заповнення textarea
    const textarea = document.getElementById('number_array_numbers');
    if (textarea) {
        textarea.value = numbers.join(', ');

        // Візуальний ефект
        textarea.classList.add('border-success');
        setTimeout(() => {
            textarea.classList.remove('border-success');
        }, 1000);
    }
}

function clearNumbers() {
    const textarea = document.getElementById('number_array_numbers');
    if (textarea) {
        textarea.value = '';
        textarea.focus();
    }
}
```

### Етап 6: Створення міграції

**Створення:**
```bash
# Згенерувати міграцію на основі Entity
php bin/console doctrine:migrations:diff
```

**Результат:** `migrations/Version20251024100217.php`

**Проблема:** Міграція спробувала видалити sequences з pgagent

**Рішення:** Вручну відредаговано файл міграції:
- Видалено рядки `DROP SEQUENCE pgagent.*`
- Залишено тільки `CREATE TABLE` команди

**Виконання:**
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

**SQL команди в міграції:**
```sql
-- Створення таблиці number_array
CREATE TABLE number_array (
    id SERIAL NOT NULL,
    numbers TEXT NOT NULL,
    negative_count INT NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    PRIMARY KEY(id)
);

-- Створення таблиці messenger_messages (для Symfony Messenger)
CREATE TABLE messenger_messages (
    id BIGSERIAL NOT NULL,
    body TEXT NOT NULL,
    headers TEXT NOT NULL,
    queue_name VARCHAR(190) NOT NULL,
    created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
    delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
    PRIMARY KEY(id)
);
```

---

## 📚 Технічна документація

### Doctrine ORM Mapping

**Анотації/Атрибути:**
```php
#[ORM\Entity(repositoryClass: NumberArrayRepository::class)]
```
- Позначає клас як Entity
- Вказує клас Repository

```php
#[ORM\Id]
#[ORM\GeneratedValue]
#[ORM\Column]
```
- `Id` - первинний ключ
- `GeneratedValue` - автоінкремент
- `Column` - стовпець таблиці

```php
#[ORM\Column(type: Types::TEXT)]
```
- Тип даних TEXT для великих рядків

```php
#[ORM\Column(type: Types::DATETIME_MUTABLE)]
```
- Тип даних DATETIME для дати/часу

### Symfony Form Component

**Типи полів:**
- `TextareaType::class` - багаторядкове текстове поле

**Constraints (Валідатори):**
```php
new Assert\NotBlank([
    'message' => 'Будь ласка, введіть числа'
])
```
- Перевірка на порожнє поле

```php
new Assert\Regex([
    'pattern' => '/^-?\d+(\.\d+)?(,\s*-?\d+(\.\d+)?)*$/',
    'message' => 'Введіть числа через кому'
])
```
- Перевірка формату через регулярний вираз

**Регулярний вираз пояснення:**
- `^` - початок рядка
- `-?` - опціональний мінус
- `\d+` - одна або більше цифр
- `(\.\d+)?` - опціональна десяткова частина
- `,\s*` - кома з опціональними пробілами
- `*` - повторення попередньої групи
- `$` - кінець рядка

### Twig Templating

**Успадкування:**
```twig
{% extends 'base.html.twig' %}
```

**Блоки:**
```twig
{% block title %}...{% endblock %}
{% block body %}...{% endblock %}
{% block javascripts %}
    {{ parent() }}
    ...
{% endblock %}
```

**Форми:**
```twig
{{ form_start(form) }}
{{ form_label(form.numbers) }}
{{ form_widget(form.numbers) }}
{{ form_errors(form.numbers) }}
{{ form_end(form) }}
```

**Цикли:**
```twig
{% for result in results %}
    {{ result.numbers }}
    {{ result.negativeCount }}
    {{ result.createdAt|date('d.m.Y H:i') }}
{% endfor %}
```

**Фільтри:**
```twig
{{ result.createdAt|date('d.m.Y H:i') }}
```
- Форматування дати

**Умови:**
```twig
{% if result.negativeCount > 0 %}danger{% else %}secondary{% endif %}
```

### Bootstrap 5 Grid System

**Container:**
```html
<div class="container">
```

**Rows and Columns:**
```html
<div class="row">
    <div class="col-lg-6">...</div>
    <div class="col-lg-6">...</div>
</div>
```

**Responsive breakpoints:**
- `col-lg-6` - на великих екранах (≥992px) - 50% ширини
- На менших екранах - 100% ширини (стовпці стають рядками)

**Утиліти:**
- `mb-3` - margin-bottom 1rem
- `d-grid` - display: grid
- `gap-2` - gap між елементами
- `text-center` - текст по центру
- `table-responsive` - адаптивна таблиця

---

## 💻 Команди для роботи

### Запуск сервера
```bash
cd negative-counter
php -S localhost:8000 -t public
```

### Робота з базою даних

**Створити БД:**
```bash
php bin/console doctrine:database:create
```

**Видалити БД:**
```bash
php bin/console doctrine:database:drop --force
```

**Створити міграцію:**
```bash
php bin/console doctrine:migrations:diff
```

**Виконати міграції:**
```bash
php bin/console doctrine:migrations:migrate
```

**Відкотити міграцію:**
```bash
php bin/console doctrine:migrations:migrate prev
```

**Виконати SQL запит:**
```bash
php bin/console doctrine:query:sql "SELECT * FROM number_array"
```

### Генерація коду (MakerBundle)

**Створити Entity:**
```bash
php bin/console make:entity
```

**Створити Controller:**
```bash
php bin/console make:controller
```

**Створити Form:**
```bash
php bin/console make:form
```

### Очистка кешу
```bash
php bin/console cache:clear
```

### Дебаг

**Список маршрутів:**
```bash
php bin/console debug:router
```

**Інформація про маршрут:**
```bash
php bin/console debug:router app_home
```

**Список сервісів:**
```bash
php bin/console debug:container
```

---

## 🐛 Troubleshooting

### Проблема 1: Помилка "Database does not exist"

**Рішення:**
```bash
php bin/console doctrine:database:create
```

### Проблема 2: Помилка міграції з pgagent

**Симптом:**
```
Migration failed: DROP SEQUENCE pgagent.pga_jobclass_jclid_seq
```

**Рішення:**
1. Створити окрему базу даних
2. Відредагувати файл міграції, видаливши `DROP SEQUENCE` команди
3. Виконати міграцію знову

### Проблема 3: Порт 8000 зайнятий

**Перевірка:**
```bash
netstat -ano | findstr :8000
```

**Завершення процесу (Windows):**
```bash
taskkill //F //PID <PID>
```

### Проблема 4: Помилка "Unable to connect to PostgreSQL"

**Рішення:**
1. Перевірити, чи запущений PostgreSQL
2. Перевірити DATABASE_URL в `.env`
3. Перевірити пароль та ім'я користувача

### Проблема 5: Форма не валідується

**Перевірка:**
1. Відкрити інструменти розробника (F12)
2. Перевірити Console на помилки JavaScript
3. Перевірити Network вкладку на помилки HTTP

**Дебаг валідації:**
```php
if ($form->isSubmitted() && !$form->isValid()) {
    dump($form->getErrors(true));
    die();
}
```

### Проблема 6: Стилі не застосовуються

**Перевірка:**
1. Очистити кеш браузера (Ctrl+F5)
2. Перевірити, чи завантажується Bootstrap CDN
3. Перевірити Console на помилки 404

---

## 📊 Структура бази даних

### Таблиця: number_array

| Колонка | Тип | Опис |
|---------|-----|------|
| id | SERIAL | Первинний ключ (автоінкремент) |
| numbers | TEXT | Числа через кому |
| negative_count | INTEGER | Кількість від'ємних чисел |
| created_at | TIMESTAMP | Дата та час створення |

**Приклад даних:**
```sql
INSERT INTO number_array (numbers, negative_count, created_at)
VALUES ('34, 39, 28, -23, -27, -32', 3, '2025-10-24 12:20:26');
```

**SQL запити:**

Вибрати всі:
```sql
SELECT * FROM number_array ORDER BY created_at DESC;
```

Вибрати останні 10:
```sql
SELECT * FROM number_array ORDER BY created_at DESC LIMIT 10;
```

Підрахувати загальну кількість:
```sql
SELECT COUNT(*) FROM number_array;
```

Знайти записи з найбільшою кількістю від'ємних:
```sql
SELECT * FROM number_array
ORDER BY negative_count DESC
LIMIT 5;
```

Очистити таблицю:
```sql
TRUNCATE TABLE number_array RESTART IDENTITY;
```

---

## 🎨 Кастомізація дизайну

### Зміна кольорової схеми

**Файл:** `templates/base.html.twig`

**Градієнт фону:**
```css
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

**Змінити на інший градієнт:**
```css
/* Синьо-зелений */
background: linear-gradient(135deg, #667eea 0%, #00d2ff 100%);

/* Червоно-помаранчевий */
background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);

/* Темний */
background: linear-gradient(135deg, #232526 0%, #414345 100%);
```

### Зміна розмірів форми

**Файл:** `src/Form/NumberArrayType.php`

```php
'attr' => [
    'rows' => 10,  // Збільшити висоту textarea
    'class' => 'form-control form-control-lg'  // Великий розмір
]
```

### Додавання власних стилів

**Створити файл:** `public/css/custom.css`

```css
.custom-card {
    border-radius: 30px;
    box-shadow: 0 30px 80px rgba(0,0,0,0.4);
}
```

**Підключити в base.html.twig:**
```html
<link rel="stylesheet" href="/css/custom.css">
```

---

## 🔐 Безпека

### Валідація введення

**На стороні клієнта (HTML5):**
```html
<input type="number" min="1" max="100" required>
```

**На стороні сервера (Symfony Validator):**
```php
new Assert\Range([
    'min' => 1,
    'max' => 100,
    'notInRangeMessage' => 'Значення має бути від {{ min }} до {{ max }}'
])
```

### CSRF захист

Symfony автоматично додає CSRF токен до форм:
```twig
{{ form_start(form) }}
    {# CSRF токен додається автоматично #}
{{ form_end(form) }}
```

### SQL Injection

Doctrine ORM автоматично екранує параметри:
```php
// Безпечно (використовуються параметри)
$repository->findBy(['id' => $id]);

// Небезпечно (не робіть так!)
$em->createQuery("SELECT n FROM App\Entity\NumberArray n WHERE n.id = $id");
```

---

## 📈 Подальший розвиток

### Можливі покращення:

1. **Експорт даних**
   - CSV файли
   - Excel таблиці
   - PDF звіти

2. **Візуалізація**
   - Графіки Chart.js
   - Статистика
   - Аналітика

3. **Пагінація**
   ```bash
   composer require knplabs/knp-paginator-bundle
   ```

4. **API**
   ```bash
   composer require api-platform
   ```

5. **Тести**
   ```bash
   composer require --dev symfony/test-pack
   php bin/phpunit
   ```

6. **Docker**
   ```dockerfile
   FROM php:8.2-apache
   RUN docker-php-ext-install pdo pdo_pgsql
   ```

---

## 📞 Контакти та допомога

**Автор:** Аспірант ЗНТУ, Спеціальність 124
**Дата:** 24 жовтня 2025

**Корисні посилання:**
- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
- [Bootstrap 5 Docs](https://getbootstrap.com/docs/5.3/)
- [Twig Documentation](https://twig.symfony.com/)
- [PostgreSQL Manual](https://www.postgresql.org/docs/)

---

**Версія документації:** 1.0
**Останнє оновлення:** 24.10.2025
