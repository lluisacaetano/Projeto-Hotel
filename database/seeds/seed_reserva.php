<?php
require_once __DIR__ . '/../Database.php';

use database\Database;

$db = new Database();
$conn = $db->getConnection();

$sql = "
    INSERT INTO reserva (valor_reserva, data_reserva, data_checkin_previsto, data_checkout_previsto, status, id_funcionario, id_hospede, id_quarto) VALUES
    (450.00, '2024-10-01', '2024-10-15', '2024-10-18', 'confirmada', 311, 301, 401),
    (660.00, '2024-10-05', '2024-10-20', '2024-10-23', 'confirmada', 312, 302, 402),
    (1350.00, '2024-09-20', '2024-10-10', '2024-10-13', 'finalizada', 313, 303, 403),
    (750.00, '2024-11-01', '2024-11-15', '2024-11-20', 'confirmada', 314, 304, 404),
    (1440.00, '2024-10-28', '2024-11-10', '2024-11-13', 'confirmada', 315, 305, 405);
";

try {
    $conn->exec($sql);
    echo "✅ Tabela reserva povoada com sucesso.\n";
} catch (Exception $e) {
    echo "❌ Erro ao popular tabela: " . $e->getMessage() . "\n";
}
?>