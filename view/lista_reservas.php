<?php

require_once __DIR__ . '/../controller/ReservaController.php';

use Controller\ReservaController;

session_start();

$mensagem = '';
$erros = [];
$reservas = [];

$controller = new ReservaController();

// Se for DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $resultado = $controller->deletar($_POST['id'] ?? 0);
    if ($resultado['sucesso']) {
        $mensagem = $resultado['mensagem'];
    } else {
        $erros = $resultado['erros'];
    }
}

// Buscar todas as reservas
$resultado = $controller->lista();
if ($resultado['sucesso']) {
    $reservas = $resultado['dados'];
} else {
    $erros = $resultado['erros'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Reservas - Palácio Lumière</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Menu Lateral (Sidebar) -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="../index.php"><img src="../assets/img/logo.png" alt="Palácio Lumière Logo"></a>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="../index.php"><i class="fas fa-tachometer-alt"></i> Painel</a>
                </div>

                <!-- Hóspedes Dropdown -->
                <div class="nav-item">
                    <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                        <span><i class="fas fa-users"></i> Hóspedes</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="cadastrar_hospede.php"><i class="fas fa-plus"></i> Cadastrar</a>
                        <a href="listar_hospede.php"><i class="fas fa-list"></i> Listar</a>
                        <a href="editar_hospede.php"><i class="fas fa-edit"></i> Editar</a>
                        <a href="deletar_hospede.php"><i class="fas fa-trash"></i> Deletar</a>
                    </div>
                </div>

                <!-- Funcionários Dropdown -->
                <div class="nav-item">
                    <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                        <span><i class="fas fa-briefcase"></i> Funcionários</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="cadastrar_funcionario.php"><i class="fas fa-plus"></i> Cadastrar</a>
                        <a href="lista_funcionario.php"><i class="fas fa-list"></i> Listar</a>
                        <a href="editar_funcionario.php"><i class="fas fa-edit"></i> Editar</a>
                        <a href="deletar_funcionario.php"><i class="fas fa-trash"></i> Deletar</a>
                    </div>
                </div>

                <!-- Quartos Dropdown -->
                <div class="nav-item">
                    <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                        <span><i class="fas fa-door-open"></i> Quartos</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="cadastrar_quarto.php"><i class="fas fa-plus"></i> Cadastrar</a>
                        <a href="lista_quartos.php"><i class="fas fa-list"></i> Listar</a>
                        <a href="editar_quartos.php"><i class="fas fa-edit"></i> Editar</a>
                        <a href="deletar_quarto.php"><i class="fas fa-trash"></i> Deletar</a>
                    </div>
                </div>

                <!-- Reservas Dropdown -->
                <div class="nav-item">
                    <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                        <span><i class="fas fa-calendar-alt"></i> Reservas</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="criar_reserva.php"><i class="fas fa-plus"></i> Nova Reserva</a>
                        <a href="lista_reservas.php"><i class="fas fa-list"></i> Listar</a>
                        <a href="editar_reserva.php"><i class="fas fa-edit"></i> Editar</a>
                        <a href="deletar_reserva.php"><i class="fas fa-trash"></i> Deletar</a>
                    </div>
                </div>

                <div class="nav-item">
                    <a href="relatorios/relatorio_hospede.php"><i class="fas fa-chart-bar"></i> Relatórios</a>
                </div>
            </nav>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <header class="main-header">
                <h1><i class="fas fa-list"></i> Listar Reservas</h1>
            </header>

            <div class="form-container">
                <?php if ($mensagem): ?>
                    <div class="form-alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <div><?= htmlspecialchars($mensagem) ?></div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($erros)): ?>
                    <div class="form-alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            Erros encontrados:
                            <ul>
                                <?php foreach ($erros as $erro): ?>
                                    <li><?= htmlspecialchars($erro) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Botões de Ação -->
                <div class="btn-group-actions" style="margin-bottom: 30px;">
                    <a href="criar_reserva.php" class="btn-primary-custom">
                        <i class="fas fa-plus-circle"></i> Nova Reserva
                    </a>
                    <a href="../index.php" class="btn-secondary-custom">
                        <i class="fas fa-home"></i> Voltar ao Painel
                    </a>
                </div>

                <!-- Tabela de Reservas -->
                <?php if (!empty($reservas)): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> ID Reserva</th>
                                    <th><i class="fas fa-user"></i> Hóspede</th>
                                    <th><i class="fas fa-door-open"></i> Quarto</th>
                                    <th><i class="fas fa-calendar-check"></i> Check-in</th>
                                    <th><i class="fas fa-calendar-times"></i> Check-out</th>
                                    <th><i class="fas fa-money-bill-wave"></i> Valor</th>
                                    <th><i class="fas fa-info-circle"></i> Status</th>
                                    <th><i class="fas fa-cog"></i> Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservas as $reserva): ?>
                                    <tr>
                                        <td class="text-center"><strong><?= htmlspecialchars($reserva['idreserva'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars($reserva['hospede_nome'] ?? 'N/A') ?></td>
                                        <td class="text-center"><?= htmlspecialchars($reserva['quarto_numero'] ?? 'N/A') ?></td>
                                        <td><?= !empty($reserva['data_checkin_previsto']) ? date('d/m/Y', strtotime($reserva['data_checkin_previsto'])) : 'N/A' ?></td>
                                        <td><?= !empty($reserva['data_checkout_previsto']) ? date('d/m/Y', strtotime($reserva['data_checkout_previsto'])) : 'N/A' ?></td>
                                        <td>R$ <?= !empty($reserva['valor_reserva']) ? number_format($reserva['valor_reserva'], 2, ',', '.') : '0,00' ?></td>
                                        <td class="text-center">
                                            <?php
                                            $statusBadges = [
                                                'pendente' => ['#fff3cd', '#856404'],
                                                'confirmada' => ['#d4edda', '#155724'],
                                                'finalizada' => ['#e2e3e5', '#383d41'],
                                                'cancelada' => ['#f8d7da', '#721c24']
                                            ];
                                            $status = $reserva['status'] ?? 'pendente';
                                            $badge = $statusBadges[$status] ?? $statusBadges['pendente'];
                                            ?>
                                            <span style="background-color: <?= $badge[0] ?>; color: <?= $badge[1] ?>; padding: 4px 8px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                                <?= ucfirst($status) ?>
                                            </span>
                                        </td>
                                        <td class="table-actions">
                                            <a href="editar_reserva.php?id=<?= $reserva['idreserva'] ?>" 
                                               class="btn-action btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="" style="display: inline;" 
                                                  onsubmit="return confirm('Tem certeza que deseja deletar esta reserva?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $reserva['idreserva'] ?>">
                                                <button type="submit" class="btn-action btn-delete" title="Deletar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer">
                        <p>Total de reservas: <strong><?= count($reservas) ?></strong></p>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Nenhuma reserva cadastrada</h3>
                        <p>Comece a criar reservas para seus hóspedes.</p>
                    </div>
                <?php endif; ?>
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
