<?php

require_once __DIR__ . '/../model/Hospede.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../model/Endereco.php';

require_once __DIR__ . '/../controller/HospedeController.php';
require_once __DIR__ . '/../model/Hospede.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../utils/Validacoes.php';
require_once __DIR__ . '/../utils/Formatter.php';

class HospedeController {
    private $db;
    private $hospede;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->hospede = new Hospede($this->db);
    }

    // CREATE
    public function criar(array $dados): array {
        try {
            $this->hospede->setNome($dados['nome']);
            $this->hospede->setCpf($dados['cpf']);
            $this->hospede->setEmail($dados['email']);
            $this->hospede->setTelefone($dados['telefone']);
            $this->hospede->setEndereco($dados['endereco'] ?? null);
            $this->hospede->setDataNascimento($dados['data_nascimento'] ?? null);

            $erros = $this->hospede->validar();
            
            if (!empty($erros)) {
                return ['sucesso' => false, 'erros' => $erros];
            }

            if ($this->hospede->create()) {
                return ['sucesso' => true, 'mensagem' => 'Hóspede cadastrado com sucesso!'];
            }

            return ['sucesso' => false, 'erros' => ['Erro ao cadastrar hóspede.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // READ ALL
    public function listar(): array {
        try {
            $stmt = $this->hospede->read();
            $hospedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['sucesso' => true, 'dados' => $hospedes];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro ao listar hóspedes: ' . $e->getMessage()]];
        }
    }

    // READ ONE
    public function buscarPorId(int $id): array {
        try {
            $this->hospede->setId($id);
            
            if ($this->hospede->readOne()) {
                return ['sucesso' => true, 'dados' => $this->hospede->toArray()];
            }

            return ['sucesso' => false, 'erros' => ['Hóspede não encontrado.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // UPDATE
    public function atualizar(int $id, array $dados): array {
        try {
            $this->hospede->setId($id);
            
            if (!$this->hospede->readOne()) {
                return ['sucesso' => false, 'erros' => ['Hóspede não encontrado.']];
            }

            $this->hospede->setNome($dados['nome']);
            $this->hospede->setCpf($dados['cpf']);
            $this->hospede->setEmail($dados['email']);
            $this->hospede->setTelefone($dados['telefone']);
            $this->hospede->setEndereco($dados['endereco'] ?? null);
            $this->hospede->setDataNascimento($dados['data_nascimento'] ?? null);

            $erros = $this->hospede->validar();
            
            if (!empty($erros)) {
                return ['sucesso' => false, 'erros' => $erros];
            }

            if ($this->hospede->update()) {
                return ['sucesso' => true, 'mensagem' => 'Hóspede atualizado com sucesso!'];
            }

            return ['sucesso' => false, 'erros' => ['Erro ao atualizar hóspede.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // DELETE
    public function deletar(int $id): array {
        try {
            $this->hospede->setId($id);
            
            if (!$this->hospede->readOne()) {
                return ['sucesso' => false, 'erros' => ['Hóspede não encontrado.']];
            }

            if ($this->hospede->delete()) {
                return ['sucesso' => true, 'mensagem' => 'Hóspede excluído com sucesso!'];
            }

            return ['sucesso' => false, 'erros' => ['Não é possível excluir hóspede com reservas ativas.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // HISTÓRICO DE RESERVAS
    public function obterHistorico(int $id): array {
        try {
            $this->hospede->setId($id);
            
            if (!$this->hospede->readOne()) {
                return ['sucesso' => false, 'erros' => ['Hóspede não encontrado.']];
            }

            $historico = $this->hospede->obterHistoricoReservas();
            
            return [
                'sucesso' => true, 
                'hospede' => $this->hospede->toArray(),
                'reservas' => $historico
            ];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    // BUSCAR POR CPF
    public function buscarPorCpf(string $cpf): array {
        try {
            if ($this->hospede->buscarPorCpf($cpf)) {
                return ['sucesso' => true, 'dados' => $this->hospede->toArray()];
            }

            return ['sucesso' => false, 'erros' => ['Hóspede não encontrado.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }
}
?>