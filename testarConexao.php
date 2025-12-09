<?php
require_once __DIR__ . '/database/Database.php';

use database\Database;

$db = new Database();
$conn = $db->getConnection();

echo $conn ? "Conectado!" : "Erro.";
