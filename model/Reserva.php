<?php

require_once __DIR__ . '/../utils/Validacoes.php';
require_once __DIR__ . '/../utils/Formatter.php';

class Reserva {
    private $conn;
    private string $table_name = "reservas";

    private ?int    $id = null;
    private int     $hospede_id;
    private int     $quarto_id;
    private int     $funcionario_id;
    private string  $data_checkin;
    private string  $data_checkout;
    private int     $num_hospedes;
    private float   $valor_total;
    private string  $status = 'pendente';
    private ?string $observacoes = null;
    private ?string $created_at = null;

    private const STATUS_VALIDOS = ['pendente', 'confirmada', 'cancelada', 'concluida'];
    private const NUM_HOSPEDES_MIN = 1;
    private const NUM_HOSPEDES_MAX = 10;
    private const OBSERVACOES_MAX = 500;

    public function __construct($db){
        $this->conn = $db;
    }

    // GETTERS / SETTERS
    public function setId(int $id): void { $this->id = $id; }
    public function getId(): ?int { return $this->id; }

    public function setHospedeId(int $hospede_id): void { $this->hospede_id = $hospede_id; }
    public function getHospedeId(): int { return $this->hospede_id; }

    public function setQuartoId(int $quarto_id): void { $this->quarto_id = $quarto_id; }
    public function getQuartoId(): int { return $this->quarto_id; }

    public function setFuncionarioId(int $funcionario_id): void { $this->funcionario_id = $funcionario_id; }
    public function getFuncionarioId(): int { return $this->funcionario_id; }

    public function setDataCheckin(string $data): void { $this->data_checkin = $data; }
    public function getDataCheckin(): string { return $this->data_checkin; }
    public function getDataCheckinFormatada(): string { return Formatter::formatarData($this->data_checkin); }

    public function setDataCheckout(string $data): void { $this->data_checkout = $data; }
    public function getDataCheckout(): string { return $this->data_checkout; }
    public function getDataCheckoutFormatada(): string { return Formatter::formatarData($this->data_checkout); }

    public function setNumHospedes(int $num): void { $this->num_hospedes = $num; }
    public function getNumHospedes(): int { return $this->num_hospedes; }

    public function setValorTotal(float $valor): void { $this->valor_total = $valor; }
    public function getValorTotal(): float { return $this->valor_total; }
    public function getValorTotalFormatado(): string { return Formatter::formatarMoeda($this->valor_total); }

    public function setStatus(string $status): void { $this->status = $status; }
    public function getStatus(): string { return $this->status; }
    public function getStatusFormatado(): string { return Formatter::formatarStatusBadge($this->status); }

    public function setObservacoes(?string $obs): void { $this->observacoes = $obs; }
    public function getObservacoes(): ?string { return $this->observacoes; }

    public function getCreatedAt(): ?string { return $this->created_at; }
    public function getCreatedAtFormatado(): ?string {
        return $this->created_at ? Formatter::formatarData($this->created_at) : null;
    }

    // CREATE
    public function create(): bool {
        $query = "INSERT INTO " . $this->table_name . " 
                  (hospede_id, quarto_id, funcionario_id, data_checkin, data_checkout, 
                   num_hospedes, valor_total, status, observacoes, data_criacao) 
                  VALUES (:hospede_id, :quarto_id, :funcionario_id, :data_checkin, :data_checkout,
                          :num_hospedes, :valor_total, :status, :observacoes, :data_criacao)";

        $stmt = $this->conn->prepare($query);

        $data_criacao = $this->created_at ?? date('Y-m-d H:i:s');

        $stmt->bindParam(":hospede_id", $this->hospede_id, \PDO::PARAM_INT);
        $stmt->bindParam(":quarto_id", $this->quarto_id, \PDO::PARAM_INT);
        $stmt->bindParam(":funcionario_id", $this->funcionario_id, \PDO::PARAM_INT);
        $stmt->bindParam(":data_checkin", $this->data_checkin);
        $stmt->bindParam(":data_checkout", $this->data_checkout);
        $stmt->bindParam(":num_hospedes", $this->num_hospedes, \PDO::PARAM_INT);
        $stmt->bindParam(":valor_total", $this->valor_total);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":observacoes", $this->observacoes);
        $stmt->bindParam(":data_criacao", $data_criacao);

        return $stmt->execute();
    }

    // READ ALL
    public function read(): \PDOStatement {
        $query = "SELECT r.*, 
                         h.nome as hospede_nome, h.cpf as hospede_cpf,
                         q.numero_quarto, q.tipo as quarto_tipo,
                         f.nome as funcionario_nome
                  FROM " . $this->table_name . " r
                  INNER JOIN hospede h ON r.hospede_id = h.id
                  INNER JOIN quarto q ON r.quarto_id = q.id
                  INNER JOIN funcionario f ON r.funcionario_id = f.id
                  ORDER BY r.data_checkin DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // READ ONE
    public function readOne(): bool {
        $query = "SELECT r.*,
                         h.nome as hospede_nome, h.cpf as hospede_cpf, h.email as hospede_email,
                         q.numero_quarto, q.tipo as quarto_tipo, q.preco_diaria,
                         f.nome as funcionario_nome
                  FROM " . $this->table_name . " r
                  INNER JOIN hospede h ON r.hospede_id = h.id
                  INNER JOIN quarto q ON r.quarto_id = q.id
                  INNER JOIN funcionario f ON r.funcionario_id = f.id
                  WHERE r.id = :id 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if($row){
            $this->hospede_id =      (int)$row['hospede_id'];
            $this->quarto_id =       (int)$row['quarto_id'];
            $this->funcionario_id =  (int)$row['funcionario_id'];
            $this->data_checkin =    $row['data_checkin'];
            $this->data_checkout =   $row['data_checkout'];
            $this->num_hospedes =    (int)$row['num_hospedes'];
            $this->valor_total =     (float)$row['valor_total'];
            $this->status =          $row['status'];
            $this->observacoes =     $row['observacoes'];
            $this->created_at =      $row['data_criacao'] ?? null;
            return true;
        }
        return false;
    }

    // UPDATE
    public function update(): bool {
        $query = "UPDATE " . $this->table_name . "
                  SET hospede_id = :hospede_id,
                      quarto_id = :quarto_id,
                      funcionario_id = :funcionario_id,
                      data_checkin = :data_checkin,
                      data_checkout = :data_checkout,
                      num_hospedes = :num_hospedes,
                      valor_total = :valor_total,
                      status = :status,
                      observacoes = :observacoes
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":hospede_id", $this->hospede_id, \PDO::PARAM_INT);
        $stmt->bindParam(":quarto_id", $this->quarto_id, \PDO::PARAM_INT);
        $stmt->bindParam(":funcionario_id", $this->funcionario_id, \PDO::PARAM_INT);
        $stmt->bindParam(":data_checkin", $this->data_checkin);
        $stmt->bindParam(":data_checkout", $this->data_checkout);
        $stmt->bindParam(":num_hospedes", $this->num_hospedes, \PDO::PARAM_INT);
        $stmt->bindParam(":valor_total", $this->valor_total);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":observacoes", $this->observacoes);
        $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);

        return $stmt->execute();
    }

    // DELETE
    public function delete(): bool {
        // Só permite excluir reservas canceladas ou pendentes
        if(!in_array($this->status, ['cancelada', 'pendente'])){
            return false;
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Cancelar reserva
    public function cancelar(): bool {
        $this->status = 'cancelada';
        $query = "UPDATE " . $this->table_name . " SET status = 'cancelada' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Confirmar reserva
    public function confirmar(): bool {
        $this->status = 'confirmada';
        $query = "UPDATE " . $this->table_name . " SET status = 'confirmada' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Concluir reserva (checkout realizado)
    public function concluir(): bool {
        $this->status = 'concluida';
        $query = "UPDATE " . $this->table_name . " SET status = 'concluida' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Calcular valor total baseado no período e preço da diária
    public function calcularValorTotal(float $preco_diaria): float {
        $checkin = new DateTime($this->data_checkin);
        $checkout = new DateTime($this->data_checkout);
        $diferenca = $checkout->diff($checkin);
        $num_diarias = $diferenca->days;
        
        // Mínimo de 1 diária
        if($num_diarias < 1) $num_diarias = 1;
        
        return $num_diarias * $preco_diaria;
    }

    // Calcular número de diárias
    public function calcularNumDiarias(): int {
        $checkin = new DateTime($this->data_checkin);
        $checkout = new DateTime($this->data_checkout);
        $diferenca = $checkout->diff($checkin);
        return max(1, $diferenca->days);
    }

    // Verificar disponibilidade do quarto no período
    public function verificarDisponibilidade(): bool {
        $query = "SELECT COUNT(*) as total 
                  FROM " . $this->table_name . " 
                  WHERE quarto_id = :quarto_id 
                    AND status IN ('confirmada', 'pendente')
                    AND NOT (data_checkout < :data_checkin OR data_checkin > :data_checkout)";

        if($this->id !== null){
            $query .= " AND id != :id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":quarto_id", $this->quarto_id, \PDO::PARAM_INT);
        $stmt->bindParam(":data_checkin", $this->data_checkin);
        $stmt->bindParam(":data_checkout", $this->data_checkout);

        if($this->id !== null){
            $stmt->bindParam(":id", $this->id, \PDO::PARAM_INT);
        }

        $stmt->execute();
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return ($row && (int)$row['total'] === 0);
    }

    // Listar reservas por período
    public function listarPorPeriodo(string $data_inicio, string $data_fim): array {
        $query = "SELECT r.*, 
                         h.nome as hospede_nome,
                         q.numero_quarto, q.tipo as quarto_tipo
                  FROM " . $this->table_name . " r
                  INNER JOIN hospede h ON r.hospede_id = h.id
                  INNER JOIN quarto q ON r.quarto_id = q.id
                  WHERE r.data_checkin >= :data_inicio 
                    AND r.data_checkout <= :data_fim
                  ORDER BY r.data_checkin ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":data_inicio", $data_inicio);
        $stmt->bindParam(":data_fim", $data_fim);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Listar reservas por status
    public function listarPorStatus(string $status): array {
        $query = "SELECT r.*, 
                         h.nome as hospede_nome,
                         q.numero_quarto
                  FROM " . $this->table_name . " r
                  INNER JOIN hospede h ON r.hospede_id = h.id
                  INNER JOIN quarto q ON r.quarto_id = q.id
                  WHERE r.status = :status
                  ORDER BY r.data_checkin DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Validar dados
    public function validar(): array {
        $erros = [];

        if(empty($this->hospede_id)) {
            $erros[] = "Hóspede é obrigatório.";
        }

        if(empty($this->quarto_id)) {
            $erros[] = "Quarto é obrigatório.";
        }

        if(empty($this->funcionario_id)) {
            $erros[] = "Funcionário responsável é obrigatório.";
        }

        if(empty($this->data_checkin)) {
            $erros[] = "Data de check-in é obrigatória.";
        } elseif (!Validacoes::validarData($this->data_checkin)){
            $erros[] = "Data de check-in inválida.";
        }

        if(empty($this->data_checkout)) {
            $erros[] = "Data de check-out é obrigatória.";
        } elseif (!Validacoes::validarData($this->data_checkout)){
            $erros[] = "Data de check-out inválida.";
        }

        // Validar se checkout é após checkin
        if(!empty($this->data_checkin) && !empty($this->data_checkout)){
            if($this->data_checkout <= $this->data_checkin){
                $erros[] = "Data de check-out deve ser posterior à data de check-in.";
            }
        }

        if($this->num_hospedes < self::NUM_HOSPEDES_MIN || $this->num_hospedes > self::NUM_HOSPEDES_MAX){
            $erros[] = "Número de hóspedes deve estar entre " . self::NUM_HOSPEDES_MIN . " e " . self::NUM_HOSPEDES_MAX . ".";
        }

        if (!Validacoes::validarEnum($this->status, self::STATUS_VALIDOS)) {
            $erros[] = "Status inválido. Opções: " . implode(', ', self::STATUS_VALIDOS);
        }

        if ($this->observacoes !== null && !Validacoes::validarTexto($this->observacoes, 0, self::OBSERVACOES_MAX)) {
            $erros[] = "Observações muito longas (máximo " . self::OBSERVACOES_MAX . " caracteres).";
        }

        // Verificar disponibilidade do quarto
        if(!$this->verificarDisponibilidade()){
            $erros[] = "Quarto não está disponível no período selecionado.";
        }

        return $erros;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'hospede_id' => $this->hospede_id,
            'quarto_id' => $this->quarto_id,
            'funcionario_id' => $this->funcionario_id,
            'data_checkin' => $this->data_checkin,
            'data_checkin_formatada' => $this->getDataCheckinFormatada(),
            'data_checkout' => $this->data_checkout,
            'data_checkout_formatada' => $this->getDataCheckoutFormatada(),
            'num_hospedes' => $this->num_hospedes,
            'num_diarias' => $this->calcularNumDiarias(),
            'valor_total' => $this->valor_total,
            'valor_total_formatado' => $this->getValorTotalFormatado(),
            'status' => $this->status,
            'status_formatado' => $this->getStatusFormatado(),
            'observacoes' => $this->observacoes,
            'created_at' => $this->created_at,
            'created_at_formatado' => $this->getCreatedAtFormatado()
        ];
    }

    public static function getStatusValidos(): array { 
        return self::STATUS_VALIDOS; 
    }
}
?>