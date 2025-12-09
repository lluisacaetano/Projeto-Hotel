<?php

namespace Controller;

require_once __DIR__ . '/../model/Quarto.php';
require_once __DIR__ . '/../database/Database.php';

use database\Database;
use model\Quarto;

class QuartoController {
    private $db;
    private $quarto;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->quarto = new Quarto($this->db);
    }

    // CREATE
    public function criar(array $dados): array {
        try {
            $this->quarto->setNumero((int)$dados['numero']);
            $this->quarto->setAndar((int)$dados['andar']);
            $this->quarto->setTipo($dados['tipo_quarto']);
            $this->quarto->setValorDiaria((float)$dados['valor_diaria']);
            $this->quarto->setCapacidade((int)$dados['capacidade_maxima']);
            $this->quarto->setDescricao($dados['descricao'] ?? null);
            $this->quarto->setStatus($dados['status'] ?? 'disponivel');

            if ($this->quarto->create()) {
                return ['sucesso' => true, 'mensagem' => 'Quarto criado com sucesso!'];
            }

            return ['sucesso' => false, 'erros' => ['Erro ao criar quarto.']];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // READ ALL
    public function lista(): array {
        try {
            $stmt = $this->quarto->read();
            $quartos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return ['sucesso' => true, 'dados' => $quartos];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro ao lista quartos: ' . $e->getMessage()]];
        }
    }

    // READ ONE
    public function buscarPorId(int $id): array {
        try {
            $this->quarto->setId($id);
            
            if ($this->quarto->readOne()) {
                return ['sucesso' => true, 'dados' => $this->quarto->toArray()];
            }

            return ['sucesso' => false, 'erros' => ['Quarto nao encontrado.']];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // UPDATE
    public function atualizar(int $id, array $dados): array {
        try {
            $this->quarto->setId($id);
            
            if (!$this->quarto->readOne()) {
                return ['sucesso' => false, 'erros' => ['Quarto nao encontrado.']];
            }

            $this->quarto->setNumero((int)$dados['numero']);
            $this->quarto->setAndar((int)$dados['andar']);
            $this->quarto->setTipo($dados['tipo_quarto']);
            $this->quarto->setValorDiaria((float)$dados['valor_diaria']);
            $this->quarto->setCapacidade((int)$dados['capacidade_maxima']);
            $this->quarto->setDescricao($dados['descricao'] ?? null);
            $this->quarto->setStatus($dados['status']);

            if ($this->quarto->update()) {
                return ['sucesso' => true, 'mensagem' => 'Quarto atualizado com sucesso!'];
            }

            return ['sucesso' => false, 'erros' => ['Erro ao atualizar quarto.']];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // DELETE
    public function deletar(int $id): array {
        try {
            $this->quarto->setId($id);
            
            if (!$this->quarto->readOne()) {
                return ['sucesso' => false, 'erros' => ['Quarto nao encontrado.']];
            }

            if ($this->quarto->delete()) {
                return ['sucesso' => true, 'mensagem' => 'Quarto excluído com sucesso!'];
            }

            return ['sucesso' => false, 'erros' => ['Erro ao excluir quarto.']];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    public function pesquisar(string $termo): array {
        try {
            $sql = "SELECT * FROM quarto
                    WHERE 
                        CAST(numero AS CHAR) LIKE :termo_numero
                        OR tipo_quarto LIKE :termo_tipo
                        OR status LIKE :termo_status
                    ORDER BY numero ASC";
            $like = '%' . $termo . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':termo_numero', $like);
            $stmt->bindValue(':termo_tipo', $like);
            $stmt->bindValue(':termo_status', $like);
            $stmt->execute();
            $quartos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return ['sucesso' => true, 'dados' => $quartos];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro ao pesquisar quartos: ' . $e->getMessage()]];
        }
    }
}
?>