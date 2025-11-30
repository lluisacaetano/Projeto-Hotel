<?php

require_once __DIR__ . '/../utils/Validacoes.php';
require_once __DIR__ . '/../utils/Formatter.php';

class Funcionario {
    private $conn;
    private string $table_name = "funcionario";

    private ?int    $id = null;
    private string  $nome;
    private string  $cpf;
    private string  $cargo;
    private string  $email;
    private string  $telefone;
    private float   $salario;
    private string  $data_admissao;
    private string  $status = 'ativo';
    private ?string $created_at = null;

    private const NOME_MIN = 3;
    private const NOME_MAX = 100;
    private const CARGO_MIN = 3;
    private const CARGO_MAX = 50;
    private const SALARIO_MIN = 1412.00; // Salário mínimo brasileiro (2024)
    private const SALARIO_MAX = 999999.99;
    private const STATUS_VALIDOS = ['ativo', 'inativo', 'ferias', 'afastado'];

    public function __construct($db){
        $this->conn = $db;
    }

    // GETTERS / SETTERS
    public function setId(int $id): void { $this->id = $id; }
    public function getId(): ?int { return $this->id; }

    public function setNome(string $nome): void { $this->nome = $nome; }
    public function getNome(): string { return $this->nome; }

    public function setCpf(string $cpf): void { $this->cpf = $cpf; }
    public function getCpf(): string { return $this->cpf; }
    public function getCpfFormatado(): string { return Formatter::formatarCPF($this->cpf); }

    public function setCargo(string $cargo): void { $this->cargo = $cargo; }
    public function getCargo(): string { return $this->cargo; }

    public function setEmail(string $email): void { $this->email = $email; }
    public function getEmail(): string { return $this->email; }

    public function setTelefone(string $telefone): void { $this->telefone = $telefone; }
    public function getTelefone(): string { return $this->telefone; }
    public function getTelefoneFormatado(): string { return Formatter::formatarTelefone($this->telefone); }

    public function setSalario(float $salario): void { $this->salario = $salario; }
    public function getSalario(): float { return $this->salario; }
    public function getSalarioFormatado(): string { return Formatter::formatarMoeda($this->salario); }

    public function setDataAdmissao(string $data): void { $this->data_admissao = $data; }
    public function getDataAdmissao(): string { return $this->data_admissao; }
    public function getDataAdmissaoFormatada(): string { return Formatter::formatarData($this->data_admissao); }

    public function setStatus(string $status): void { $this->status = $status; }
    public function getStatus(): string { return $this->status; }
    public function getStatusFormatado(): string { return Formatter::formatarStatusBadge($this->status); }

    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getCreatedAtFormatado(): ?string {
        return $this->created_at ? Formatter::formatarData($this->created_at) : null;
    }

    // CREATE
    public function create(): bool {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome, cpf, cargo, email, telefone, salario, data_admissao, status, data_criacao) 
                  VALUES (:nome, :cpf, :cargo, :email, :telefone, :salario, :data_admissao, :status, :data_criacao)";

        $stmt = $this->conn->prepare($query);

        $data_criacao = $this->created_at ?? date('Y-m-d H:i:s');

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":cpf", $this->cpf);
        $stmt->bindParam(":cargo", $this->cargo);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":salario", $this->salario);
        $stmt->bindParam(":data_admissao", $this->data_admissao);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":data_criacao", $data_criacao);

        return $stmt->execute();
    }

    // READ ALL
    public function read(): \PDOStatement {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nome ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ ONE
    public function readOne(): bool {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($row){
            $this->nome =           $row['nome'];
            $this->cpf =            $row['cpf'];
            $this->cargo =          $row['cargo'];
            $this->email =          $row['email'];
            $this->telefone =       $row['telefone'];
            $this->salario =        (float)$row['salario'];
            $this->data_admissao =  $row['data_admissao'];
            $this->status =         $row['status'];
            $this->created_at =     $row['data_criacao'] ?? null;
            return true;
        }
        return false;
    }

    // UPDATE
    public function update(): bool {
        $query = "UPDATE " . $this->table_name . "
                  SET nome = :nome,
                      cpf = :cpf,
                      cargo = :cargo,
                      email = :email,
                      telefone = :telefone,
                      salario = :salario,
                      data_admissao = :data_admissao,
                      status = :status
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":cpf", $this->cpf);
        $stmt->bindParam(":cargo", $this->cargo);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":salario", $this->salario);
        $stmt->bindParam(":data_admissao", $this->data_admissao);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    // DELETE
    public function delete(): bool {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Listar funcionários por cargo
    public function listarPorCargo(string $cargo): array {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE cargo = :cargo 
                  ORDER BY nome ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cargo", $cargo);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Listar funcionários por status
    public function listarPorStatus(string $status): array {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE status = :status 
                  ORDER BY nome ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Buscar funcionário por CPF
    public function buscarPorCpf(string $cpf): bool {
        $query = "SELECT * FROM " . $this->table_name . " WHERE cpf = :cpf LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":cpf", $cpf);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($row){
            $this->id =             (int)$row['id'];
            $this->nome =           $row['nome'];
            $this->cpf =            $row['cpf'];
            $this->cargo =          $row['cargo'];
            $this->email =          $row['email'];
            $this->telefone =       $row['telefone'];
            $this->salario =        (float)$row['salario'];
            $this->data_admissao =  $row['data_admissao'];
            $this->status =         $row['status'];
            $this->created_at =     $row['data_criacao'] ?? null;
            return true;
        }
        return false;
    }

    // Calcular tempo de empresa (em anos)
    public function calcularTempoEmpresa(): int {
        $data_admissao = new DateTime($this->data_admissao);
        $hoje = new DateTime();
        $diferenca = $hoje->diff($data_admissao);
        return $diferenca->y;
    }

    // Validar dados
    public function validar(): array {
        $erros = [];

        if(empty($this->nome)) {
            $erros[] = "Nome é obrigatório.";
        } elseif (!Validacoes::validarTexto($this->nome, self::NOME_MIN, self::NOME_MAX)){
            $erros[] = "Nome deve ter entre " . self::NOME_MIN . " e " . self::NOME_MAX . " caracteres.";
        }

        if(empty($this->cpf)) {
            $erros[] = "CPF é obrigatório.";
        } elseif (!Validacoes::validarCPF($this->cpf)){
            $erros[] = "CPF inválido.";
        }

        if(empty($this->cargo)) {
            $erros[] = "Cargo é obrigatório.";
        } elseif (!Validacoes::validarTexto($this->cargo, self::CARGO_MIN, self::CARGO_MAX)){
            $erros[] = "Cargo deve ter entre " . self::CARGO_MIN . " e " . self::CARGO_MAX . " caracteres.";
        }

        if(empty($this->email)) {
            $erros[] = "E-mail é obrigatório.";
        } elseif (!Validacoes::validarEmail($this->email)){
            $erros[] = "E-mail inválido.";
        }

        if(empty($this->telefone)) {
            $erros[] = "Telefone é obrigatório.";
        } elseif (!Validacoes::validarTelefone($this->telefone)){
            $erros[] = "Telefone inválido.";
        }

        if (!Validacoes::validarValorMonetario($this->salario, self::SALARIO_MIN, self::SALARIO_MAX)) {
            $erros[] = "Salário deve estar entre " . 
                      Formatter::formatarMoeda(self::SALARIO_MIN) . " e " . 
                      Formatter::formatarMoeda(self::SALARIO_MAX);
        }

        if(empty($this->data_admissao)) {
            $erros[] = "Data de admissão é obrigatória.";
        } elseif (!Validacoes::validarData($this->data_admissao)){
            $erros[] = "Data de admissão inválida.";
        }

        if (!Validacoes::validarEnum($this->status, self::STATUS_VALIDOS)) {
            $erros[] = "Status inválido. Opções: " . implode(', ', self::STATUS_VALIDOS);
        }

        return $erros;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'cpf_formatado' => $this->getCpfFormatado(),
            'cargo' => $this->cargo,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'telefone_formatado' => $this->getTelefoneFormatado(),
            'salario' => $this->salario,
            'salario_formatado' => $this->getSalarioFormatado(),
            'data_admissao' => $this->data_admissao,
            'data_admissao_formatada' => $this->getDataAdmissaoFormatada(),
            'status' => $this->status,
            'status_formatado' => $this->getStatusFormatado(),
            'created_at' => $this->created_at,
            'created_at_formatado' => $this->getCreatedAtFormatado()
        ];
    }

    public static function getStatusValidos(): array { 
        return self::STATUS_VALIDOS; 
    }
}
?>