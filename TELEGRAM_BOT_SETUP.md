# 🤖 Настройка Telegram бота для приёма заявок

## Шаг 1: Создание бота

1. Откройте Telegram и найдите [@BotFather](https://t.me/BotFather)
2. Отправьте команду `/newbot`
3. Введите имя вашего бота (например: "KD Transfer Orders")
4. Введите username бота (например: "kdtransfer_orders_bot")
5. BotFather отправит вам токен вида: `1234567890:ABCdefGHIjklMNOpqrsTUVwxyz`

**⚠️ ВАЖНО:** Сохраните этот токен в надёжном месте!

## Шаг 2: Получение Chat ID

### Метод 1 (простой):
1. Найдите бота [@userinfobot](https://t.me/userinfobot)
2. Нажмите "Start"
3. Бот отправит вам ваш Chat ID (например: `123456789`)

### Метод 2 (через API):
1. Отправьте любое сообщение вашему новому боту
2. Откройте в браузере:
   ```
   https://api.telegram.org/bot<ВАШ_ТОКЕН>/getUpdates
   ```
3. Найдите значение `"chat":{"id":123456789}`

## Шаг 3: Настройка сайта

Откройте файл `index.html` и найдите эти строки (примерно на строке 537):

```javascript
// ВАЖНО: Замените на данные вашего Telegram бота!
const TELEGRAM_BOT_TOKEN = 'YOUR_BOT_TOKEN'; // Получите у @BotFather
const TELEGRAM_CHAT_ID = 'YOUR_CHAT_ID'; // Ваш ID в Telegram
```

Замените значения:

```javascript
const TELEGRAM_BOT_TOKEN = '1234567890:ABCdefGHIjklMNOpqrsTUVwxyz';
const TELEGRAM_CHAT_ID = '123456789';
```

## Шаг 4: Проверка

1. Откройте сайт в браузере
2. Прокрутите до раздела "Форма заказа трансфера"
3. Заполните форму и нажмите "Отправить заявку"
4. Проверьте, что заявка пришла в Telegram

## 🔒 Безопасность

### ⚠️ ВАЖНО для продакшена:

Текущая реализация отправляет токен бота напрямую с клиента (браузера пользователя). Это подходит для тестирования, но **не безопасно** для продакшена!

### Рекомендации для продакшена:

#### Вариант 1: Backend сервер (рекомендуется)
Создайте простой сервер (Node.js, Python, PHP):

**Пример на Node.js:**
```javascript
// server.js
const express = require('express');
const axios = require('axios');
const app = express();

app.use(express.json());

app.post('/api/send-order', async (req, res) => {
  const BOT_TOKEN = process.env.TELEGRAM_BOT_TOKEN; // из .env файла
  const CHAT_ID = process.env.TELEGRAM_CHAT_ID;
  
  const message = req.body.message;
  
  try {
    await axios.post(`https://api.telegram.org/bot${BOT_TOKEN}/sendMessage`, {
      chat_id: CHAT_ID,
      text: message,
      parse_mode: 'Markdown'
    });
    
    res.json({ success: true });
  } catch (error) {
    res.status(500).json({ success: false });
  }
});

app.listen(3000);
```

Тогда в `index.html` измените:
```javascript
const response = await fetch('/api/send-order', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ message })
});
```

#### Вариант 2: Serverless функция (Vercel, Netlify)

Создайте файл `api/send-order.js`:
```javascript
export default async function handler(req, res) {
  const BOT_TOKEN = process.env.TELEGRAM_BOT_TOKEN;
  const CHAT_ID = process.env.TELEGRAM_CHAT_ID;
  
  // ... остальной код
}
```

#### Вариант 3: Google Apps Script (бесплатно)

1. Создайте новый проект на [script.google.com](https://script.google.com)
2. Вставьте код:
```javascript
function doPost(e) {
  var data = JSON.parse(e.postData.contents);
  var token = 'ВАШ_ТОКЕН';
  var chatId = 'ВАШ_CHAT_ID';
  
  var url = 'https://api.telegram.org/bot' + token + '/sendMessage';
  
  UrlFetchApp.fetch(url, {
    method: 'post',
    payload: {
      chat_id: chatId,
      text: data.message,
      parse_mode: 'Markdown'
    }
  });
  
  return ContentService.createTextOutput(JSON.stringify({success: true}))
    .setMimeType(ContentService.MimeType.JSON);
}
```
3. Разверните как веб-приложение
4. Используйте полученный URL в fetch запросе

## 📋 Формат заявки в Telegram

Каждая заявка придёт в таком формате:

```
🚗 НОВАЯ ЗАЯВКА НА ТРАНСФЕР

👤 ФИО: Иванов Иван Иванович
📞 Телефон: +7 (900) 123-45-67
🗺 Маршрут: Калининград → Гданьск
👥 Пассажиров: 3
📅 Дата: 2026-08-15
⏰ Время: 14:30
👶 Детских кресел: 2
💬 Способ связи: Telegram
📱 Username/Номер: @ivan_ivanov

📝 Комментарий:
Нужна подача по адресу ул. Ленина, 10. Багаж - 3 чемодана.
```

## 🆘 Возможные проблемы

### "Ошибка отправки"
- Проверьте правильность токена и Chat ID
- Убедитесь, что вы отправили хотя бы одно сообщение боту
- Проверьте консоль браузера (F12) на наличие ошибок

### "CORS error"
- Это норма для локального тестирования
- Загрузите сайт на хостинг (GitHub Pages, Netlify, Vercel)
- Или используйте расширение браузера для отключения CORS (только для тестов!)

### Заявки не приходят
- Проверьте, что бот не заблокирован
- Убедитесь, что Chat ID правильный
- Попробуйте отправить тестовое сообщение через API вручную:
  ```
  https://api.telegram.org/bot<ТОКЕН>/sendMessage?chat_id=<CHAT_ID>&text=Test
  ```

## 📞 Контакты для технической поддержки

Если возникли проблемы с настройкой, напишите в [@kdtransfer](https://t.me/kdtransfer)
