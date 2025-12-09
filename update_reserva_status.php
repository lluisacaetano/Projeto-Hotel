<?php
require_once __DIR__ . '/database/Database.php';
use database\Database;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? null;

    if (!$id || !in_array($status, ['em andamento', 'finalizada'])) {
        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        // Atualizar status da reserva
        $stmt = $conn->prepare("UPDATE reserva SET status = ? WHERE idreserva = ?");
        $stmt->execute([$status, $id]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Reserva não encontrada']);
        }
        exit;
        
    } catch (Exception $e) {
        error_log("Erro ao atualizar status da reserva: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Método não permitido']);