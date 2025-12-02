<?php
require_once __DIR__ . '/../Database.php';

$db = new Database();
$conn = $db->getConnection();

$sql = "
    INSERT INTO funcionario (id_pessoa, cargo, salario, data_contratacao, numero_ctps, turno) VALUES
    (16, 'Recepcionista', 2500.00, '2023-01-15', 123456, 'Manhã'),
    (17, 'Recepcionista', 2500.00, '2023-03-20', 234567, 'Tarde'),
    (18, 'Gerente', 5500.00, '2022-06-10', 345678, 'Integral'),
    (19, 'Camareira', 1800.00, '2023-05-05', 456789, 'Manhã'),
    (20, 'Camareiro', 1800.00, '2023-07-12', 567890, 'Tarde'),
    (21, 'Concierge', 3000.00, '2022-11-20', 678901, 'Noite'),
    (22, 'Supervisor de Limpeza', 3200.00, '2022-09-15', 789012, 'Manhã'),
    (23, 'Atendente de Reservas', 2200.00, '2023-02-28', 890123, 'Integral'),
    (24, 'Auxiliar Administrativo', 2000.00, '2023-08-10', 901234, 'Tarde'),
    (25, 'Coordenador de Eventos', 4000.00, '2022-12-01', 012345, 'Integral');

";

$conn->exec($sql);

echo "Tabela hospede povoada com sucesso.\n";