<?php

namespace Controller;

require_once __DIR__ . '/../model/Hospede.php';
require_once __DIR__ . '/../model/Pessoa.php';
require_once __DIR__ . '/../model/Endereco.php';
require_once __DIR__ . '/../database/Database.php';

use PDO;
use Exception;
use database\Database;
use model\Hospede;  
use model\Pessoa;
use model\Endereco;
$db = new Database();

class HospedeController {
    private $db;
    private $hospede;
    private $pessoa;
    private $endereco;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->hospede = new Hospede($this->db);
        $this->pessoa = new Pessoa($this->db);
        $this->endereco = new Endereco($this->db);
    }

    public function criar(array $dados): array {
        try {
            $this->db->beginTransaction();

            // Criar endereco
            $this->endereco->setLogradouro($dados['endereco'] ?? null);
            $this->endereco->setNumero(isset($dados['numero']) ? (int)$dados['numero'] : null);
            $this->endereco->setCidade($dados['cidade']);
            $this->endereco->setEstado($dados['estado']);
            $this->endereco->setPais($dados['pais'] ?? 'Brasil');
            $this->endereco->setCep($dados['cep'] ?? null);

            if (!$this->endereco->create()) {
                $this->db->rollBack();
                return ['sucesso' => false, 'erros' => ['Erro ao criar endereco.']];
            }

            // Criar pessoa
            $this->pessoa->setNome($dados['nome']);
            $this->pessoa->setSexo($dados['sexo'] ?? null);
            $this->pessoa->setDataNascimento($dados['data_nascimento'] ?? null);
            $this->pessoa->setDocumento($dados['cpf'] ?? null);
            $this->pessoa->setTelefone($dados['telefone'] ?? null);
            $this->pessoa->setEmail($dados['email'] ?? null);
            $this->pessoa->setTipoPessoa('hospede');
            $this->pessoa->setEnderecoId($this->endereco->getId());

            if (!$this->pessoa->create()) {
                $this->db->rollBack();
                return ['sucesso' => false, 'erros' => ['Erro ao criar pessoa.']];
            }

            // criar hospede incluindo obs no historico
            $this->hospede->setIdPessoa($this->pessoa->getId());
            $this->hospede->setPreferencias($dados['preferencias'] ?? null);
            $this->hospede->setHistorico($dados['observacoes'] ?? null);

            if (!$this->hospede->create()) {
                $this->db->rollBack();
                return ['sucesso' => false, 'erros' => ['Erro ao criar hóspede.']];
            }

            $this->db->commit();
            return [
                'sucesso' => true, 
                'mensagem' => 'Hóspede criado com sucesso!',
                'id' => $this->pessoa->getId()
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    public function lista(): array {
        try {
            $sql = "SELECT p.id_pessoa as id, p.nome, p.email, p.telefone, p.documento, p.data_nascimento,
                           p.sexo, p.data_criacao, e.cidade, e.estado
                    FROM pessoa p
                    LEFT JOIN endereco e ON p.endereco_id_endereco = e.id_endereco
                    WHERE p.tipo_pessoa = 'hospede'
                    ORDER BY p.nome ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $hospedes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['sucesso' => true, 'dados' => $hospedes];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro ao listar hóspedes: ' . $e->getMessage()]];
        }
    }

    public function buscarPorId(int $id): array {
        try {
            $sql = "SELECT p.id_pessoa as id, p.nome, p.email, p.telefone, p.documento, 
                           p.data_nascimento, p.sexo, p.data_criacao as data_cadastro,
                           e.logradouro, e.numero, e.bairro, e.cidade, e.estado, e.cep,
                           h.preferencias, h.historico as observacoes
                    FROM pessoa p
                    LEFT JOIN endereco e ON p.endereco_id_endereco = e.id_endereco
                    LEFT JOIN hospede h ON p.id_pessoa = h.id_pessoa
                    WHERE p.id_pessoa = ? AND p.tipo_pessoa = 'hospede'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($dados) {
                return ['sucesso' => true, 'dados' => $dados];
            }
            return ['sucesso' => false, 'erros' => ['Hóspede não encontrado.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    public function atualizar(int $id, array $dados): array {
        try {
            $this->db->beginTransaction();

            // Buscar o endereco_id da pessoa
            $sql = "SELECT endereco_id_endereco FROM pessoa WHERE id_pessoa = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $enderecoId = $stmt->fetchColumn();

            // Atualizar endereco (se existir)
            if ($enderecoId) {
                $sql = "UPDATE endereco SET logradouro = ?, numero = ?, bairro = ?, 
                        cidade = ?, estado = ?, cep = ? WHERE id_endereco = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    $dados['endereco'] ?? null,
                    isset($dados['numero']) ? (int)$dados['numero'] : null,
                    $dados['bairro'] ?? null,
                    $dados['cidade'],
                    $dados['estado'],
                    $dados['cep'] ?? null,
                    $enderecoId
                ]);
            }

            // Atualizar pessoa
            $sql = "UPDATE pessoa SET nome = ?, sexo = ?, data_nascimento = ?, documento = ?, 
                    telefone = ?, email = ? WHERE id_pessoa = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $dados['nome'],
                $dados['sexo'] ?? null,
                $dados['data_nascimento'] ?? null,
                $dados['documento'] ?? null,
                $dados['telefone'] ?? null,
                $dados['email'] ?? null,
                $id
            ]);

            // Atualizar hóspede
            $sql = "UPDATE hospede SET preferencias = ?, historico = ? WHERE id_pessoa = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $dados['preferencias'] ?? null,
                $dados['observacoes'] ?? null,
                $id
            ]);

            $this->db->commit();
            return ['sucesso' => true, 'mensagem' => 'Hóspede atualizado com sucesso!'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    public function deletar(int $id): array {
        try {
            $this->db->beginTransaction();

            // Deletar hóspede
            $sql = "DELETE FROM hospede WHERE id_pessoa = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            // Deletar pessoa
            $sql = "DELETE FROM pessoa WHERE id_pessoa = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);

            $this->db->commit();
            return ['sucesso' => true, 'mensagem' => 'Hóspede excluído com sucesso!'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    public function pesquisar(string $termo): array {
        try {
            $sql = "SELECT p.id_pessoa as id, p.nome, p.email, p.telefone, p.documento, p.data_nascimento,
                           p.sexo, p.data_criacao, e.cidade, e.estado
                    FROM pessoa p
                    LEFT JOIN endereco e ON p.endereco_id_endereco = e.id_endereco
                    WHERE p.tipo_pessoa = 'hospede'
                      AND (
                          p.nome LIKE :nome
                          OR p.documento LIKE :documento
                      )
                    ORDER BY p.nome ASC";
            $like = '%' . $termo . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $like);
            $stmt->bindValue(':documento', $like);
            $stmt->execute();
            $hospedes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            return ['sucesso' => true, 'dados' => $hospedes];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro ao pesquisar hóspedes: ' . $e->getMessage()]];
        }
    }
}
?>