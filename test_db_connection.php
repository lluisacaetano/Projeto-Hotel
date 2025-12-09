<?php

// Configurações do banco de dados
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'mydb';

try {
    // Tenta conectar ao MySQL
    $connection = new mysqli($host, $user, $password, $database);
    
    // Verifica se houve erro na conexão
    if ($connection->connect_error) {
        die("❌ Erro de conexão: " . $connection->connect_error);
    }
    
    echo "✅ Conexão ao banco de dados estabelecida com sucesso!<br>";
    echo "📊 Banco de dados: <strong>" . $database . "</strong><br>";
    echo "🖥️ Host: <strong>" . $host . "</strong><br><br>";
    
    // Lista todas as tabelas
    $result = $connection->query("SHOW TABLES");
    
    if ($result) {
        echo "📋 Tabelas no banco de dados:<br>";
        echo "<ul>";
        while ($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    }
    
    // Conta registros em cada tabela
    echo "<h3>Registros por tabela:</h3>";
    $tables = ['pessoa', 'endereco', 'hospede', 'funcionario', 'quarto', 'reserva', 'pagamento', 'consumo', 'item'];
    
    foreach ($tables as $table) {
        $count_result = $connection->query("SELECT COUNT(*) as total FROM $table");
        if ($count_result) {
            $row = $count_result->fetch_assoc();
            echo "📌 <strong>$table</strong>: " . $row['total'] . " registros<br>";
        }
    }
    
    $connection->close();
    echo "<br>✅ Conexão fechada com sucesso!";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
