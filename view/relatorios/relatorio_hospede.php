<?php
require_once __DIR__ . '/../../database/Database.php';
require_once __DIR__ . '/../../controller/RelatorioController.php';
require_once __DIR__ . '/../../utils/Formatter.php';

use Controller\RelatorioController;

$controller = new RelatorioController();

// Buscar dados do dashboard
$dashboard = $controller->dashboard();
$topHospedes = $controller->hospedesMaisFrequentes(10);

$stats = $dashboard['sucesso'] ? $dashboard['estatisticas'] : [];
$hospedesAtivos = $dashboard['sucesso'] ? $dashboard['hospedes_ativos'] : [];
$top = $topHospedes['sucesso'] ? $topHospedes['dados'] : [];

// Função para buscar reservas de um hóspede
function buscarReservasPorHospede($conn, $nome) {
    $sql = "SELECT r.idreserva, r.data_checkin_previsto, r.data_checkout_previsto, r.valor_reserva, r.status, q.numero as quarto_numero
            FROM reserva r
            INNER JOIN hospede h ON r.id_hospede = h.id_pessoa
            INNER JOIN pessoa p ON h.id_pessoa = p.id_pessoa
            INNER JOIN quarto q ON r.id_quarto = q.id_quarto
            WHERE p.nome = ?
            ORDER BY r.data_checkin_previsto DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nome]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Hóspedes - Palácio Lumière</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
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

        /* Novo estilo para títulos dos cards de relatório */
        .report-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .report-card-header {
            background: linear-gradient(135deg, #d6a427ff 0%, #d6a427ff 100%);
            padding: 20px 30px;
            border-bottom: 3px solid #6B5111;
        }

        .report-card-title {
            font-family: 'Cinzel', serif;
            font-size: 1.4rem;
            font-weight: 600;
            color: #6B5111;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .report-card-title i {
            font-size: 1.3rem;
        }

        .report-card-subtitle {
            font-family: 'Lato', sans-serif;
            font-size: 0.9rem;
            color: #5a4610;
            margin: 8px 0 0 0;
            font-weight: 400;
        }

        .report-card-body {
            padding: 25px 30px;
        }

        /* Ajuste para as tabelas dentro dos cards */
        .report-card .table-responsive {
            margin: 0;
        }

        .report-card table {
            margin-bottom: 0;
        }

        /* Estilo do modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.7);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .modal-header-cinzel {
            background: linear-gradient(135deg, #d6a427ff 0%, #d6a427ff 100%);
            padding: 15px;
            border-bottom: 3px solid #6B5111;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .modal-close:hover,
        .modal-close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-body-columns {
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 15px 0;
        }

        .modal-body-columns .column {
            flex: 1;
        }

        /* Estilo para o botão de ver reservas */
        .btn-view-details {
            background: none;
            border: none;
            color: #0d6efd;
            cursor: pointer;
            padding: 0;
            font-size: inherit;
            text-align: left;
        }

        /* Novo estilo para o botão "Realizar Consulta" */
        .btn-primary-custom {
            background-color: #FBBD24;
            color: #5a4610;
            padding: 10px 22px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease;
        }

        .btn-primary-custom:hover {
            background-color: #FBBD24;
        }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <!-- Menu Lateral (Sidebar) -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="../../index.php"><img src="../../assets/img/logo.png" alt="Palácio Lumière Logo"></a>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-item">
                <a href="../../index.php"><i class="fas fa-tachometer-alt"></i> Painel</a>
            </div>
            <!-- Hóspedes Dropdown -->
            <div class="nav-item">
                <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-users"></i> Hóspedes</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="../../view/cadastrar_hospede.php"><i class="fas fa-plus"></i> Cadastrar</a>
                    <a href="../../view/listar_hospede.php"><i class="fas fa-list"></i> Listar</a>
                    <a href="../../view/editar_hospede.php"><i class="fas fa-edit"></i> Editar</a>
                    <a href="../../view/deletar_hospede.php"><i class="fas fa-trash"></i> Deletar</a>
                </div>
            </div>
            <!-- Funcionários Dropdown -->
            <div class="nav-item">
                <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-briefcase"></i> Funcionários</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="../../view/cadastrar_funcionario.php"><i class="fas fa-plus"></i> Cadastrar</a>
                    <a href="../../view/lista_funcionario.php"><i class="fas fa-list"></i> Listar</a>
                    <a href="../../view/editar_funcionario.php"><i class="fas fa-edit"></i> Editar</a>
                    <a href="../../view/deletar_funcionario.php"><i class="fas fa-trash"></i> Deletar</a>
                </div>
            </div>
            <!-- Quartos Dropdown -->
            <div class="nav-item">
                <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-door-open"></i> Quartos</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="../../view/cadastrar_quarto.php"><i class="fas fa-plus"></i> Cadastrar</a>
                    <a href="../../view/lista_quartos.php"><i class="fas fa-list"></i> Listar</a>
                    <a href="../../view/editar_quartos.php"><i class="fas fa-edit"></i> Editar</a>
                    <a href="../../view/deletar_quarto.php"><i class="fas fa-trash"></i> Deletar</a>
                </div>
            </div>
            <!-- Reservas Dropdown -->
            <div class="nav-item">
                <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                    <span><i class="fas fa-calendar-alt"></i> Reservas</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown-menu">
                    <a href="../../criar_reserva.php"><i class="fas fa-plus"></i> Nova Reserva</a>
                    <a href="../../lista_reservas.php"><i class="fas fa-list"></i> Listar</a>
                    <a href="../../editar_reserva.php"><i class="fas fa-edit"></i> Editar</a>
                    <a href="../../deletar_reserva.php"><i class="fas fa-trash"></i> Deletar</a>
                </div>
            </div>
            <!-- Relatórios -->
            <div class="nav-item">
                <a href="relatorio_hospede.php" class="active"><i class="fas fa-chart-bar"></i> Relatórios</a>
            </div>
        </nav>
    </aside>

    <!-- Conteúdo Principal -->
    <main class="main-content">
        <header class="main-header" style="margin-bottom: 40px; display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1 style="display: inline-block; margin-right: 20px;">Relatório de Hóspedes</h1>
            </div>
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                <a href="consultas_personalizadas.php" class="btn-primary-custom" style="padding: 10px 22px; font-size: 1rem; border-radius: 8px; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-search"></i> Realizar Consulta
                </a>
            </div>
        </header>
        
        <div class="container mt-3" style="max-width: 1400px;">
            <!-- Cards de Estatísticas -->
            <div class="summary-cards" style="margin-bottom: 20px;">
                <div class="card">
                    <div class="card-icon" style="background-color: #e3f2fd; color: #0d6efd;">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-value" style="font-size: 20px"><?= $stats['total_hospedes'] ?? 0 ?></span>
                        <span class="card-label">Total de Hóspedes</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon" style="background-color: #d1f5e0; color: #198754;">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-value" style="font-size: 20px"><?= $stats['hospedes_ativos'] ?? 0 ?></span>
                        <span class="card-label">Hóspedes Ativos</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon" style="background-color: #fff8e1; color: #ffc107;">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-value" style="font-size: 20px"><?= $stats['total_reservas'] ?? 0 ?></span>
                        <span class="card-label">Total Reservas</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon" style="background-color: #e0f7fa; color: #0dcaf0;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-value" style="font-size: 20px">R$ <?= number_format($stats['receita_total'] ?? 0, 2, ',', '.') ?></span>
                        <span class="card-label">Receita Total</span>
                    </div>
                </div>
            </div>

            <!-- Hóspedes Ativos Agora -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3 class="report-card-title">
                        <i class="bi bi-door-open"></i>
                        Hóspedes com Check-in Ativo
                    </h3>
                </div>
                <div class="report-card-body">
                    <?php if (empty($hospedesAtivos)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Nenhum hóspede com check-in ativo no momento.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
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
            <div class="report-card">
                <div class="report-card-header">
                    <h3 class="report-card-title">
                        <i class="bi bi-trophy"></i>
                        Top 10 Hóspedes Mais Frequentes
                    </h3>
                </div>
                <div class="report-card-body">
                    <?php if (empty($top)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Nenhum dado disponível ainda.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
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
                                                <?php
                                                $reservas = buscarReservasPorHospede($controller->conn, $hospede['nome']);
                                                if ($reservas):
                                                ?>
                                                <?php endif; ?>
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
        </div>
    </main>
</div>

<script>
function toggleDropdown(element) {
    const menu = element.nextElementSibling;
    menu.classList.toggle('show');
    element.classList.toggle('active');
}
</script>
</body>
</html>