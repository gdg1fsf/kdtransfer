// Конфигурация Telegram бота
const TELEGRAM_CONFIG = {
    botToken: '8820246704:AAFUkqASpkt9zsdt1w5PlLqAhRBaLIaI2IA', // Замените на токен вашего бота
    chatId: '6045154952,470493325'      // Замените на ваш chat ID
};

// Создание звезд
function createStars() {
    const starsContainer = document.querySelector('.stars');
    const starCount = 100;
    
    for (let i = 0; i < starCount; i++) {
        const star = document.createElement('div');
        star.className = 'star';
        star.style.left = Math.random() * 100 + '%';
        star.style.top = Math.random() * 100 + '%';
        star.style.animationDelay = Math.random() * 3 + 's';
        starsContainer.appendChild(star);
    }
}

createStars();

// Плавная прокрутка для навигации
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Обработка формы заказа
document.getElementById('orderForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = {
        name: document.getElementById('name').value,
        phone: document.getElementById('phone').value,
        from: document.getElementById('from').value,
        to: document.getElementById('to').value,
        date: document.getElementById('date').value,
        time: document.getElementById('time').value,
        passengers: document.getElementById('passengers').value,
        comment: document.getElementById('comment').value || 'Не указано'
    };
    
    // Формирование сообщения для Telegram
    const message = `
🚗 НОВЫЙ ЗАКАЗ ТРАНСФЕРА

👤 Имя: ${formData.name}
📞 Телефон: ${formData.phone}

📍 Откуда: ${formData.from}
📍 Куда: ${formData.to}

📅 Дата: ${formData.date}
🕐 Время: ${formData.time}
👥 Пассажиров: ${formData.passengers}

💬 Комментарий: ${formData.comment}
    `.trim();
    
    try {
        // Отправка сообщения в Telegram
        const response = await sendToTelegram(message);
        
        if (response.ok) {
            // Показываем сообщение об успехе
            document.getElementById('orderForm').style.display = 'none';
            document.getElementById('successMessage').style.display = 'block';
            
            // Сбрасываем форму
            document.getElementById('orderForm').reset();
            
            // Через 5 секунд возвращаем форму
            setTimeout(() => {
                document.getElementById('orderForm').style.display = 'block';
                document.getElementById('successMessage').style.display = 'none';
            }, 5000);
        } else {
            throw new Error('Ошибка отправки');
        }
    } catch (error) {
        console.error('Ошибка:', error);
        alert('Произошла ошибка при отправке заявки. Пожалуйста, попробуйте позже или свяжитесь с нами по телефону.');
    }
});

// Функция отправки сообщения в Telegram
async function sendToTelegram(message) {
    const url = `https://api.telegram.org/bot${TELEGRAM_CONFIG.botToken}/sendMessage`;
    
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            chat_id: TELEGRAM_CONFIG.chatId,
            text: message,
            parse_mode: 'HTML'
        })
    });
    
    return response;
}

// Установка минимальной даты для поля даты (сегодня)
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('date');
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);
});

// Анимация появления элементов при скролле
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Применяем анимацию к карточкам
document.querySelectorAll('.service-card, .feature, .fleet-info').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});
