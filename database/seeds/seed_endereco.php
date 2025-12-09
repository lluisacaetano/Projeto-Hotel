<?php
require_once __DIR__ . '/../Database.php';

use database\Database;

$db = new Database();
$conn = $db->getConnection();

$sql = "
    INSERT INTO endereco (id_endereco, logradouro, numero, bairro, cidade, estado, pais, cep) VALUES
    (201, 'Rua das Flores', 100, 'Centro', 'Formiga', 'MG', 'Brasil', '35570-000'),
    (202, 'Av. Principal', 250, 'Jardim', 'Formiga', 'MG', 'Brasil', '35570-100'),
    (203, 'Rua do Comércio', 350, 'Centro', 'Formiga', 'MG', 'Brasil', '35570-050'),
    (204, 'Av. Brasil', 500, 'Industrial', 'Divinópolis', 'MG', 'Brasil', '35500-000'),
    (205, 'Rua Santa Rita', 75, 'São José', 'Formiga', 'MG', 'Brasil', '35570-200'),
    (206, 'Av. Atlântica', 1500, 'Copacabana', 'Rio de Janeiro', 'RJ', 'Brasil', '22021-000'),
    (207, 'Rua Augusta', 2000, 'Consolação', 'São Paulo', 'SP', 'Brasil', '01305-100'),
    (208, 'Av. Paulista', 1000, 'Bela Vista', 'São Paulo', 'SP', 'Brasil', '01310-100'),
    (209, 'Rua das Palmeiras', 450, 'Botafogo', 'Rio de Janeiro', 'RJ', 'Brasil', '22280-000'),
    (210, 'Av. Boa Viagem', 3000, 'Boa Viagem', 'Recife', 'PE', 'Brasil', '51020-000'),
    (211, 'Rua Padre Chagas', 500, 'Moinhos de Vento', 'Porto Alegre', 'RS', 'Brasil', '90570-080'),
    (212, 'Av. Beira Mar', 1200, 'Meireles', 'Fortaleza', 'CE', 'Brasil', '60165-121'),
    (213, 'Rua da Praia', 800, 'Centro', 'Florianópolis', 'SC', 'Brasil', '88010-400'),
    (214, 'Av. Central', 150, 'Vila Nova', 'Formiga', 'MG', 'Brasil', '35570-300'),
    (215, 'Rua Tiradentes', 220, 'Centro', 'Formiga', 'MG', 'Brasil', '35570-020');
";

$conn->exec($sql);

echo "Tabela endereco povoada com sucesso.\n";