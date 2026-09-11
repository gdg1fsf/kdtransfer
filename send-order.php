<?php
/**
 * KD Transfer - Обработчик заявок
 * 
 * Этот файл получает данные формы и отправляет их в Telegram
 * Использование: загрузите на PHP хостинг и измените fetch URL в index.html
 */

// Настройки (ИЗМЕНИТЕ НА СВОИ!)
define('TELEGRAM_BOT_TOKEN', 'YOUR_BOT_TOKEN'); // Токен от @BotFather
define('TELEGRAM_CHAT_ID', 'YOUR_CHAT_ID');     // Ваш Chat ID

// CORS заголовки (разрешаем запросы с вашего домена)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// Обработка preflight запроса
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Получение данных из POST запроса
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Проверка обязательных полей
$requiredFields = ['name', 'phone', 'route', 'passengers', 'date', 'time', 'contact'];
foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Поле '$field' обязательно"]);
        exit();
    }
}

// Экранирование данных
function escape($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Формирование сообщения для Telegram
$message = "🚗 *НОВАЯ ЗАЯВКА НА ТРАНСФЕР*\n\n";
$message .= "👤 *ФИО:* " . escape($data['name']) . "\n";
$message .= "📞 *Телефон:* " . escape($data['phone']) . "\n";
$message .= "🗺 *Маршрут:* " . escape($data['route']) . "\n";
$message .= "👥 *Пассажиров:* " . escape($data['passengers']) . "\n";
$message .= "📅 *Дата:* " . escape($data['date']) . "\n";
$message .= "⏰ *Время:* " . escape($data['time']) . "\n";
$message .= "👶 *Детских кресел:* " . (isset($data['childSeats']) ? escape($data['childSeats']) : '0') . "\n";
$message .= "💬 *Способ связи:* " . escape($data['contact']) . "\n";

if (!empty($data['contactUsername'])) {
    $message .= "📱 *Username/Номер:* " . escape($data['contactUsername']) . "\n";
}

if (!empty($data['comment'])) {
    $message .= "\n📝 *Комментарий:*\n" . escape($data['comment']);
}

// Отправка в Telegram
$telegramApiUrl = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage";

$postData = [
    'chat_id' => TELEGRAM_CHAT_ID,
    'text' => $message,
    'parse_mode' => 'Markdown'
];

// Инициализация cURL
$ch = curl_init($telegramApiUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

// Выполнение запроса
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Проверка результата
if ($httpCode === 200) {
    $result = json_decode($response, true);
    if ($result['ok']) {
        echo json_encode([
            'success' => true,
            'message' => 'Заявка успешно отправлена'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Ошибка Telegram API: ' . $result['description']
        ]);
    }
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Ошибка отправки в Telegram'
    ]);
}

// Опционально: сохранение заявки в лог-файл
$logData = date('Y-m-d H:i:s') . " | " . $data['name'] . " | " . $data['phone'] . " | " . $data['route'] . "\n";
file_put_contents('orders.log', $logData, FILE_APPEND);
?>
