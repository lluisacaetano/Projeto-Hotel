<?php

namespace Controller;

require_once __DIR__ . '/../model/Funcionario.php';
require_once __DIR__ . '/../model/Pessoa.php';
require_once __DIR__ . '/../model/Endereco.php';
require_once __DIR__ . '/../database/Database.php';

use PDO;
use Exception;
use database\Database;
use model\Funcionario;
use model\Pessoa;
use model\Endereco;

class FuncionarioController {
    private $db;
    private $funcionario;
    private $pessoa;
    private $endereco;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->funcionario = new Funcionario($this->db);
        $this->pessoa = new Pessoa($this->db);
        $this->endereco = new Endereco($this->db);
    }

    public function criar(array $dados): array {
        try {
            $this->db->beginTransaction();

            // Criar endereço
            $this->endereco->setLogradouro($dados['endereco'] ?? null);
            $this->endereco->setNumero(!empty($dados['numero']) ? (int)filter_var($dados['numero'], FILTER_SANITIZE_NUMBER_INT) : null);
            $this->endereco->setBairro($dados['bairro'] ?? null);
            $this->endereco->setCidade($dados['cidade']);
            $this->endereco->setEstado($dados['estado']);
            $this->endereco->setPais($dados['pais'] ?? 'Brasil');
            $this->endereco->setCep($dados['cep'] ?? null);

            if (!$this->endereco->create()) {
                $this->db->rollBack();
                return ['sucesso' => false, 'erros' => ['Erro ao criar endereço.']];
            }

            // Criar pessoa
            $this->pessoa->setNome($dados['nome']);
            $this->pessoa->setSexo($dados['sexo'] ?? null);
            $this->pessoa->setDataNascimento($dados['data_nascimento'] ?? null);
            $this->pessoa->setDocumento($dados['cpf'] ?? null);
            $this->pessoa->setTelefone($dados['telefone'] ?? null);
            $this->pessoa->setEmail($dados['email'] ?? null);
            $this->pessoa->setTipoPessoa('funcionario');
            $this->pessoa->setEnderecoId($this->endereco->getId());

            if (!$this->pessoa->create()) {
                $this->db->rollBack();
                return ['sucesso' => false, 'erros' => ['Erro ao criar pessoa.']];
            }

            // Criar funcionário
            $this->funcionario->setIdPessoa($this->pessoa->getId());
            $this->funcionario->setCargo($dados['cargo'] ?? null);
            
            // Converter salário formatado para float
            $salario = null;
            if (!empty($dados['salario'])) {
                $salario = str_replace(['R$ ', '.', ','], ['', '', '.'], $dados['salario']);
                $salario = floatval($salario);
            }
            $this->funcionario->setSalario($salario);
            
            $this->funcionario->setTurno($dados['turno'] ?? null);
            $this->funcionario->setDataContratacao($dados['data_contratacao'] ?? date('Y-m-d'));
            
            // Converter CTPS: pega apenas os primeiros 7 dígitos (antes da barra)
            $ctps = null;
            if (!empty($dados['numero_ctps'])) {
                // Remove tudo que não é número
                $ctps = preg_replace('/\D/', '', $dados['numero_ctps']);
                // Pega apenas os primeiros 7 dígitos
                $ctps = substr($ctps, 0, 7);
                $ctps = !empty($ctps) ? (int)$ctps : null;
            }
            $this->funcionario->setNumeroCTPS($ctps);

            if (!$this->funcionario->create()) {
                $this->db->rollBack();
                return ['sucesso' => false, 'erros' => ['Erro ao criar funcionário.']];
            }

            $this->db->commit();
            return [
                'sucesso' => true, 
                'mensagem' => 'Funcionário criado com sucesso!',
                'id' => $this->pessoa->getId()
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    public function lista(): array {
        try {
            // Mostra todos os funcionários cadastrados na tabela funcionario,
            // trazendo dados da pessoa se existir, senão mostra só os dados da tabela funcionario.
            $sql = "
                SELECT 
                    f.id_pessoa as id,
                    p.nome,
                    p.documento as cpf,
                    p.email,
                    f.cargo,
                    f.turno
                FROM funcionario f
                LEFT JOIN pessoa p ON f.id_pessoa = p.id_pessoa
                ORDER BY p.nome IS NULL, p.nome ASC
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ['sucesso' => true, 'dados' => $funcionarios];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro ao listar: ' . $e->getMessage()]];
        }
    }

    public function buscarPorId(int $id): array {
        try {
            $this->funcionario->setIdPessoa($id);
            $dados = $this->funcionario->readComplete();
            
            if ($dados) {
                return ['sucesso' => true, 'dados' => $dados];
            }
            return ['sucesso' => false, 'erros' => ['Funcionario nao encontrado.']];
        } catch (Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    public function atualizar(int $id, array $dados): array {
        try {
            $this->db->beginTransaction();

            $this->pessoa->setId($id);
            if (!$this->pessoa->readOne()) {
                $this->db->rollBack();
                return ['sucesso' => false, 'erros' => ['Pessoa nao encontrada.']];
            }

            $this->pessoa->setNome($dados['nome']);
            $this->pessoa->setSexo($dados['sexo'] ?? null);
            $this->pessoa->setDataNascimento($dados['data_nascimento'] ?? null);
            $this->pessoa->setDocumento($dados['documento'] ?? null);
            $this->pessoa->setTelefone($dados['telefone'] ?? null);
            $this->pessoa->setEmail($dados['email'] ?? null);

            if (!$this->pessoa->update()) {
                $this->db->rollBack();
                return ['sucesso' => false, 'erros' => ['Erro ao atualizar pessoa.']];
            }

            $this->funcionario->setIdPessoa($id);
            $this->funcionario->setCargo($dados['cargo'] ?? null);
            
            // Converter salário formatado para float
            $salario = null;
            if (!empty($dados['salario'])) {
                $salario = str_replace(['R$ ', '.', ','], ['', '', '.'], $dados['salario']);
                $salario = floatval($salario);
            }
            $this->funcionario->setSalario($salario);
            
            $this->funcionario->setDataContratacao($dados['data_contratacao']);
            
            // Converter CTPS: pega apenas os primeiros 7 dígitos (antes da barra)
            $ctps = null;
            if (!empty($dados['numero_ctps'])) {
                // Remove tudo que não é número
                $ctps = preg_replace('/\D/', '', $dados['numero_ctps']);
                // Pega apenas os primeiros 7 dígitos
                $ctps = substr($ctps, 0, 7);
                $ctps = !empty($ctps) ? (int)$ctps : null;
            }
            $this->funcionario->setNumeroCtps($ctps);
            
            $this->funcionario->setTurno($dados['turno'] ?? null);

            if (!$this->funcionario->update()) {
                $this->db->rollBack();
                return ['sucesso' => false, 'erros' => ['Erro ao atualizar funcionario.']];
            }

            $this->db->commit();
            return ['sucesso' => true, 'mensagem' => 'Funcionario atualizado!'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['sucesso' => false, 'erros' => ['Erro: ' . $e->getMessage()]];
        }
    }

    /**
     * Verifica se o funcionário tem reservas vinculadas
     */
    private function temReservasVinculadas(int $id): bool {
        try {
            $query = "SELECT COUNT(*) as total FROM reserva WHERE id_funcionario = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $resultado['total'] > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function deletar(int $id): array {
        try {
            // Verificar se o funcionário tem reservas vinculadas
            if ($this->temReservasVinculadas($id)) {
                return [
                    'sucesso' => false, 
                    'erros' => [
                        'Não é possível excluir este funcionário pois ele possui reservas vinculadas.',
                        'Ou primeiro remova/transfira todas as reservas vinculadas a este funcionário.'
                    ],
                    'tem_vinculo' => true
                ];
            }

            $this->db->beginTransaction();

            $this->funcionario->setIdPessoa($id);
            if (!$this->funcionario->delete()) {
                $this->db->rollBack();
                return ['sucesso' => false, 'erros' => ['Erro ao excluir funcionario.']];
            }

            $this->pessoa->setId($id);
            if (!$this->pessoa->delete()) {
                $this->db->rollBack();
                return ['sucesso' => false, 'erros' => ['Erro ao excluir pessoa.']];
            }

            $this->db->commit();
            return ['sucesso' => true, 'mensagem' => 'Funcionario excluído com sucesso!'];
        } catch (Exception $e) {
            $this->db->rollBack();
            
            // Detectar erro de chave estrangeira
            if (strpos($e->getMessage(), '1451') !== false || strpos($e->getMessage(), 'foreign key constraint') !== false) {
                return [
                    'sucesso' => false, 
                    'erros' => [
                        'Não é possível excluir este funcionário pois ele possui vínculos no sistema.',
                        'Verifique se existem reservas ou outros registros associados a este funcionário.'
                    ],
                    'tem_vinculo' => true
                ];
            }
            
            return ['sucesso' => false, 'erros' => ['Erro ao excluir: ' . $e->getMessage()]];
        }
    }

    public function pesquisar(string $termo): array {
        try {
            // Pesquisa por nome, cpf ou cargo, mesmo que só exista na tabela funcionario
            $sql = "
                SELECT 
                    f.id_pessoa as id,
                    p.nome,
                    p.documento as cpf,
                    p.email,
                    f.cargo,
                    f.turno
                FROM funcionario f
                LEFT JOIN pessoa p ON f.id_pessoa = p.id_pessoa
                WHERE 
                    (p.nome LIKE :nome OR f.cargo LIKE :cargo OR p.documento LIKE :cpf)
                ORDER BY p.nome IS NULL, p.nome ASC
            ";
            $like = '%' . $termo . '%';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':nome', $like);
            $stmt->bindValue(':cargo', $like);
            $stmt->bindValue(':cpf', $like);
            $stmt->execute();
            $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return ['sucesso' => true, 'dados' => $funcionarios];
        } catch (\Exception $e) {
            return ['sucesso' => false, 'erros' => ['Erro ao pesquisar funcionários: ' . $e->getMessage()]];
        }
    }
}
?>