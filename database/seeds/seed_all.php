<?php
require_once __DIR__ . '/../Database.php';

echo "Iniciando povoamento...\n";

include __DIR__ . '/seed_endereco.php';
include __DIR__ . '/seed_pessoa.php';
include __DIR__ . '/seed_hospede.php';
include __DIR__ . '/seed_funcionario.php';
include __DIR__ . '/seed_quarto.php';
include __DIR__ . '/seed_reserva.php';

echo "Povoamento finalizado!\n";
?>