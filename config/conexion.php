<?php
class Database {
    private $host = 'localhost';
    private $port = '3306'; 
    private $db_name = 'DesireCloset';
    private $username = 'oly';
    private $password = '';
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}

// Crear una instancia de la clase Database y obtener la conexión
$database = new Database();
$conn = $database->getConnection();
?>
