<?php
require_once __DIR__ . '/../controller/RelatorioController.php';
require_once __DIR__ . '/../utils/Formatter.php';

use Controller\RelatorioController;

$controller = new RelatorioController();

// Buscar dados do dashboard
$dashboard = $controller->dashboard();
$topHospedes = $controller->hospedesMaisFrequentes(10);

$stats = $dashboard['sucesso'] ? $dashboard['estatisticas'] : [];
$hospedesAtivos = $dashboard['sucesso'] ? $dashboard['hospedes_ativos'] : [];
$top = $topHospedes['sucesso'] ? $topHospedes['dados'] : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Hóspedes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .stat-card-primary { border-color: #0d6efd; }
        .stat-card-success { border-color: #198754; }
        .stat-card-warning { border-color: #ffc107; }
        .stat-card-info { border-color: #0dcaf0; }
        
        .top-badge {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .top-1 { background: linear-gradient(135deg, #FFD700, #FFA500); }
        .top-2 { background: linear-gradient(135deg, #C0C0C0, #808080); }
        .top-3 { background: linear-gradient(135deg, #CD7F32, #8B4513); }
    </style>
</head>
<body>
    <div class="container mt-5">
        <!-- Cabeçalho -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-graph-up"></i> Relatório de Hóspedes</h2>
            <div>
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
                <a href="../index.php" class="btn btn-secondary">
                    <i class="bi bi-house"></i> Menu
                </a>
            </div>
        </div>

        <!-- Cards de Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-card-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total de Hóspedes</p>
                                <h3 class="mb-0"><?= $stats['total_hospedes'] ?? 0 ?></h3>
                            </div>
                            <div class="text-primary">
                                <i class="bi bi-people" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-card-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Hóspedes Ativos</p>
                                <h3 class="mb-0"><?= $stats['hospedes_ativos'] ?? 0 ?></h3>
                            </div>
                            <div class="text-success">
                                <i class="bi bi-person-check" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-card-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Reservas</p>
                                <h3 class="mb-0"><?= $stats['total_reservas'] ?? 0 ?></h3>
                            </div>
                            <div class="text-warning">
                                <i class="bi bi-calendar-check" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 mb-3">
                <div class="card stat-card stat-card-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Receita Total</p>
                                <h3 class="mb-0">R$ <?= number_format($stats['receita_total'] ?? 0, 2, ',', '.') ?></h3>
                            </div>
                            <div class="text-info">
                                <i class="bi bi-currency-dollar" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hóspedes Ativos Agora -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-door-open"></i> Hóspedes com Check-in Ativo</h5>
            </div>
            <div class="card-body">
                <?php if (empty($hospedesAtivos)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> Nenhum hóspede com check-in ativo no momento.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Quarto</th>
                                    <th>Check-in</th>
                                    <th>Check-out</th>
                                    <th>Dias Restantes</th>
                                    <th>Contato</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hospedesAtivos as $hospede): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($hospede['nome']) ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                Quarto <?= htmlspecialchars($hospede['numero_quarto']) ?>
                                            </span>
                                        </td>
                                        <td><?= Formatter::formatarData($hospede['data_checkin']) ?></td>
                                        <td><?= Formatter::formatarData($hospede['data_checkout']) ?></td>
                                        <td>
                                            <span class="badge bg-<?= $hospede['dias_restantes'] <= 1 ? 'danger' : 'info' ?>">
                                                <?= $hospede['dias_restantes'] ?> dia(s)
                                            </span>
                                        </td>
                                        <td>
                                            <small>
                                                <i class="bi bi-telephone"></i> <?= Formatter::formatarTelefone($hospede['telefone']) ?>
                                            </small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top 10 Hóspedes Mais Frequentes -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-trophy"></i> Top 10 Hóspedes Mais Frequentes</h5>
            </div>
            <div class="card-body">
                <?php if (empty($top)): ?>
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle"></i> Nenhum dado disponível ainda.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="50">#</th>
                                    <th>Nome</th>
                                    <th class="text-center">Total Reservas</th>
                                    <th class="text-end">Valor Gasto</th>
                                    <th class="text-end">Ticket Médio</th>
                                    <th>Última Visita</th>
                                    <th>Cliente Desde</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top as $index => $hospede): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index < 3): ?>
                                                <div class="top-badge top-<?= $index + 1 ?>">
                                                    <?= $index + 1 ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="text-center fw-bold text-muted">
                                                    <?= $index + 1 ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($hospede['nome']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-envelope"></i> <?= htmlspecialchars($hospede['email']) ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info fs-6">
                                                <?= $hospede['total_reservas'] ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <strong class="text-success">
                                                R$ <?= number_format($hospede['valor_total_gasto'], 2, ',', '.') ?>
                                            </strong>
                                        </td>
                                        <td class="text-end">
                                            R$ <?= number_format($hospede['ticket_medio'], 2, ',', '.') ?>
                                        </td>
                                        <td>
                                            <small><?= Formatter::formatarData($hospede['ultima_visita']) ?></small>
                                        </td>
                                        <td>
                                            <small><?= Formatter::formatarData($hospede['primeira_visita']) ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Informações Adicionais -->
        <div class="card mb-5">
            <div class="card-body bg-light">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h5 class="text-muted">Ticket Médio Geral</h5>
                        <h3 class="text-primary">R$ <?= number_format($stats['ticket_medio_geral'] ?? 0, 2, ',', '.') ?></h3>
                    </div>
                    <div class="col-md-4">
                        <h5 class="text-muted">Taxa de Ocupação</h5>
                        <h3 class="text-success">
                            <?php 
                            $taxa = $stats['total_hospedes'] > 0 
                                ? ($stats['hospedes_ativos'] / $stats['total_hospedes']) * 100 
                                : 0;
                            echo number_format($taxa, 1) . '%';
                            ?>
                        </h3>
                    </div>
                    <div class="col-md-4">
                        <h5 class="text-muted">Média Reservas/Hóspede</h5>
                        <h3 class="text-info">
                            <?php 
                            $media = $stats['total_hospedes'] > 0 
                                ? $stats['total_reservas'] / $stats['total_hospedes'] 
                                : 0;
                            echo number_format($media, 1);
                            ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style media="print">
        .btn, .card-header {
            print-color-adjust: exact;
            -webkit-print-color-adjust: exact;
        }
        .no-print {
            display: none !important;
        }
    </style>
</body>
</html>