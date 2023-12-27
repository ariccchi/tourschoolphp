<?php
require_once "DatabaseModel.php";
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

$db = new DatabaseModel();
$json = file_get_contents('php://input');
$data = json_decode(file_get_contents('php://input'), true);

$username = isset($data['name']) ? $data['name'] : null;
$surname = isset($data['surname']) ? $data['surname'] : null;
$email = isset($data['email']) ? $data['email'] : null;
$password = isset($data['password']) ? $data['password'] : null;
$confirmPassword = isset($data['confirmPassword']) ? $data['confirmPassword'] : null;
$dob = isset($data['dob']) ? $data['dob'] : null;
$curatorUsername = isset($data['curatorUsername']) ? $data['curatorUsername'] : null;
$registration_date = isset($data['registration_date']) ? $data['registration_date'] : null;
$role = isset($data['role']) ? $data['role'] : null;
$city = isset($data['city']) ? $data['city'] : null;
$timestamp = isset($data['timestamp']) ? $data['timestamp'] : null;

// Проверка формата данных
if (
  !is_string($username) ||
  !is_string($email) ||
  !is_string($password) ||
  !is_string($confirmPassword) ||
  !is_string($dob) ||
  !is_string($curatorUsername) ||
  !is_string($role) ||
  !is_string($city) ||
  !is_string($surname)
) {
  echo json_encode(['error' => 'Неверный формат данных']);
  exit();
}

// Проверка совпадения паролей
if ($password !== $confirmPassword) {
  echo json_encode(['error' => 'Пароли не совпадают']);
  exit();
}
$sqlSelectCuratorId = "SELECT id FROM users WHERE email = ? AND role = 'curator'";
$stmtSelectCuratorId = $db->prepare($sqlSelectCuratorId);
$stmtSelectCuratorId->bind_param("s", $curatorUsername);
$stmtSelectCuratorId->execute();
$stmtSelectCuratorId->bind_result($curator_id);
$stmtSelectCuratorId->fetch();
$stmtSelectCuratorId->close();

// Если куратор не найден, прерываем выполнение
if (!$curator_id) {
    // Возвращаем ошибку или прерываем выполнение
    die('Ошибка регистрации: указанный email не принадлежит куратору.');
}
$sqlSelectCuratorCity = "SELECT city FROM users WHERE id = ?";
$stmtSelectCuratorCity = $db->prepare($sqlSelectCuratorCity);
$stmtSelectCuratorCity->bind_param("i", $curator_id);
$stmtSelectCuratorCity->execute();
$stmtSelectCuratorCity->bind_result($curator_city);
$stmtSelectCuratorCity->fetch();
$stmtSelectCuratorCity->close(); 
function hash_bcrypt($password, $cost = 10) {
    $salt = strtr(base64_encode(random_bytes(16)), '+', '.');
    $hash = crypt($password, '$2a$' . $cost . '$' . $salt);
    return $hash;
  }
// Хеширование пароля
$hashedPassword = hash_bcrypt($password, 10);
$sqlInsertUser = "INSERT INTO users (name, surname,  password, email, registration_date, role, birthdate, city) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmtInsertUser = $db->prepare($sqlInsertUser);
$stmtInsertUser->bind_param("ssssssss", $username, $surname, $hashedPassword, $email, $timestamp, $role, $dob, $city);
$stmtInsertUser->execute();

// Проверка успешности вставки пользователя
if ($stmtInsertUser->affected_rows > 0) {
    // Получение user_id нового пользователя
    $user_id = $stmtInsertUser->insert_id;

    // Обновление записи пользователя с информацией о кураторе
    $sqlUpdateUser = "UPDATE users SET curator = ? WHERE id = ?";
    $stmtUpdateUser = $db->prepare($sqlUpdateUser);
    $stmtUpdateUser->bind_param("ii", $curator_id, $user_id);
    $stmtUpdateUser->execute();
    $stmtUpdateUser->close();
}

  
// Проверка успешности вставки пользователя
// if ($stmtInsertUser->affected_rows > 0) {
//   // Получение user_id нового пользователя
//   $user_id = $stmtInsertUser->insert_id;

//   // Выбор curator_id на основе curator_username
//   $sqlSelectCuratorId = "SELECT id FROM users WHERE username = ? AND role = 'curator'";
//   $stmtSelectCuratorId = $db->prepare($sqlSelectCuratorId);
//   $stmtSelectCuratorId->bind_param("s", $curatorUsername);
//   $stmtSelectCuratorId->execute();
//   $stmtSelectCuratorId->bind_result($curator_id);
//   $stmtSelectCuratorId->fetch();
//   $stmtSelectCuratorId->close();

//   // Проверка существования куратора
//   if ($curator_id) {
//     // Вставка данных в таблицу user_courses
//     $sqlInsertUserCourses = "INSERT INTO user_courses (user_id, curator_id, progress, course_id) VALUES (?, ?, 0, NULL)";
//     $stmtInsertUserCourses = $db->prepare($sqlInsertUserCourses);
//     $stmtInsertUserCourses->bind_param("ss", $user_id, $curator_id);
//     $stmtInsertUserCourses->execute();

//     // Проверка успешности вставки в user_courses
//     if ($stmtInsertUserCourses->affected_rows > 0) {
//       echo json_encode(['success' => true]);
//     } else {
//       echo json_encode(['error' => 'Ошибка вставки данных в таблицу user_courses']);
//     }
//   } else {
//     echo json_encode(['error' => 'Куратор не существует']);
//   }
// } 
// else {
//   echo json_encode(['error' => 'Ошибка вставки данных в таблицу users']);
// }
?>
