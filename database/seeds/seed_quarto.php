<?php
require_once __DIR__ . '/../Database.php';

use database\Database;

$db = new Database();
$conn = $db->getConnection();

$sql = "
    INSERT INTO quarto (id_quarto, status, numero, andar, descricao, valor_diaria, capacidade_maxima, tipo_quarto) VALUES
    (401, 'disponivel', 101, 1, 'Quarto Standard com cama casal', 150.00, 2, 'Standard'),
    (402, 'ocupado', 102, 1, 'Quarto Standard com duas camas', 150.00, 2, 'Standard'),
    (403, 'disponivel', 201, 2, 'Quarto Standard com vista jardim', 150.00, 2, 'Standard'),
    (404, 'manutencao', 301, 3, 'Suíte Luxo com hidromassagem', 450.00, 2, 'Luxo'),
    (405, 'disponivel', 401, 4, 'Cobertura duplex', 800.00, 4, 'Suite');
";
$conn->exec($sql);

echo "Tabela quarto povoada com sucesso.\n";