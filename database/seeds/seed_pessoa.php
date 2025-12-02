<?php
require_once __DIR__ . '/../Database.php';

$db = new Database();
$conn = $db->getConnection();

$sql = "
INSERT INTO pessoa (nome, sexo, data_nascimento, documento, telefone, email, tipo_pessoa, endereco_id_endereco) VALUES
-- Hóspedes
('João Silva Santos', 'M', '1985-03-15', '123.456.789-00', '(37) 99999-0001', 'joao.silva@email.com', 'hospede', 1),
('Maria Oliveira Costa', 'F', '1990-07-22', '234.567.890-11', '(37) 99999-0002', 'maria.oliveira@email.com', 'hospede', 2),
('Carlos Pereira Lima', 'M', '1978-11-30', '345.678.901-22', '(37) 99999-0003', 'carlos.pereira@email.com', 'hospede', 3),
('Ana Paula Souza', 'F', '1995-05-10', '456.789.012-33', '(21) 98888-0001', 'ana.souza@email.com', 'hospede', 6),
('Roberto Alves Mendes', 'M', '1982-09-25', '567.890.123-44', '(11) 97777-0001', 'roberto.mendes@email.com', 'hospede', 7),
('Juliana Costa Ribeiro', 'F', '1988-02-14', '678.901.234-55', '(21) 96666-0001', 'juliana.ribeiro@email.com', 'hospede', 9),
('Fernando Santos Rocha', 'M', '1992-12-05', '789.012.345-66', '(81) 95555-0001', 'fernando.rocha@email.com', 'hospede', 10),
('Patrícia Lima Dias', 'F', '1987-06-18', '890.123.456-77', '(51) 94444-0001', 'patricia.dias@email.com', 'hospede', 11),
('Ricardo Fernandes Cruz', 'M', '1980-04-20', '901.234.567-88', '(85) 93333-0001', 'ricardo.cruz@email.com', 'hospede', 12),
('Camila Rodrigues Nunes', 'F', '1993-08-08', '012.345.678-99', '(48) 92222-0001', 'camila.nunes@email.com', 'hospede', 13),
('Lucas Martins Araújo', 'M', '1991-01-12', '111.222.333-44', '(37) 99999-0004', 'lucas.araujo@email.com', 'hospede', 4),
('Beatriz Almeida Pinto', 'F', '1986-10-30', '222.333.444-55', '(37) 99999-0005', 'beatriz.pinto@email.com', 'hospede', 5),
('Gabriel Costa Freitas', 'M', '1994-03-25', '333.444.555-66', '(11) 98888-0002', 'gabriel.freitas@email.com', 'hospede', 8),
('Larissa Santos Barros', 'F', '1989-07-15', '444.555.666-77', '(37) 99999-0006', 'larissa.barros@email.com', 'hospede', 14),
('Thiago Oliveira Melo', 'M', '1983-11-28', '555.666.777-88', '(37) 99999-0007', 'thiago.melo@email.com', 'hospede', 15),


('Pedro Henrique Souza', 'M', '1990-05-20', '100.200.300-40', '(37) 98888-0001', 'pedro.souza@hotel.com', 'funcionario', 1),
('Amanda Silva Costa', 'F', '1992-08-15', '200.300.400-50', '(37) 98888-0002', 'amanda.costa@hotel.com', 'funcionario', 2),
('Rafael Oliveira Dias', 'M', '1988-03-10', '300.400.500-60', '(37) 98888-0003', 'rafael.dias@hotel.com', 'funcionario', 3),
('Fernanda Lima Santos', 'F', '1991-12-25', '400.500.600-70', '(37) 98888-0004', 'fernanda.santos@hotel.com', 'funcionario', 4),
('Bruno Costa Alves', 'M', '1987-07-30', '500.600.700-80', '(37) 98888-0005', 'bruno.alves@hotel.com', 'funcionario', 5),
('Carolina Pereira Ramos', 'F', '1993-09-12', '600.700.800-90', '(37) 98888-0006', 'carolina.ramos@hotel.com', 'funcionario', 14),
('Rodrigo Martins Silva', 'M', '1989-04-18', '700.800.900-00', '(37) 98888-0007', 'rodrigo.silva@hotel.com', 'funcionario', 15),
('Isabela Santos Nunes', 'F', '1994-11-22', '800.900.000-11', '(37) 98888-0008', 'isabela.nunes@hotel.com', 'funcionario', 1),
('Guilherme Rocha Pinto', 'M', '1986-06-08', '900.000.111-22', '(37) 98888-0009', 'guilherme.pinto@hotel.com', 'funcionario', 2),
('Mariana Alves Cardoso', 'F', '1990-02-14', '000.111.222-33', '(37) 98888-0010', 'mariana.cardoso@hotel.com', 'funcionario', 3);
";

$conn->exec($sql);
echo "Tabela pessoa povoada com sucesso.\n";