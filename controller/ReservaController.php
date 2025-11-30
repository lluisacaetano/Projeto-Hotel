<?php

require_once __DIR__ . '/../model/Reserva.php';
require_once __DIR__ . '/../model/Quarto.php';
require_once __DIR__ . '/../database/Database.php';

class ReservaController {
    private $db;
    private $reserva;
    private $quarto;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->reserva = new Reserva($this->db);
        $this->quarto = new Quarto($this->db);
    }

    // CREATE
    public function criar(array $dados): array {
        try {
            $this->reserva->setHospedeId((int)$dados['hospede_id']);
            $this->reserva->setQuartoId((int)$dados['quarto_id']);
            $this->reserva->setFuncionarioId((int)$dados['funcionario_id']);
            $this->reserva->setDataCheckin($dados['data_checkin']);
            $this->reserva->setDataCheckout($dados['data_checkout']);
            $this->reserva->setNumHospedes((int)$dados['num_hospedes']);
            $this->reserva->setStatus($dados['status'] ?? 'pendente');
            $this->reserva->setObservacoes($dados['observacoes'] ?? null);

            // Buscar preço do quarto e calcular valor total
            $this->quarto->setId((int)$dados['quarto_id']);
            if ($this->quarto->readOne()) {
                $preco_diaria = $this->quarto->getPrecoDiaria();
                $valor_total = $this->reserva->calcularValorTotal($preco_diaria);
                $this->reserva->setValorTotal($valor_total);
            } else {
                return ['sucesso' => false, 'erros' => ['Quarto não encontrado.']];
            }

            $erros = $this->reserva->validar();
            
            if (!empty($erros)) {
                return ['sucesso' => false, 'erros' => $erros];
            }

            if ($this->reserva->create()) {
                return [
                    'sucesso' => true, 
                    'mensagem' => 'Reserva criada com sucesso!',
                    'valor_total' => $valor_total
                ];
            }

            return ['sucesso' => false, 'erros' => ['Erro ao criar reserva.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // READ ALL
    public function listar(): array {
        try {
            $stmt = $this->reserva->read();
            $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['sucesso' => true, 'dados' => $reservas];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro ao listar reservas: ' . $e->getMessage()]];
        }
    }

    // READ ONE
    public function buscarPorId(int $id): array {
        try {
            $this->reserva->setId($id);
            
            if ($this->reserva->readOne()) {
                return ['sucesso' => true, 'dados' => $this->reserva->toArray()];
            }

            return ['sucesso' => false, 'erros' => ['Reserva não encontrada.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // UPDATE
    public function atualizar(int $id, array $dados): array {
        try {
            $this->reserva->setId($id);
            
            if (!$this->reserva->readOne()) {
                return ['sucesso' => false, 'erros' => ['Reserva não encontrada.']];
            }

            $this->reserva->setHospedeId((int)$dados['hospede_id']);
            $this->reserva->setQuartoId((int)$dados['quarto_id']);
            $this->reserva->setFuncionarioId((int)$dados['funcionario_id']);
            $this->reserva->setDataCheckin($dados['data_checkin']);
            $this->reserva->setDataCheckout($dados['data_checkout']);
            $this->reserva->setNumHospedes((int)$dados['num_hospedes']);
            $this->reserva->setStatus($dados['status']);
            $this->reserva->setObservacoes($dados['observacoes'] ?? null);

            // Recalcular valor total
            $this->quarto->setId((int)$dados['quarto_id']);
            if ($this->quarto->readOne()) {
                $preco_diaria = $this->quarto->getPrecoDiaria();
                $valor_total = $this->reserva->calcularValorTotal($preco_diaria);
                $this->reserva->setValorTotal($valor_total);
            }

            $erros = $this->reserva->validar();
            
            if (!empty($erros)) {
                return ['sucesso' => false, 'erros' => $erros];
            }

            if ($this->reserva->update()) {
                return ['sucesso' => true, 'mensagem' => 'Reserva atualizada com sucesso!'];
            }

            return ['sucesso' => false, 'erros' => ['Erro ao atualizar reserva.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // DELETE
    public function deletar(int $id): array {
        try {
            $this->reserva->setId($id);
            
            if (!$this->reserva->readOne()) {
                return ['sucesso' => false, 'erros' => ['Reserva não encontrada.']];
            }

            if ($this->reserva->delete()) {
                return ['sucesso' => true, 'mensagem' => 'Reserva excluída com sucesso!'];
            }

            return ['sucesso' => false, 'erros' => ['Apenas reservas pendentes ou canceladas podem ser excluídas.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // CANCELAR RESERVA
    public function cancelar(int $id): array {
        try {
            $this->reserva->setId($id);
            
            if (!$this->reserva->readOne()) {
                return ['sucesso' => false, 'erros' => ['Reserva não encontrada.']];
            }

            if ($this->reserva->cancelar()) {
                return ['sucesso' => true, 'mensagem' => 'Reserva cancelada com sucesso!'];
            }

            return ['sucesso' => false, 'erros' => ['Erro ao cancelar reserva.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // CONFIRMAR RESERVA
    public function confirmar(int $id): array {
        try {
            $this->reserva->setId($id);
            
            if (!$this->reserva->readOne()) {
                return ['sucesso' => false, 'erros' => ['Reserva não encontrada.']];
            }

            if ($this->reserva->confirmar()) {
                return ['sucesso' => true, 'mensagem' => 'Reserva confirmada com sucesso!'];
            }

            return ['sucesso' => false, 'erros' => ['Erro ao confirmar reserva.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // CONCLUIR RESERVA (CHECKOUT)
    public function concluir(int $id): array {
        try {
            $this->reserva->setId($id);
            
            if (!$this->reserva->readOne()) {
                return ['sucesso' => false, 'erros' => ['Reserva não encontrada.']];
            }

            if ($this->reserva->concluir()) {
                return ['sucesso' => true, 'mensagem' => 'Check-out realizado com sucesso!'];
            }

            return ['sucesso' => false, 'erros' => ['Erro ao concluir reserva.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // LISTAR POR PERÍODO
    public function listarPorPeriodo(string $data_inicio, string $data_fim): array {
        try {
            $reservas = $this->reserva->listarPorPeriodo($data_inicio, $data_fim);
            return ['sucesso' => true, 'dados' => $reservas];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // LISTAR POR STATUS
    public function listarPorStatus(string $status): array {
        try {
            $reservas = $this->reserva->listarPorStatus($status);
            return ['sucesso' => true, 'dados' => $reservas];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }
}
?>