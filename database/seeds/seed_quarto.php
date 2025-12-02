<?php
require_once __DIR__ . '/../Database.php';

$db = new Database();
$conn = $db->getConnection();

$sql = "
INSERT INTO quarto (status, numero, andar, descricao, valor_diaria, capacidade_maxima, tipo_quarto) VALUES
('disponivel', 101, 1, 'Quarto Standard com cama casal', 150.00, 2, 'Standard'),
('disponivel', 102, 1, 'Quarto Standard com duas camas', 150.00, 2, 'Standard'),
('ocupado', 103, 1, 'Quarto Standard com cama casal', 150.00, 2, 'Standard'),
('disponivel', 104, 1, 'Quarto Standard triplo', 180.00, 3, 'Standard'),
('manutencao', 105, 1, 'Quarto Standard com varanda', 170.00, 2, 'Standard'),

('disponivel', 201, 2, 'Quarto Standard com vista jardim', 150.00, 2, 'Standard'),
('ocupado', 202, 2, 'Quarto Standard cama king', 160.00, 2, 'Standard'),
('disponivel', 203, 2, 'Quarto Standard duplo', 150.00, 2, 'Standard'),
('disponivel', 204, 2, 'Quarto Standard com varanda', 170.00, 2, 'Standard'),
('ocupado', 205, 2, 'Quarto Standard triplo', 180.00, 3, 'Standard'),

('disponivel', 301, 3, 'Suíte Luxo com hidromassagem', 450.00, 2, 'Luxo'),
('disponivel', 302, 3, 'Suíte Luxo vista mar', 500.00, 2, 'Luxo'),
('ocupado', 303, 3, 'Suíte Luxo completa', 480.00, 2, 'Luxo'),
('disponivel', 304, 3, 'Suíte Luxo presidencial', 600.00, 4, 'Luxo'),
('disponivel', 305, 3, 'Suíte Luxo com varanda ampla', 520.00, 2, 'Luxo'),

('disponivel', 401, 4, 'Cobertura duplex', 800.00, 4, 'Suite'),
('disponivel', 402, 4, 'Cobertura com piscina privativa', 1000.00, 6, 'Suite'),

('disponivel', 106, 1, 'Quarto Standard econômico', 130.00, 2, 'Standard'),

('disponivel', 206, 2, 'Quarto Standard familiar', 180.00, 4, 'Standard'),

('disponivel', 306, 3, 'Suíte Luxo romântica', 550.00, 2, 'Luxo');

";

$conn->exec($sql);

echo "Tabela hospede povoada com sucesso.\n";