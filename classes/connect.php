<?php 

class Database
{
	private $host;
	private $username;
	private $password;
	private $db;
	private $conn;
 
	public function __construct()
	{
		// Load environment variables
		$this->load_env();
		$this->connect();
	}

	private function load_env()
	{
		$env_file = __DIR__ . '/../.env';
		
		if (file_exists($env_file)) {
			$lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			foreach ($lines as $line) {
				if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
					list($key, $value) = explode('=', $line, 2);
					$_ENV[trim($key)] = trim($value);
				}
			}
		}
		
		$this->host = $_ENV['DB_HOST'] ?? 'localhost';
		$this->username = $_ENV['DB_USERNAME'] ?? 'root';
		$this->password = $_ENV['DB_PASSWORD'] ?? '';
		$this->db = $_ENV['DB_NAME'] ?? 'mybook_db';
	}

	private function connect()
	{
		$this->conn = mysqli_connect($this->host, $this->username, $this->password, $this->db);
		
		if (!$this->conn) {
			die("Connection failed: " . mysqli_connect_error());
		}
		
		// Set charset to utf8mb4
		mysqli_set_charset($this->conn, "utf8mb4");
	}

	public function get_connection()
	{
		return $this->conn;
	}

	// DEPRECATED: Use read_prepared() instead
	function read($query)
	{
		$result = mysqli_query($this->conn, $query);

		if (!$result) {
			return false;
		} else {
			$data = false;
			while ($row = mysqli_fetch_assoc($result)) {
				$data[] = $row;
			}
			return $data;
		}
	}

	// DEPRECATED: Use save_prepared() instead
	function save($query)
	{
		$result = mysqli_query($this->conn, $query);

		if (!$result) {
			return false;
		} else {
			return true;
		}
	}

	// Prepared statement for SELECT queries
	public function read_prepared($query, $types = "", $params = array())
	{
		$stmt = $this->conn->prepare($query);
		
		if (!$stmt) {
			return false;
		}

		if (!empty($params) && !empty($types)) {
			$stmt->bind_param($types, ...$params);
		}

		if (!$stmt->execute()) {
			return false;
		}

		$result = $stmt->get_result();
		$data = false;

		while ($row = $result->fetch_assoc()) {
			$data[] = $row;
		}

		$stmt->close();
		return $data;
	}

	// Prepared statement for INSERT/UPDATE/DELETE queries
	public function save_prepared($query, $types = "", $params = array())
	{
		$stmt = $this->conn->prepare($query);
		
		if (!$stmt) {
			return false;
		}

		if (!empty($params) && !empty($types)) {
			$stmt->bind_param($types, ...$params);
		}

		if (!$stmt->execute()) {
			return false;
		}

		$stmt->close();
		return true;
	}

	// Get last inserted ID
	public function get_last_id()
	{
		return $this->conn->insert_id;
	}

	// Close connection
	public function close()
	{
		if ($this->conn) {
			$this->conn->close();
		}
	}

}
