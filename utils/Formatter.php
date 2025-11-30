<?php

class Formatter {

    // FORMATA DATA PARA DD/MM/YYYY
    public static function formatarData(string $data): string 
    {
        $d = DateTime::createFromFormat('Y-m-d', $data);
        return $d ? $d->format('d/m/Y') : '';
    }
    
    // FORMATA MOEDA PARA PADRÃO BRASILEIRO
    public static function formatarMoeda(float $valor, bool $simbolo = true): string 
    {
         $formatado = number_format($valor, 2, ',', '.');
        return $simbolo ? "R$ {$formatado}" : $formatado;
    }

    // FORMATA CPF (000.000.000-00)
    public static function formatarCPF(string $cpf): string 
    {
        $numeros = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($numeros) != 11) {
            return $cpf; // retorna original se não tiver 11 dígitos
        }
        return substr($numeros, 0, 3) . '.' .
               substr($numeros, 3, 3) . '.' .
               substr($numeros, 6, 3) . '-' .
               substr($numeros, 9, 2);
    }

    // FORMATA TELEFONE (99) 99999-9999
    public static function formatarTelefone(string $telefone): string {
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        
        if (strlen($telefone) === 11) {
            // Celular: (11) 98765-4321
            return '(' . substr($telefone, 0, 2) . ') ' . 
                   substr($telefone, 2, 5) . '-' . 
                   substr($telefone, 7);
        } elseif (strlen($telefone) === 10) {
            // Fixo: (11) 3456-7890
            return '(' . substr($telefone, 0, 2) . ') ' . 
                   substr($telefone, 2, 4) . '-' . 
                   substr($telefone, 6);
        }
        
        return $telefone;
    }
 
    // FORMATA STATUS COM BADGE HTML
     public static function formatarStatusBadge(string $status): string {
        $classes = [
            'disponivel' => 'success',
            'ocupado' => 'danger',
            'manutencao' => 'warning',
            'confirmada' => 'success',
            'pendente' => 'warning',
            'cancelada' => 'danger',
            'concluida' => 'info'
        ];
        
        $textos = [
            'disponivel' => 'Disponível',
            'ocupado' => 'Ocupado',
            'manutencao' => 'Manutenção',
            'confirmada' => 'Confirmada',
            'pendente' => 'Pendente',
            'cancelada' => 'Cancelada',
            'concluida' => 'Concluída'
        ];
        
        $classe = $classes[$status] ?? 'secondary';
        $texto = $textos[$status] ?? ucfirst($status);
        
        return "<span class=\"badge badge-{$classe}\">{$texto}</span>";
    }

}

?>