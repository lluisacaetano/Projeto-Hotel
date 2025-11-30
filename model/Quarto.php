<?php

require_once __DIR__ . '/../utils/Validacoes.php';
require_once __DIR__ . '/../utils/Formatter.php';

class Quarto {
    private $conn;
    private string $table_name = "quarto";

    private ?int    $id_quarto = null;
    private int     $numero;
    private int     $andar;
    private string  $tipo_quarto;
    private float   $valor_diaria;
    private int     $capacidade_maxima;
    private ?string $descricao = null;
    private string  $status = 'disponivel';

    private const TIPOS_VALIDOS = ['Standard', 'Luxo', 'Suite'];
    private const STATUS_VALIDOS = ['disponivel', 'ocupado', 'manutencao'];

    public function __construct($db){
        $this->conn = $db;
    }

    // GETTERS / SETTERS
    public function setId(int $id): void { $this->id_quarto = $id; }
    public function getId(): ?int { return $this->id_quarto; }

    public function setNumero(int $numero): void { $this->numero = $numero; }
    public function getNumero(): int { return $this->numero; }

    public function setAndar(int $andar): void { $this->andar = $andar; }
    public function getAndar(): int { return $this->andar; }

    public function setTipo(string $tipo): void { $this->tipo_quarto = $tipo; }
    public function getTipo(): string { return $this->tipo_quarto; }

    public function setValorDiaria(float $valor): void { $this->valor_diaria = $valor; }
    public function getValorDiaria(): float { return $this->valor_diaria; }

    public function setCapacidade(int $capacidade): void { $this->capacidade_maxima = $capacidade; }
    public function getCapacidade(): int { return $this->capacidade_maxima; }

    public function setDescricao(?string $descricao): void { $this->descricao = $descricao; }
    public function getDescricao(): ?string { return $this->descricao; }

    public function setStatus(string $status): void { $this->status = $status; }
    public function getStatus(): string { return $this->status; }

    // CREATE
    public function create(): bool{
        $query = "INSERT INTO " . $this->table_name . " 
                  (numero, andar, tipo_quarto, valor_diaria, capacidade_maxima, descricao, status) 
                  VALUES (:numero, :andar, :tipo_quarto, :valor_diaria, :capacidade_maxima, :descricao, :status)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":numero", $this->numero, \PDO::PARAM_INT);
        $stmt->bindParam(":andar", $this->andar, \PDO::PARAM_INT);
        $stmt->bindParam(":tipo_quarto", $this->tipo_quarto);
        $stmt->bindParam(":valor_diaria", $this->valor_diaria);
        $stmt->bindParam(":capacidade_maxima", $this->capacidade_maxima, \PDO::PARAM_INT);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":status", $this->status);

        return $stmt->execute();
    }

    // READ ALL
    public function read(): \PDOStatement {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY numero ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ ONE
    public function readOne(): bool {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_quarto = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id_quarto, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($row){
            $this->numero = (int)$row['numero'];
            $this->andar = (int)$row['andar'];
            $this->tipo_quarto = $row['tipo_quarto'];
            $this->valor_diaria = (float)$row['valor_diaria'];
            $this->capacidade_maxima = (int)$row['capacidade_maxima'];
            $this->descricao = $row['descricao'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // UPDATE
    public function update(): bool {
        $query = "UPDATE " . $this->table_name . "
                  SET numero = :numero,
                      andar = :andar,
                      tipo_quarto = :tipo_quarto,
                      valor_diaria = :valor_diaria,
                      capacidade_maxima = :capacidade_maxima,
                      descricao = :descricao,
                      status = :status
                  WHERE id_quarto = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":numero", $this->numero, \PDO::PARAM_INT);
        $stmt->bindParam(":andar", $this->andar, \PDO::PARAM_INT);
        $stmt->bindParam(":tipo_quarto", $this->tipo_quarto);
        $stmt->bindParam(":valor_diaria", $this->valor_diaria);
        $stmt->bindParam(":capacidade_maxima", $this->capacidade_maxima, \PDO::PARAM_INT);
        $stmt->bindParam(":descricao", $this->descricao);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id_quarto, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    // DELETE
    public function delete(): bool {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_quarto = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id_quarto, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function toArray(): array {
        return [
            'id_quarto' => $this->id_quarto,
            'numero' => $this->numero,
            'andar' => $this->andar,
            'tipo_quarto' => $this->tipo_quarto,
            'valor_diaria' => $this->valor_diaria,
            'capacidade_maxima' => $this->capacidade_maxima,
            'descricao' => $this->descricao,
            'status' => $this->status
        ];
    }

    public static function getTiposValidos(): array { return self::TIPOS_VALIDOS; }
    public static function getStatusValidos(): array { return self::STATUS_VALIDOS; }
}
?>