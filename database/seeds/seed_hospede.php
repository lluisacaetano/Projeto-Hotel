<?php
require_once __DIR__ . '/../Database.php';

$db = new Database();
$conn = $db->getConnection();

$sql = "
INSERT INTO hospede (id_pessoa, preferencias, historico) VALUES
(1, 'Quarto silencioso, andar alto', 'Cliente regular, 5 estadias'),
(2, 'Vista para o mar, café reforçado', 'Primeira estadia'),
(3, 'Cama king size, ar condicionado', 'Cliente VIP, 12 estadias'),
(4, 'Quarto com varanda', 'Cliente regular, 3 estadias'),
(5, 'Andar térreo, próximo elevador', 'Segunda estadia'),
(6, 'Minibar completo, TV a cabo', 'Cliente regular, 7 estadias'),
(7, 'Quarto para não fumantes', 'Primeira estadia'),
(8, 'Berço infantil, frigobar', 'Cliente regular, 4 estadias'),
(9, 'Hidromassagem, vista panorâmica', 'Cliente VIP, 15 estadias'),
(10, 'Quarto silencioso, café da manhã', 'Segunda estadia'),
(11, 'Wi-Fi de alta velocidade', 'Terceira estadia'),
(12, 'Quarto acessível, térreo', 'Cliente regular, 6 estadias'),
(13, 'Cama extra, frigobar', 'Primeira estadia'),
(14, 'Vista jardim, varanda ampla', 'Cliente VIP, 10 estadias'),
(15, 'Quarto duplo, ar condicionado', 'Cliente regular, 8 estadias');
";

$conn->exec($sql);

echo "Tabela hospede povoada com sucesso.\n";