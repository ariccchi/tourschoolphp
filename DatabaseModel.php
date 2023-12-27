<?php
require_once "config.php";
class DatabaseModel {
	private $host = DB_HOST; // Хост базы данных
	private $username = DB_USER; // Имя пользователя
	private $password = DB_PASSWORD; // Пароль
	private $dbname = DB_NAME; // Имя базы данных
	private $charset = DB_CHARSET; // Кодировка

	private $connection;

	public function __construct() {
    	$this->connect();
	}

	// Метод для установки соединения с базой данных
	private function connect() {
    	$this->connection = new mysqli($this->host, $this->username, $this->password, $this->dbname);

    	if ($this->connection->connect_error) {
        	die("Ошибка подключения к базе данных: " . $this->connection->connect_error);
    	}

    	$this->connection->set_charset($this->charset);
	}

	// Метод для выполнения SQL-запроса
	public function query($sql) {
    	return $this->connection->query($sql);
	}

	// Метод для экранирования строк перед выполнением SQL-запроса
	public function escapeString($str) {
    	return $this->connection->real_escape_string($str);
	}

	// Метод для закрытия соединения с базой данных
	public function close() {
    	$this->connection->close();
	}
    public function ping() {
        return $this->connection->ping();
    }
	public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
	public function insertNews($title, $image_url, $news_text, $date_written) {
		$stmt = $this->connection->prepare("INSERT INTO news (title, image_url, news_text, date_written) VALUES (?, ?, ?, ?)");
		$stmt->bind_param("ssss", $title, $image_url, $news_text, $date_written);
		return $stmt->execute();
	}
	public function getInsertId() {
		return $this->connection->insert_id;
	}
	
	
}
