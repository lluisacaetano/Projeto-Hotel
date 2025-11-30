<?php
echo "=== DIAGNÓSTICO DO PROJETO ===\n\n";

// 1. Testa conexão com banco
echo "1. TESTANDO CONEXÃO COM BANCO DE DADOS\n";
require_once 'database/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if($conn) {
        echo "✓ Conexão OK\n\n";
    } else {
        echo "✗ Conexão retornou NULL\n\n";
    }
} catch (Exception $e) {
    echo "✗ Erro: " . $e->getMessage() . "\n\n";
}

// 2. Verifica se as classes existem
echo "2. VERIFICANDO CLASSES\n";

$classes = [
    'model/Quarto.php' => 'Quarto',
    'model/Hospede.php' => 'Hospede',
    'model/Reserva.php' => 'Reserva',
    'model/Func.php' => 'Func',
    'controller/HospedeController.php' => 'HospedeController',
];

foreach($classes as $arquivo => $classe) {
    if(file_exists($arquivo)) {
        require_once $arquivo;
        if(class_exists($classe)) {
            echo "✓ $classe OK\n";
        } else {
            echo "✗ $classe não encontrada em $arquivo\n";
        }
    } else {
        echo "✗ Arquivo $arquivo não existe\n";
    }
}

echo "\n3. TABELAS NO BANCO\n";
try {
    $conn = (new Database())->getConnection();
    $query = "SHOW TABLES FROM mydb";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $tabelas = $stmt->fetchAll(PDO::FETCH_NUM);
    
    if(count($tabelas) > 0) {
        echo "✓ Tabelas encontradas:\n";
        foreach($tabelas as $tab) {
            echo "  - " . $tab[0] . "\n";
        }
    } else {
        echo "✗ Nenhuma tabela encontrada\n";
    }
} catch (Exception $e) {
    echo "✗ Erro ao listar tabelas: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DO DIAGNÓSTICO ===\n";
?>