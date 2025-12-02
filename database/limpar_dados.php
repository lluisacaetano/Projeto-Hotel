<?php
require_once __DIR__ . '/../Database.php';

$db = new Database();
$conn = $db->getConnection();

// Desativa FK
$conn->exec("SET FOREIGN_KEY_CHECKS = 0;");

// Apaga tudo na ordem certa
$tables = [
    'item_has_consumo',
    'item',
    'consumo',
    'pagamento',
    'reserva',
    'quarto_luxo',
    'quarto',
    'funcionario',
    'hospede',
    'pessoa',
    'endereco'
];

foreach ($tables as $t) {
    $conn->exec("TRUNCATE TABLE $t;");
    echo "Limpou: $t\n";
}

// Ativa FK
$conn->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "Todas as tabelas foram limpas com sucesso.\n";