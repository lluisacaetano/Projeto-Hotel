<?php
require_once __DIR__ . '/../Database.php';

use database\Database;

$db = new Database();
$conn = $db->getConnection();

$sql = "
    INSERT INTO funcionario (id_pessoa, cargo, salario, data_contratacao, numero_ctps, turno) VALUES
    (311, 'Recepcionista', 2500.00, '2023-01-15', 123456, 'Manhã'),
    (312, 'Gerente', 5500.00, '2022-06-10', 234567, 'Integral'),
    (313, 'Camareira', 1800.00, '2023-05-05', 345678, 'Manhã'),
    (314, 'Concierge', 3000.00, '2022-11-20', 456789, 'Noite'),
    (315, 'Supervisor', 3200.00, '2022-09-15', 567890, 'Manhã');
";

$conn->exec($sql);

echo "Tabela funcionario povoada com sucesso.\n";