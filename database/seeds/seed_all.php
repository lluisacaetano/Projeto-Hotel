<?php
require_once __DIR__ . '/../Database.php';

echo "Iniciando povoamento...\n";

include __DIR__ . '..seeds/seed_enderecos.php';
include __DIR__ . '/seed_pessoas.php';
include __DIR__ . '/seed_hospedes.php';
include __DIR__ . '/seed_quartos.php';
include __DIR__ . '/seed_reservas.php';

echo "Povoamento finalizado!\n";
