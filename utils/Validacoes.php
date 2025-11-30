<?php 

/**
 *validacoes para dados gerais do hotel
 */

 class Validacoes {
     
    // VALIDA EMAIL
    public static function validarEmail(string $email): bool 
    {
         return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
     }

     // VALIDA TELEFONE (APENAS NÚMEROS, COM 10 OU 11 DÍGITOS)
     public static function validarTelefone(string $telefone): bool 
     {
        $numeros = preg_replace('/[^0-9]/', '', $telefone);
        return strlen($numeros) >= 10 && strlen($numeros) <= 11;     
    }

    // VALIDA DATA NO FORMATO YYYY-MM-DD
    public static function validarData(string $data): bool 
    {
        $d = DateTime::createFromFormat('Y-m-d', $data);
        return $d && $d->format('Y-m-d') === $data;
    }

     // VALIDA NOME (APENAS LETRAS E ESPAÇOS, COM TAMANHO MÍNIMO E MÁXIMO)
    public static function validarNome(string $nome, int $min = 3, int $max = 100): bool 
    {
        $tamanho = strlen($nome);
        if ($tamanho < $min || $tamanho > $max) {
            return false;
        }
        return preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $nome) === 1;
    }
    
    
    // VALIDA CPF (FORMATO: 000.000.000-00 OU 00000000000)
    public static function validarCPF(string $cpf): bool 
    {

        // remove caracters nao numericos
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        
        // verifica se tem 11 digitos
        if (strlen($cpf) != 11) {
            return false;
        }
        // verifica se todos os digitos sao iguais
        if (preg_match('/^(\\d)\\1{10}$/', $cpf)) {
            return false;
        }

        // calcula o primeiro digito verificador
        $soma = 0;
        for ($i = 0; $i < 9; $i++) {
            $soma += $cpf[$i] * (10 - $i);
        }
        $resto = ($soma * 10) % 11;
        $digito1 = ($resto === 10) ? 0 : $resto;
        
        if ($cpf[9] != $digito1) {
            return false;
        }

        // calcula o segundo digito verificador
        $soma = 0;
        for ($i = 0; $i < 10; $i++) {
            $soma += $cpf[$i] * (11 - $i);
        }
        $resto = ($soma * 10) % 11;
        $digito2 = ($resto === 10) ? 0 : $resto;

        return $cpf[10] == $digito2;

    }

    // VALIDA DATA DE NASCIMENTO (MAIOR DE 18 ANOS)
    public static function validarDataNascimento(
        string $data,
        int $idadeMinima = 18,
        int $idadeMaxima = 121
    ): bool {
        $dataNascimento = DateTime::createFromFormat('Y-m-d', $data);

        if(!$dataNascimento || $dataNascimento->format('Y-m-d') !== $data) {
            return false;
        }

        $hoje = new DateTime();
        $idade = $hoje->diff($dataNascimento)->y;

        return $idade >= $idadeMinima && $idade <= $idadeMaxima;
    }

    // SANITIZAR - LIMPAR E FILTRAR DADOS
    public static function validarNumeroQuarto(string $numeroQuarto): bool 
    {
        return preg_match("/^[A-Za-z0-9\-]+$/", $numeroQuarto) === 1;
 
    }

    public static function validarTexto(string $texto, int $min = 1, int $max = 255): bool {
    $texto = trim($texto);
    $len = strlen($texto);

    if ($len < $min || $len > $max) {
        return false;
    }

    return preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $texto) === 1;
    }
    
}
?>