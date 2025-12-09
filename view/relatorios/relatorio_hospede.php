<?php
require_once __DIR__ . '/../../database/Database.php';
require_once __DIR__ . '/../../controller/RelatorioController.php';
require_once __DIR__ . '/../../utils/Formatter.php';

use Controller\RelatorioController;

$controller = new RelatorioController();

// Buscar dados do dashboard
$dashboard = $controller->dashboard();
$topHospedes = $controller->hospedesMaisFrequentes(10);
$hospedesVIP = $controller->hospedesVIP();
$faturamento30dias = $controller->faturamentoUltimos30Dias();
$topFuncionariosMes = $controller->topFuncionariosMes();
$aniversariantesMes = $controller->funcionariosAniversariantesMes();
$despesasFuncionarios = $controller->despesasFuncionarios();

$stats = $dashboard['sucesso'] ? $dashboard['estatisticas'] : [];
$hospedesCheckinAtivo = $dashboard['sucesso'] ? $dashboard['hospedes_checkin_ativo'] : [];
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
        /* Medalhas para top 3 */
        .func-medal {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            color: #fff;
            margin-right: 8px;
        }
        .func-medal.gold { background: linear-gradient(135deg, #FFD700, #FFA500); }
        .func-medal.silver { background: linear-gradient(135deg, #C0C0C0, #808080); }
        .func-medal.bronze { background: linear-gradient(135deg, #CD7F32, #8B4513); }
        .total-highlight {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 18px 24px;
            font-size: 1.2rem;
            font-weight: bold;
            color: #6B5111;
            margin-top: 12px;
            text-align: right;
            box-shadow: 0 2px 8px rgba(251,189,36,0.08);
            border: 1px solid #f3e6c4;
        }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <!-- Menu Lateral-->
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
                    <a href="../../view/criar_reserva.php"><i class="fas fa-plus"></i> Nova Reserva</a>
                    <a href="../../view/lista_reservas.php"><i class="fas fa-list"></i> Listar</a>
                    <a href="../../view/editar_reserva.php"><i class="fas fa-edit"></i> Editar</a>
                    <a href="../../view/deletar_reserva.php"><i class="fas fa-trash"></i> Deletar</a>
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
        </header>
        
        <div class="container mt-3" style="max-width: 1400px;">
            <!-- Cards de Estatísticas -->
            <div class="summary-cards" style="margin-bottom: 20px; display: flex; gap: 24px;">
                <div class="card" style="flex: 1;">
                    <div class="card-icon" style="background-color: #e3f2fd; color: #0d6efd;">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-value" style="font-size: 20px"><?= $stats['total_hospedes'] ?? 0 ?></span>
                        <span class="card-label">Total de Hóspedes</span>
                    </div>
                </div>
                <div class="card" style="flex: 1;">
                    <div class="card-icon" style="background-color: #fff8e1; color: #ffc107;">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-value" style="font-size: 20px"><?= $stats['total_reservas'] ?? 0 ?></span>
                        <span class="card-label">Total Reservas</span>
                    </div>
                </div>
                <div class="card" style="flex: 1;">
                    <div class="card-icon" style="background-color: #e0f7fa; color: #0dcaf0;">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="card-info">
                        <span class="card-value" style="font-size: 20px">R$ <?= number_format($stats['receita_total'] ?? 0, 2, ',', '.') ?></span>
                        <span class="card-label">Receita Total</span>
                    </div>
                </div>
            </div>

            <!-- Hóspedes com Check-in Ativo (reservas em andamento) -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3 class="report-card-title">
                        <i class="bi bi-door-open"></i>
                        Hóspedes com Check-in Ativo
                    </h3>
                </div>
                <div class="report-card-body">
                    <?php if (empty($hospedesCheckinAtivo)): ?>
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
                                    <?php foreach ($hospedesCheckinAtivo as $hospede): ?>
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

            <!-- Hóspedes VIP -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3 class="report-card-title">
                        <i class="bi bi-star-fill"></i>
                        Hóspedes VIP (mais de 5 reservas)
                    </h3>
                </div>
                <div class="report-card-body">
                    <?php if (empty($hospedesVIP)): ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> Nenhum hóspede VIP encontrado.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nome</th>
                                        <th>Total de Reservas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hospedesVIP as $index => $vip): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><strong><?= htmlspecialchars($vip['nome']) ?></strong></td>
                                            <td><?= $vip['total_reservas'] ?></td>
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
                                                <?php if ($index === 0): ?>
                                                    <span class="func-medal gold">🥇</span>
                                                <?php elseif ($index === 1): ?>
                                                    <span class="func-medal silver">🥈</span>
                                                <?php elseif ($index === 2): ?>
                                                    <span class="func-medal bronze">🥉</span>
                                                <?php else: ?>
                                                    <span class="top-badge top-<?= $index + 1 ?>"><?= $index + 1 ?></span>
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

            <!-- Faturamento dos últimos 30 dias -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3 class="report-card-title">
                        <i class="bi bi-cash-stack"></i>
                        Faturamento dos Últimos 30 Dias
                    </h3>
                </div>
                <div class="report-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID Reserva</th>
                                    <th>Hóspede</th>
                                    <th>Funcionário</th>
                                    <th>Data Reserva</th>
                                    <th>Valor</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Mostra apenas reservas finalizadas
                                $reservasFinalizadas = array_filter(
                                    $faturamento30dias['reservas'],
                                    function($reserva) {
                                        return strtolower($reserva['status']) === 'finalizada';
                                    }
                                );
                                ?>
                                <?php foreach ($reservasFinalizadas as $reserva): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($reserva['idreserva']) ?></td>
                                        <td><?= htmlspecialchars($reserva['hospede_nome']) ?></td>
                                        <td><?= htmlspecialchars($reserva['funcionario_nome']) ?></td>
                                        <td><?= htmlspecialchars(date('d/m/Y', strtotime($reserva['data_reserva']))) ?></td>
                                        <td>R$ <?= number_format($reserva['valor_reserva'], 2, ',', '.') ?></td>
                                        <td>
                                            <?php
                                            $status = strtolower($reserva['status']);
                                            $badge = [
                                                'finalizada'   => ['#d4edda', '#155724'],   // verde
                                                'em andamento' => ['#fff3cd', '#856404'],   // amarelo
                                                'cancelada'    => ['#f8d7da', '#721c24'],   // vermelho
                                                'confirmada'   => ['#fed78fff', '#d35400'], // laranja
                                            ];
                                            $cor = $badge[$status] ?? ['#e2e3e5', '#383d41'];
                                            ?>
                                            <span style="background-color: <?= $cor[0] ?>; color: <?= $cor[1] ?>; padding: 4px 10px; border-radius: 4px; font-size: 0.95em; font-weight: 600;">
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($reservasFinalizadas)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Nenhuma reserva finalizada nos últimos 30 dias.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="total-highlight">
                        <i class="bi bi-coin"></i>
                        Total Faturado: R$ <?= number_format($faturamento30dias['total_faturamento'], 2, ',', '.') ?>
                    </div>
                </div>
            </div>

            <!-- Top 10 Funcionários do Mês -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3 class="report-card-title">
                        <i class="bi bi-award"></i>
                        Top 10 Funcionários do Mês (Reservas nos últimos 30 dias)
                    </h3>
                </div>
                <div class="report-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nome</th>
                                    <th>Cargo</th>
                                    <th>Total de Reservas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topFuncionariosMes as $index => $func): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index === 0): ?>
                                                <span class="func-medal gold">🥇</span>
                                            <?php elseif ($index === 1): ?>
                                                <span class="func-medal silver">🥈</span>
                                            <?php elseif ($index === 2): ?>
                                                <span class="func-medal bronze">🥉</span>
                                            <?php else: ?>
                                                <span class="top-badge top-<?= $index + 1 ?>"><?= $index + 1 ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($func['nome']) ?></strong></td>
                                        <td><?= htmlspecialchars($func['cargo'] ?? '-') ?></td>
                                        <td><?= $func['total_reservas'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($topFuncionariosMes)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhum funcionário com reservas no mês.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Funcionários Aniversariantes do Mês -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3 class="report-card-title">
                        <i class="bi bi-gift"></i>
                        Funcionários Aniversariantes do Mês
                    </h3>
                </div>
                <div class="report-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Cargo</th>
                                    <th>Data de Nascimento</th>
                                    <th>Idade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($aniversariantesMes as $aniv): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($aniv['nome']) ?></td>
                                        <td><?= htmlspecialchars($aniv['cargo']) ?></td>
                                        <td><?= date('d/m', strtotime($aniv['data_nascimento'])) ?></td>
                                        <td>
                                            <?php
                                            $nasc = new DateTime($aniv['data_nascimento']);
                                            $hoje = new DateTime();
                                            $idade = $hoje->diff($nasc)->y;
                                            echo $idade . ' anos';
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($aniversariantesMes)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center">Nenhum aniversariante este mês.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Despesas com Funcionários -->
            <div class="report-card">
                <div class="report-card-header">
                    <h3 class="report-card-title">
                        <i class="bi bi-cash"></i>
                        Despesas com Funcionários
                    </h3>
                </div>
                <div class="report-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Cargo</th>
                                    <th>Salário</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($despesasFuncionarios['funcionarios'] as $func): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($func['nome']) ?></td>
                                        <td><?= htmlspecialchars($func['cargo']) ?></td>
                                        <td>R$ <?= number_format($func['salario'], 2, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($despesasFuncionarios['funcionarios'])): ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Nenhum funcionário cadastrado.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="total-highlight">
                        <i class="bi bi-coin"></i>
                        Total de Despesas: R$ <?= number_format($despesasFuncionarios['total_salarios'], 2, ',', '.') ?>
                    </div>
                </div>
            </div

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