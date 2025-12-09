<?php
require_once __DIR__ . '/../Database.php';

use database\Database;

$db = new Database();
$conn = $db->getConnection();

$sql = "
    INSERT INTO pessoa (id_pessoa, nome, sexo, data_nascimento, documento, telefone, email, tipo_pessoa, endereco_id_endereco) VALUES
    (301, 'João Silva Santos', 'M', '1985-03-15', '123.456.789-00', '(37) 99999-0001', 'joao@email.com', 'hospede', 201),
    (302, 'Maria Oliveira Costa', 'F', '1990-07-22', '234.567.890-11', '(37) 99999-0002', 'maria@email.com', 'hospede', 202),
    (303, 'Carlos Pereira Lima', 'M', '1978-11-30', '345.678.901-22', '(37) 99999-0003', 'carlos@email.com', 'hospede', 203),
    (304, 'Ana Paula Souza', 'F', '1995-05-10', '456.789.012-33', '(21) 98888-0001', 'ana@email.com', 'hospede', 204),
    (305, 'Roberto Alves Mendes', 'M', '1982-09-25', '567.890.123-44', '(11) 97777-0001', 'roberto@email.com', 'hospede', 205),
    (306, 'Juliana Costa Ribeiro', 'F', '1988-02-14', '678.901.234-55', '(21) 96666-0001', 'juliana@email.com', 'hospede', 206),
    (307, 'Fernando Santos Rocha', 'M', '1992-12-05', '789.012.345-66', '(81) 95555-0001', 'fernando@email.com', 'hospede', 207),
    (308, 'Patrícia Lima Dias', 'F', '1987-06-18', '890.123.456-77', '(51) 94444-0001', 'patricia@email.com', 'hospede', 208),
    (309, 'Ricardo Fernandes Cruz', 'M', '1980-04-20', '901.234.567-88', '(85) 93333-0001', 'ricardo@email.com', 'hospede', 209),
    (310, 'Camila Rodrigues Nunes', 'F', '1993-08-08', '012.345.678-99', '(48) 92222-0001', 'camila@email.com', 'hospede', 210),


    (311, 'Pedro Henrique Souza', 'M', '1990-05-20', '100.200.300-40', '(37) 98888-0001', 'pedro@hotel.com', 'funcionario', 201),
    (312, 'Amanda Silva Costa', 'F', '1992-08-15', '200.300.400-50', '(37) 98888-0002', 'amanda@hotel.com', 'funcionario', 202),
    (313, 'Rafael Oliveira Dias', 'M', '1988-03-10', '300.400.500-60', '(37) 98888-0003', 'rafael@hotel.com', 'funcionario', 203),
    (314, 'Fernanda Lima Santos', 'F', '1991-12-25', '400.500.600-70', '(37) 98888-0004', 'fernanda@hotel.com', 'funcionario', 204),
    (315, 'Bruno Costa Alves', 'M', '1987-07-30', '500.600.700-80', '(37) 98888-0005', 'bruno@hotel.com', 'funcionario', 205)
";
$conn->exec($sql);

echo "Tabela pessoa povoada com sucesso.\n";