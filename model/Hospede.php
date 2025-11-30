<?php

require_once __DIR__ . '/../utils/Validacoes.php';
require_once __DIR__ . '/../utils/Formatter.php';

class Hospede {
    private $conn;
    private string $table_name = "pessoa";
    
    private ?int    $id_pessoa = null;
    private ?string $nome = null;
    private ?string $sexo = null;
    private ?string $data_nascimento = null;
    private ?string $documento = null;
    private ?string $telefone = null;
    private ?string $email = null;
    private ?int    $endereco_id_endereco = null;
    private ?string $preferencias = null;
    private ?string $historico = null;

    public function __construct($db) {
        $this->conn = $db;
    }

    // GETTERS / SETTERS
    public function setId(int $id): void { $this->id_pessoa = $id; }
    public function getId(): ?int { return $this->id_pessoa; }

    public function setNome(string $nome): void { $this->nome = $nome; }
    public function getNome(): ?string { return $this->nome; }

    public function setSexo(string $sexo): void { $this->sexo = $sexo; }
    public function getSexo(): ?string { return $this->sexo; }

    public function setDataNascimento(string $data): void { $this->data_nascimento = $data; }
    public function getDataNascimento(): ?string { return $this->data_nascimento; }

    public function setDocumento(string $documento): void { $this->documento = $documento; }
    public function getDocumento(): ?string { return $this->documento; }

    public function setTelefone(string $telefone): void { $this->telefone = $telefone; }
    public function getTelefone(): ?string { return $this->telefone; }

    public function setEmail(string $email): void { $this->email = $email; }
    public function getEmail(): ?string { return $this->email; }

    public function setEndereco(int $endereco_id): void { $this->endereco_id_endereco = $endereco_id; }
    public function getEndereco(): ?int { return $this->endereco_id_endereco; }

    public function setEnderecoId(int $endereco_id): void { $this->endereco_id_endereco = $endereco_id; }
    public function getEnderecoId(): ?int { return $this->endereco_id_endereco; }

    public function setPreferencias(string $preferencias): void { $this->preferencias = $preferencias; }
    public function getPreferencias(): ?string { return $this->preferencias; }

    public function setHistorico(string $historico): void { $this->historico = $historico; }
    public function getHistorico(): ?string { return $this->historico; }

    // CREATE - Insere na tabela pessoa
    public function create(): bool {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nome, sexo, data_nascimento, documento, telefone, email, tipo_pessoa, endereco_id_endereco) 
                  VALUES (:nome, :sexo, :data_nascimento, :documento, :telefone, :email, :tipo_pessoa, :endereco_id_endereco)";

        $stmt = $this->conn->prepare($query);

        $tipo_pessoa = 'hospede';

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":sexo", $this->sexo);
        $stmt->bindParam(":data_nascimento", $this->data_nascimento);
        $stmt->bindParam(":documento", $this->documento);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":tipo_pessoa", $tipo_pessoa);
        $stmt->bindParam(":endereco_id_endereco", $this->endereco_id_endereco, \PDO::PARAM_INT);

        if($stmt->execute()) {
            // Pega o ID da pessoa inserida
            $this->id_pessoa = $this->conn->lastInsertId();
            
            // Agora insere na tabela hospede
            $query_hospede = "INSERT INTO hospede (id_pessoa, preferencias, historico) 
                             VALUES (:id_pessoa, :preferencias, :historico)";
            $stmt_hospede = $this->conn->prepare($query_hospede);
            $stmt_hospede->bindParam(":id_pessoa", $this->id_pessoa, \PDO::PARAM_INT);
            $stmt_hospede->bindParam(":preferencias", $this->preferencias);
            $stmt_hospede->bindParam(":historico", $this->historico);
            
            return $stmt_hospede->execute();
        }
        return false;
    }

    // READ ALL
    public function read(): \PDOStatement {
        $query = "SELECT p.*, h.preferencias, h.historico 
                  FROM " . $this->table_name . " p
                  LEFT JOIN hospede h ON p.id_pessoa = h.id_pessoa
                  WHERE p.tipo_pessoa = 'hospede'
                  ORDER BY p.nome ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ ONE
    public function readOne(): bool {
        $query = "SELECT p.*, h.preferencias, h.historico 
                  FROM " . $this->table_name . " p
                  LEFT JOIN hospede h ON p.id_pessoa = h.id_pessoa
                  WHERE p.id_pessoa = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id_pessoa, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($row){
            $this->nome = $row['nome'];
            $this->sexo = $row['sexo'];
            $this->data_nascimento = $row['data_nascimento'];
            $this->documento = $row['documento'];
            $this->telefone = $row['telefone'];
            $this->email = $row['email'];
            $this->endereco_id_endereco = (int)$row['endereco_id_endereco'];
            $this->preferencias = $row['preferencias'];
            $this->historico = $row['historico'];
            return true;
        }
        return false;
    }

    // UPDATE
    public function update(): bool {
        $query = "UPDATE " . $this->table_name . "
                  SET nome = :nome,
                      sexo = :sexo,
                      data_nascimento = :data_nascimento,
                      documento = :documento,
                      telefone = :telefone,
                      email = :email,
                      endereco_id_endereco = :endereco_id_endereco
                  WHERE id_pessoa = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $this->nome);
        $stmt->bindParam(":sexo", $this->sexo);
        $stmt->bindParam(":data_nascimento", $this->data_nascimento);
        $stmt->bindParam(":documento", $this->documento);
        $stmt->bindParam(":telefone", $this->telefone);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":endereco_id_endereco", $this->endereco_id_endereco, \PDO::PARAM_INT);
        $stmt->bindParam(":id", $this->id_pessoa, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    // DELETE
    public function delete(): bool {
        // Primeiro deleta da tabela hospede
        $query_hospede = "DELETE FROM hospede WHERE id_pessoa = :id";
        $stmt_hospede = $this->conn->prepare($query_hospede);
        $stmt_hospede->bindParam(":id", $this->id_pessoa, \PDO::PARAM_INT);
        $stmt_hospede->execute();

        // Depois deleta de pessoa
        $query = "DELETE FROM " . $this->table_name . " WHERE id_pessoa = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id_pessoa, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toArray(): array {
        return [
            'id_pessoa' => $this->id_pessoa,
            'nome' => $this->nome,
            'sexo' => $this->sexo,
            'data_nascimento' => $this->data_nascimento,
            'documento' => $this->documento,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'preferencias' => $this->preferencias,
            'historico' => $this->historico
        ];
    }
}
?>