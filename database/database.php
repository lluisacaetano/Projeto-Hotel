<?php

class Database {
    private $host = "localhost";
    private $db_name = "mydb";
    private $username = "root";
    private $password = "bd2025";  // ← SENHA VAZIA (tente sem senha)
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET NAMES utf8mb4");
            
        } catch (PDOException $e) {
            die("❌ ERRO DE CONEXÃO: " . $e->getMessage());
        }

        return $this->conn;
    }
}
?>
