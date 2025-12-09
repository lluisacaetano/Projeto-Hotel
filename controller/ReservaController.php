<?php

namespace Controller;

require_once __DIR__ . '/../database/Database.php';

use PDO;
use Exception;
use database\Database;

class ReservaController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // CREATE
    public function criar(array $dados): array {
        try {
            // Calcular valor total
            $checkin = new \DateTime($dados['data_checkin']);
            $checkout = new \DateTime($dados['data_checkout']);
            $noites = $checkout->diff($checkin)->days;
            
            if ($noites < 1) {
                return ['sucesso' => false, 'erros' => ['O checkout deve ser posterior ao checkin.']];
            }
            
            // Buscar preço do quarto
            $sqlQuarto = "SELECT valor_diaria FROM quarto WHERE id_quarto = ?";
            $stmtQuarto = $this->db->prepare($sqlQuarto);
            $stmtQuarto->execute([$dados['quarto_id']]);
            $quarto = $stmtQuarto->fetch(PDO::FETCH_ASSOC);
            
            if (!$quarto) {
                return ['sucesso' => false, 'erros' => ['Quarto não encontrado.']];
            }
            
            $valor_total = $quarto['valor_diaria'] * $noites;
            
            $sql = "INSERT INTO reserva (valor_reserva, data_reserva, data_checkin_previsto, data_checkout_previsto, status, id_funcionario, id_hospede, id_quarto) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $valor_total,
                date('Y-m-d'),
                $dados['data_checkin'],
                $dados['data_checkout'],
                $dados['status'] ?? 'pendente',
                $dados['funcionario_id'],
                $dados['hospede_id'],
                $dados['quarto_id']
            ]);

            return [
                'sucesso' => true, 
                'mensagem' => 'Reserva criada com sucesso!',
                'valor_total' => $valor_total
            ];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // READ ALL
    public function lista(): array {
        try {
            $sql = "SELECT r.*, h.nome as hospede_nome, q.numero as quarto_numero, f.nome as funcionario_nome 
                    FROM reserva r
                    LEFT JOIN hospede ho ON r.id_hospede = ho.id_pessoa
                    LEFT JOIN pessoa h ON ho.id_pessoa = h.id_pessoa
                    LEFT JOIN quarto q ON r.id_quarto = q.id_quarto
                    LEFT JOIN funcionario fu ON r.id_funcionario = fu.id_pessoa
                    LEFT JOIN pessoa f ON fu.id_pessoa = f.id_pessoa
                    ORDER BY r.data_checkin_previsto DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return ['sucesso' => true, 'dados' => $reservas];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro ao listar reservas: ' . $e->getMessage()]];
        }
    }

    // READ ONE
    public function buscarPorId(int $id): array {
        try {
            $sql = "SELECT r.*, h.nome as hospede_nome, q.numero as quarto_numero, f.nome as funcionario_nome 
                    FROM reserva r
                    LEFT JOIN hospede ho ON r.id_hospede = ho.id_pessoa
                    LEFT JOIN pessoa h ON ho.id_pessoa = h.id_pessoa
                    LEFT JOIN quarto q ON r.id_quarto = q.id_quarto
                    LEFT JOIN funcionario fu ON r.id_funcionario = fu.id_pessoa
                    LEFT JOIN pessoa f ON fu.id_pessoa = f.id_pessoa
                    WHERE r.idreserva = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $reserva = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reserva) {
                return ['sucesso' => true, 'dados' => $reserva];
            }
            return ['sucesso' => false, 'erros' => ['Reserva não encontrada.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // UPDATE
    public function atualizar(int $id, array $dados): array {
        try {
            // Calcular valor total atualizado
            $checkin = new \DateTime($dados['data_checkin_previsto']);
            $checkout = new \DateTime($dados['data_checkout_previsto']);
            $noites = $checkout->diff($checkin)->days;

            if ($noites < 1) {
                return ['sucesso' => false, 'erros' => ['O checkout deve ser posterior ao checkin.']];
            }

            // Buscar preço do quarto
            $sqlQuarto = "SELECT valor_diaria FROM quarto WHERE id_quarto = ?";
            $stmtQuarto = $this->db->prepare($sqlQuarto);
            $stmtQuarto->execute([$dados['quarto_id']]);
            $quarto = $stmtQuarto->fetch(PDO::FETCH_ASSOC);

            if (!$quarto) {
                return ['sucesso' => false, 'erros' => ['Quarto não encontrado.']];
            }

            $valor_total = $quarto['valor_diaria'] * $noites;

            // Remover num_hospedes e observacoes do UPDATE se não existem na tabela
            $sql = "UPDATE reserva SET 
                    status = ?, 
                    data_checkin_previsto = ?, 
                    data_checkout_previsto = ?, 
                    id_quarto = ?, 
                    id_funcionario = ?, 
                    id_hospede = ?, 
                    valor_reserva = ?
                WHERE idreserva = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $dados['status'],
                $dados['data_checkin_previsto'],
                $dados['data_checkout_previsto'],
                $dados['quarto_id'],
                $dados['funcionario_id'],
                $dados['hospede_id'],
                $valor_total,
                $id
            ]);

            return ['sucesso' => true, 'mensagem' => 'Reserva atualizada com sucesso!'];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // DELETE
    public function deletar(int $id): array {
        try {
            $sql = "DELETE FROM reserva WHERE idreserva = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            return ['sucesso' => true, 'mensagem' => 'Reserva excluída com sucesso!'];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // CANCELAR RESERVA
    public function cancelar(int $id): array {
        try {
            $sql = "UPDATE reserva SET status = 'cancelada' WHERE idreserva = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            return ['sucesso' => true, 'mensagem' => 'Reserva cancelada com sucesso!'];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // CONFIRMAR RESERVA
    public function confirmar(int $id): array {
        try {
            $sql = "UPDATE reserva SET status = 'confirmada' WHERE idreserva = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            return ['sucesso' => true, 'mensagem' => 'Reserva confirmada com sucesso!'];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // CONCLUIR RESERVA (CHECKOUT)
    public function concluir(int $id): array {
        try {
            $sql = "UPDATE reserva SET status = 'finalizada' WHERE idreserva = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            return ['sucesso' => true, 'mensagem' => 'Check-out realizado com sucesso!'];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // PESQUISAR RESERVA
    public function pesquisar(string $termo): array {
        try {
            $sql = "SELECT r.*, 
                           p.nome AS hospede_nome, 
                           q.numero AS quarto_numero
                    FROM reserva r
                    INNER JOIN hospede h ON r.id_hospede = h.id_pessoa
                    INNER JOIN pessoa p ON h.id_pessoa = p.id_pessoa
                    INNER JOIN quarto q ON r.id_quarto = q.id_quarto
                    WHERE p.nome LIKE :termo_hospede
                       OR CAST(q.numero AS CHAR) LIKE :termo_quarto
                       OR r.status LIKE :termo_status
                    ORDER BY r.idreserva DESC";
            $like = '%' . $termo . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':termo_hospede', $like);
            $stmt->bindValue(':termo_quarto', $like);
            $stmt->bindValue(':termo_status', $like);
            $stmt->execute();
            $reservas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return ['sucesso' => true, 'dados' => $reservas];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro ao pesquisar reservas: ' . $e->getMessage()]];
        }
    }
}
?>