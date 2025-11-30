<?php
class Endereço {
    private $rua;
    private $numero;
    private $cidade;
    private $estado;
    private $cep;

    public function __construct($rua, $numero, $cidade, $estado, $cep) {
        $this->rua = $rua;
        $this->numero = $numero;
        $this->cidade = $cidade;
        $this->estado = $estado;
        $this->cep = $cep;
    }

    public function getRua() {
        return $this->rua;
    }

    public function getNumero() {
        return $this->numero;
    }

    public function getCidade() {
        return $this->cidade;
    }

    public function getEstado() {
        return $this->estado;
    }

    public function getCep() {
        return $this->cep;
    }
    public function criar(array $dados): array {
    try {
        // 1. PRIMEIRO: Criar o endereço
        $endereco = new Endereco($this->db);
        $endereco->setRua($dados['rua'] ?? '');
        $endereco->setCidade($dados['cidade'] ?? '');
        $endereco->setEstado($dados['estado'] ?? '');
        $endereco->setCep($dados['cep'] ?? '');
        $endereco->setNumero($dados['numero'] ?? '');
        $endereco->setComplemento($dados['complemento'] ?? null);
        
        // Validar e criar endereço
        $errosEndereco = $endereco->validar();
        if (!empty($errosEndereco)) {
            return ['sucesso' => false, 'erros' => $errosEndereco];
        }
        
        $endereco_id = $endereco->create(); // Retorna o ID do endereço criado
        
        if (!$endereco_id) {
            return ['sucesso' => false, 'erros' => ['Erro ao criar endereço.']];
        }

        // 2. DEPOIS: Criar o hóspede com o ID do endereço
        $this->hospede->setNome($dados['nome']);
        $this->hospede->setCpf($dados['cpf']);
        $this->hospede->setEmail($dados['email']);
        $this->hospede->setTelefone($dados['telefone']);
        $this->hospede->setEnderecoId($endereco_id); // Passar o ID, não a string
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
}

?>