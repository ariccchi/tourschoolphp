<?php
require_once "DatabaseModel.php";
require_once "vendor/autoload.php"; // Подключите библиотеку JWT
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
date_default_timezone_set('Asia/Almaty'); 

use \Firebase\JWT\JWT; // Используйте Firebase JWT

class UserLogin {
public function execute($username, $password) {
try {
$db = new DatabaseModel();
            
            // Подготовленное выражение для защиты от SQL-инъекций
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $username);
            
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user) {
                    // Проверка пароля
                    if (password_verify($password, $user['password'])) {
                        $logMessage = date('Y-m-d H:i:s') . " - Пользователь $username вошел в систему.\n";
                        file_put_contents('log.txt', $logMessage, FILE_APPEND);
                        
        
                        $key = "1b815f4129c84de40c2ae2a24f876262454a5f0cfda9bb10a0948bd37d6567c8"; // Замените на свой секретный ключ
                        $payload = array(
                            "sub" => $user['id'], // ID пользователя
                            "name" => $user['name'], // имя пользователя
                            "role" => $user['role'],
                            "iat" => time(), // текущее время в формате Unix timestamp
                            "exp" => time() + 60
                        );
                        $jwt = JWT::encode($payload, $key, 'HS256');
                        
                        // Генерация рефреш-токена
                        $refreshKey = "d270e5c9df4c52e258ae7b9550f36b356ab29cda98df542b1896458e697d8b6b"; // Замените на свой секретный ключ для рефреш-токена
                        $refreshPayload = array(
                            "role" => $user['role'],
                            "sub" => $user['id'], // ID пользователя
                            "iat" => time(), // текущее время в формате Unix timestamp
                            "exp" => time() + (60) // Установите время истечения рефреш-токена, например, на 7 дней вперед
                        );
                        $refreshToken = JWT::encode($refreshPayload, $refreshKey, 'HS256');
                        
                        // Добавление токена в базу данных
                        $stmt = $db->prepare("UPDATE users SET access_token = ?, refresh_token = ? WHERE id = ?");
                        $stmt->bind_param("ssi", $jwt, $refreshToken, $user['id']);
                        $stmt->execute();
                        
                        // Установка рефреш-токена в куки
                        setcookie("refreshToken", $refreshToken, time() + (60), "/"); // Установите куки на 7 дней
                    
                        echo json_encode(array('status' => 'success', 'message' => 'User logged in successfully', 'token' => $jwt));
                        
                    } else {
                        // Пароль неверен
                        echo json_encode(array('status' => 'error', 'message' => 'Invalid password'));
                    }
                    
                } else {
                    // Пользователь не найден
                    echo json_encode(array('status' => 'error', 'message' => 'User not found'));
                }
            } else {
                // Ошибка выполнения запроса
                echo json_encode(array('status' => 'error', 'message' => 'Failed to execute query'));
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
        }
    }
}

// Получите данные POST
$postdata = file_get_contents("php://input");
$request = json_decode($postdata);

// Создайте экземпляр класса UserLogin и вызовите метод execute
$login = new UserLogin();
$login->execute($request->username, $request->password);

?>
