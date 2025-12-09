<?php
namespace Controller;

require_once __DIR__ . '/../database/Database.php';

use database\Database;
use PDO;

class RelatorioController {
    public $conn; // <-- alterado de private para public

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /**
     * Consulta com junção: Lista hóspedes e seus quartos atuais.
     * Junta as tabelas pessoa, hospede, reserva e quarto.
     */
    public function hospedesComQuartoAtual() {
        $sql = "SELECT p.nome, q.numero AS numero_quarto, r.data_checkin_previsto, r.data_checkout_previsto
                FROM pessoa p
                INNER JOIN hospede h ON p.id_pessoa = h.id_pessoa
                INNER JOIN reserva r ON h.id_pessoa = r.id_hospede
                INNER JOIN quarto q ON r.id_quarto = q.id_quarto
                WHERE r.status = 'confirmada' AND CURDATE() BETWEEN r.data_checkin_previsto AND r.data_checkout_previsto";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta com group by e função agregada: Top hóspedes por número de reservas.
     * Agrupa por hóspede e conta reservas.
     */
    public function hospedesMaisFrequentes($limit = 10) {
        $sql = "SELECT p.nome, p.email, COUNT(r.idreserva) AS total_reservas,
                       SUM(r.valor_reserva) AS valor_total_gasto,
                       AVG(r.valor_reserva) AS ticket_medio,
                       MAX(r.data_checkout_previsto) AS ultima_visita,
                       MIN(r.data_checkin_previsto) AS primeira_visita
                FROM pessoa p
                INNER JOIN hospede h ON p.id_pessoa = h.id_pessoa
                INNER JOIN reserva r ON h.id_pessoa = r.id_hospede
                WHERE r.status IN ('confirmada', 'finalizada')
                GROUP BY p.id_pessoa
                ORDER BY total_reservas DESC
                LIMIT ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limit]);
        return ['sucesso' => true, 'dados' => $stmt->fetchAll(PDO::FETCH_ASSOC)];
    }

    /**
     * Consulta com função de data: Reservas que vão vencer nos próximos 3 dias.
     */
    public function reservasVencendo() {
        $sql = "SELECT r.*, p.nome, q.numero AS numero_quarto
                FROM reserva r
                INNER JOIN hospede h ON r.id_hospede = h.id_pessoa
                INNER JOIN pessoa p ON h.id_pessoa = p.id_pessoa
                INNER JOIN quarto q ON r.id_quarto = q.id_quarto
                WHERE r.status = 'confirmada'
                AND r.data_checkout_previsto BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta aninhada: Hóspedes que nunca fizeram reserva.
     */
    public function hospedesSemReserva() {
        $sql = "SELECT p.nome, p.email
                FROM pessoa p
                INNER JOIN hospede h ON p.id_pessoa = h.id_pessoa
                WHERE p.id_pessoa NOT IN (
                    SELECT r.id_hospede FROM reserva r
                )";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta com group by, having e função agregada: Hóspedes com mais de 5 reservas.
     */
    public function hospedesVIP() {
        $sql = "SELECT p.nome, COUNT(r.idreserva) AS total_reservas
                FROM pessoa p
                INNER JOIN hospede h ON p.id_pessoa = h.id_pessoa
                INNER JOIN reserva r ON h.id_pessoa = r.id_hospede
                GROUP BY p.id_pessoa
                HAVING total_reservas > 5
                ORDER BY total_reservas DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta com função de data: Reservas feitas no último mês.
     */
    public function reservasUltimoMes() {
        $sql = "SELECT r.*, p.nome
                FROM reserva r
                INNER JOIN hospede h ON r.id_hospede = h.id_pessoa
                INNER JOIN pessoa p ON h.id_pessoa = p.id_pessoa
                WHERE r.data_checkin_previsto >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta aninhada: Quartos nunca reservados.
     */
    public function quartosNuncaReservados() {
        $sql = "SELECT q.numero, q.tipo_quarto
                FROM quarto q
                WHERE q.id_quarto NOT IN (
                    SELECT r.id_quarto FROM reserva r
                )";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta com função de data: Hóspedes que fizeram check-in hoje.
     */
    public function hospedesCheckinHoje() {
        $sql = "SELECT p.nome, r.data_checkin_previsto
                FROM pessoa p
                INNER JOIN hospede h ON p.id_pessoa = h.id_pessoa
                INNER JOIN reserva r ON h.id_pessoa = r.id_hospede
                WHERE DATE(r.data_checkin_previsto) = CURDATE()";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta com junção: Funcionários que realizaram reservas (exemplo de join).
     */
    public function reservasPorFuncionario() {
        $sql = "SELECT f.nome AS funcionario, COUNT(r.idreserva) AS total_reservas
                FROM funcionario fu
                INNER JOIN pessoa f ON fu.id_pessoa = f.id_pessoa
                INNER JOIN reserva r ON fu.id_pessoa = r.id_funcionario
                GROUP BY f.id_pessoa";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta aninhada: Reservas feitas por hóspedes VIP (aqueles com mais de 5 reservas).
     */
    public function reservasPorHospedesVIP() {
        $sql = "SELECT r.*, p.nome
                FROM reserva r
                INNER JOIN hospede h ON r.id_hospede = h.id_pessoa
                INNER JOIN pessoa p ON h.id_pessoa = p.id_pessoa
                WHERE r.id_hospede IN (
                    SELECT h.id_pessoa
                    FROM reserva r2
                    INNER JOIN hospede h ON r2.id_hospede = h.id_pessoa
                    GROUP BY h.id_pessoa
                    HAVING COUNT(r2.idreserva) > 5
                )";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function dashboard() {
        // Estatísticas gerais
        $estatisticas = [
            'total_hospedes' => 0,
            'total_reservas' => 0,
            'receita_total' => 0,
            'ticket_medio_geral' => 0
        ];
        $hospedes_checkin_ativo = [];

        // Total de hóspedes
        $sql = "SELECT COUNT(*) as total FROM pessoa WHERE tipo_pessoa = 'hospede'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $estatisticas['total_hospedes'] = (int)$stmt->fetchColumn();

        // Total de reservas
        $sql = "SELECT COUNT(*) as total FROM reserva";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $estatisticas['total_reservas'] = (int)$stmt->fetchColumn();

        // Receita total (apenas reservas finalizadas)
        $sql = "SELECT SUM(valor_reserva) as receita FROM reserva WHERE status = 'finalizada'";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $estatisticas['receita_total'] = (float)($row['receita'] ?? 0);

        // Ticket médio geral
        $estatisticas['ticket_medio_geral'] = $estatisticas['total_reservas'] > 0
            ? $estatisticas['receita_total'] / $estatisticas['total_reservas']
            : 0;

        // Hóspedes com check-in ativo (reservas em andamento)
        $sql = "SELECT p.nome, q.numero AS numero_quarto, r.data_checkin_previsto AS data_checkin, r.data_checkout_previsto AS data_checkout,
                       DATEDIFF(r.data_checkout_previsto, CURDATE()) AS dias_restantes, p.telefone
                FROM pessoa p
                INNER JOIN hospede h ON p.id_pessoa = h.id_pessoa
                INNER JOIN reserva r ON h.id_pessoa = r.id_hospede
                INNER JOIN quarto q ON r.id_quarto = q.id_quarto
                WHERE r.status = 'em andamento'
                  AND CURDATE() BETWEEN r.data_checkin_previsto AND r.data_checkout_previsto";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $hospedes_checkin_ativo = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'sucesso' => true,
            'estatisticas' => $estatisticas,
            'hospedes_checkin_ativo' => $hospedes_checkin_ativo
        ];
    }
}
