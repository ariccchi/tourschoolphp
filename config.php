
<?php
define('DB_HOST', 'localhost');      // Хост базы данных
define('DB_USER', 'admin');   // Имя пользователя базы данных
define('DB_PASSWORD', 'root');   // Пароль пользователя базы данных
define('DB_NAME', 'tourschool'); // Имя базы данных


// Кодировка для базы данных
define('DB_CHARSET', 'utf8mb4');


// Настройки для путей в приложении
define('APP_ROOT', dirname(__FILE__)); // Корневая директория вашего приложения


// URL вашего сайта
define('APP_URL', 'http://localhost:8888/tourschoolphp/'); // Укажите URL вашего сайта


// Название вашего сайта
define('SITE_NAME', 'Название вашего сайта');


// Режим отладки
define('DEBUG_MODE', true); // true - включен, false - выключен


// Секретный ключ для безопасности
define('SECRET_KEY', 'ваш_секретный_ключ');


// Настройки сессии
define('SESSION_NAME', 'my_session'); // Имя сессии
define('SESSION_TIMEOUT', 3600);      // Время жизни сессии в секундах (1 час)
002


// Другие настройки, если необходимо
// ...


?>
