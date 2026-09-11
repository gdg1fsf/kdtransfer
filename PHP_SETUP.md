# 🐘 Использование PHP для отправки заявок

Если у вас есть PHP хостинг, вы можете использовать более безопасный вариант с серверной обработкой заявок.

## 📋 Преимущества PHP варианта

✅ **Безопасность:** Токен бота хранится на сервере, а не в браузере  
✅ **Логирование:** Автоматическое сохранение всех заявок в лог-файл  
✅ **CORS:** Правильная настройка кросс-доменных запросов  
✅ **Валидация:** Проверка обязательных полей на сервере  

## 🚀 Установка

### Шаг 1: Настройка PHP файла

1. Откройте файл `send-order.php`
2. Найдите строки:
   ```php
   define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN');
   define('TELEGRAM_CHAT_ID', 'YOUR_CHAT_ID');
   ```
3. Замените на ваши данные от Telegram бота
4. Сохраните файл

### Шаг 2: Загрузка на хостинг

Загрузите файл `send-order.php` на ваш PHP хостинг (через FTP, cPanel или другой способ).

Пример структуры:
```
public_html/
├── index.html
├── style.css
├── send-order.php  ← этот файл
└── images/
```

### Шаг 3: Изменение index.html

Откройте `index.html` и найдите эту часть кода (примерно строка 550):

```javascript
// Отправка в Telegram
const response = await fetch(`https://api.telegram.org/bot${TELEGRAM_BOT_TOKEN}/sendMessage`, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    chat_id: TELEGRAM_CHAT_ID,
    text: message,
    parse_mode: 'Markdown'
  })
});

const result = await response.json();

if (result.ok) {
  // Успешная отправка
  // ...
}
```

**Замените весь этот блок на:**

```javascript
// Отправка через PHP
const response = await fetch('/send-order.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    name: data.name,
    phone: data.phone,
    route: data.route,
    passengers: data.passengers,
    date: data.date,
    time: data.time,
    childSeats: data.childSeats,
    contact: data.contact,
    contactUsername: data.contactUsername,
    comment: data.comment
  })
});

const result = await response.json();

if (result.success) {
  // Успешная отправка
  formMessage.textContent = '✅ Заявка успешно отправлена! Мы свяжемся с вами в ближайшее время.';
  formMessage.className = 'form-message success';
  bookingForm.reset();
  
  // Прокрутка к сообщению
  formMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
} else {
  throw new Error(result.error || 'Ошибка отправки');
}
```

### Шаг 4: Удаление токенов из HTML

Теперь можно **удалить** эти строки из `index.html`:

```javascript
// УДАЛИТЕ ЭТИ СТРОКИ:
const TELEGRAM_BOT_TOKEN = 'YOUR_BOT_TOKEN';
const TELEGRAM_CHAT_ID = 'YOUR_CHAT_ID';
```

## 🧪 Тестирование

1. Откройте ваш сайт в браузере
2. Заполните форму заказа
3. Отправьте заявку
4. Проверьте:
   - ✅ Заявка пришла в Telegram
   - ✅ В корне сайта появился файл `orders.log`

## 📊 Лог-файл заявок

PHP скрипт автоматически создаёт файл `orders.log` со всеми заявками:

```
2026-08-10 15:30:45 | Иванов Иван Иванович | +7 (900) 123-45-67 | Калининград → Гданьск
2026-08-10 16:15:20 | Петрова Мария | +7 (911) 555-55-55 | Калининград → Варшава
```

### Просмотр логов

#### Через браузер (небезопасно):
```
https://ваш-сайт.ru/orders.log
```

⚠️ **Для защиты логов добавьте в .htaccess:**
```apache
<Files "orders.log">
  Order Allow,Deny
  Deny from all
</Files>
```

#### Через FTP/SSH:
Скачайте файл `orders.log` с сервера

## 🔒 Дополнительная безопасность

### 1. Ограничение частоты запросов

Добавьте в начало `send-order.php`:

```php
session_start();

// Проверка: не более 3 заявок в 10 минут
if (!isset($_SESSION['last_order_time'])) {
    $_SESSION['last_order_time'] = [];
}

$now = time();
$_SESSION['last_order_time'] = array_filter(
    $_SESSION['last_order_time'],
    function($time) use ($now) { return $now - $time < 600; }
);

if (count($_SESSION['last_order_time']) >= 3) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Слишком много заявок. Попробуйте позже.']);
    exit();
}

$_SESSION['last_order_time'][] = $now;
```

### 2. Защита от спама (капча)

Добавьте Google reCAPTCHA v3:

1. Получите ключи на [google.com/recaptcha](https://www.google.com/recaptcha)
2. Добавьте в `<head>` в index.html:
   ```html
   <script src="https://www.google.com/recaptcha/api.js?render=ВАШ_SITE_KEY"></script>
   ```
3. Перед отправкой формы получите токен:
   ```javascript
   const token = await grecaptcha.execute('ВАШ_SITE_KEY', {action: 'submit'});
   // Добавьте token в данные запроса
   ```
4. Проверьте токен в `send-order.php`

### 3. Ограничение доступа по IP

```php
// Разрешить только с вашего домена
$allowedOrigins = ['https://ваш-сайт.ru', 'https://www.ваш-сайт.ru'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (!in_array($origin, $allowedOrigins)) {
    http_response_code(403);
    exit('Forbidden');
}
```

## 🐛 Решение проблем

### Ошибка 500
- Проверьте права на запись (chmod 644 для send-order.php)
- Проверьте логи PHP на хостинге
- Убедитесь, что cURL установлен (`phpinfo()`)

### Заявки не приходят
- Проверьте правильность токена и Chat ID в `send-order.php`
- Убедитесь, что PHP скрипт доступен по URL
- Проверьте файл `orders.log` - создаётся ли он?

### CORS ошибка
- Убедитесь, что в `send-order.php` есть заголовки CORS
- Для продакшена замените `*` на ваш домен:
  ```php
  header('Access-Control-Allow-Origin: https://ваш-сайт.ru');
  ```

### Файл orders.log не создаётся
- Проверьте права на запись в папке (chmod 755)
- Создайте файл вручную: `touch orders.log && chmod 666 orders.log`

## 📱 Альтернативы PHP

Если у вас нет PHP хостинга, рассмотрите альтернативы:

| Вариант | Сложность | Стоимость | Рекомендация |
|---------|-----------|-----------|--------------|
| **PHP** | ⭐⭐ | Бесплатно | ✅ Рекомендуется |
| Node.js + Express | ⭐⭐⭐ | $5-10/мес | Хорошо для опытных |
| Vercel Functions | ⭐⭐ | Бесплатно | ✅ Отличный выбор |
| Netlify Functions | ⭐⭐ | Бесплатно | ✅ Простая настройка |
| Google Apps Script | ⭐⭐⭐ | Бесплатно | Для энтузиастов |

## 📞 Поддержка

Если возникли проблемы:
- Telegram: [@kdtransfer](https://t.me/kdtransfer)
- Email: support@kdtransfer.com

---

✅ **Готово!** Теперь ваш сайт безопасно обрабатывает заявки через PHP.
