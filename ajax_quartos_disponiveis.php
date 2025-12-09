<?php
require_once __DIR__ . '/database/Database.php';
use Database\Database;

header('Content-Type: application/json');

$checkin = $_GET['checkin'] ?? null;
$checkout = $_GET['checkout'] ?? null;
$reserva_id = $_GET['reserva_id'] ?? null;

if (!$checkin || !$checkout) {
    echo json_encode(['error' => 'Datas não informadas']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $sql = "
        SELECT * FROM quarto q
        WHERE q.status != 'manutencao'
        AND q.id_quarto NOT IN (
            SELECT r.id_quarto FROM reserva r
            WHERE (
                (DATE(r.data_checkin_previsto) < :checkout AND DATE(r.data_checkout_previsto) > :checkin)
                AND r.status IN ('confirmada', 'em andamento')
                " . ($reserva_id ? "AND r.idreserva != :reserva_id" : "") . "
            )
        )
        ORDER BY q.numero ASC
    ";
    $params = [
        ':checkin' => $checkin,
        ':checkout' => $checkout
    ];
    if ($reserva_id) {
        $params[':reserva_id'] = $reserva_id;
    }
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $quartos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    echo json_encode($quartos);
} catch (Exception $e) {
    error_log("Erro ao buscar quartos: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
