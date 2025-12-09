<?php
require_once __DIR__ . '/../Database.php';

use database\Database;

$db = new Database();
$conn = $db->getConnection();

$sql = "
INSERT INTO hospede (id_pessoa, preferencias, historico) VALUES
(301, 'Quarto silencioso', 'Cliente regular'),
(302, 'Vista para o mar', 'Primeira estadia'),
(303, 'Cama king size', 'Cliente VIP'),
(304, 'Quarto com varanda', 'Cliente regular'),
(305, 'Andar térreo', 'Segunda estadia');
";

$conn->exec($sql);

echo "Tabela hospede povoada com sucesso.\n";