<?php
// Aqui você pode incluir lógica para processar a pesquisa
$pesquisa = $_GET['pesquisa'] ?? '';
$tipo = $_GET['tipo'] ?? '';
$resultados = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $pesquisa && $tipo) {
    // Exemplo de lógica para consulta (adapte conforme suas queries)
    require_once __DIR__ . '/../../controller/RelatorioController.php';
    $controller = new \Controller\RelatorioController();
    switch ($tipo) {
        case 'hospede_nome':
            $sql = "SELECT p.nome, p.email, r.* FROM pessoa p INNER JOIN hospede h ON p.id_pessoa = h.id_pessoa INNER JOIN reserva r ON h.id_pessoa = r.id_hospede WHERE p.nome LIKE ?";
            $stmt = $controller->conn->prepare($sql);
            $stmt->execute(['%' . $pesquisa . '%']);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'quarto_numero':
            $sql = "SELECT p.nome, p.email, r.* FROM pessoa p INNER JOIN hospede h ON p.id_pessoa = h.id_pessoa INNER JOIN reserva r ON h.id_pessoa = r.id_hospede INNER JOIN quarto q ON r.id_quarto = q.id_quarto WHERE q.numero LIKE ?";
            $stmt = $controller->conn->prepare($sql);
            $stmt->execute(['%' . $pesquisa . '%']);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        case 'status':
            $sql = "SELECT p.nome, p.email, r.* FROM pessoa p INNER JOIN hospede h ON p.id_pessoa = h.id_pessoa INNER JOIN reserva r ON h.id_pessoa = r.id_hospede WHERE r.status LIKE ?";
            $stmt = $controller->conn->prepare($sql);
            $stmt->execute(['%' . $pesquisa . '%']);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
        // Adicione outros tipos conforme necessário
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Consultas Personalizadas - Palácio Lumière</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="dashboard-wrapper">
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
    <main class="main-content">
        <header class="main-header">
            <h1><i class="fas fa-search"></i> Consultas Personalizadas</h1>
        </header>
        <div class="form-container">
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pesquisa" class="form-label">Pesquisar</label>
                            <input type="text" class="form-control" id="pesquisa" name="pesquisa" value="<?= htmlspecialchars($pesquisa) ?>" placeholder="Digite o termo...">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tipo" class="form-label">Tipo de Consulta</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="">Selecione...</option>
                                <option value="hospede_nome" <?= $tipo == 'hospede_nome' ? 'selected' : '' ?>>Nome do Hóspede</option>
                                <option value="quarto_numero" <?= $tipo == 'quarto_numero' ? 'selected' : '' ?>>Número do Quarto</option>
                                <option value="status" <?= $tipo == 'status' ? 'selected' : '' ?>>Status da Reserva</option>
                                <!-- Adicione outros tipos conforme necessário -->
                            </select>
                        </div>
                    </div>
                </div>
                <div class="btn-group-actions">
                    <button type="submit" class="btn-primary-custom">
                        <i class="fas fa-search"></i> Consultar
                    </button>
                    <a href="relatorio_hospede.php" class="btn-secondary-custom">
                        <i class="fas fa-arrow-left"></i> Voltar ao Relatório
                    </a>
                </div>
            </form>
            <?php if ($_SERVER['REQUEST_METHOD'] === 'GET' && $pesquisa && $tipo): ?>
                <div class="table-container" style="margin-top: 30px;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Reserva</th>
                                <th>Nome</th>
                                <th>Email</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Quarto</th>
                                <th>Status</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resultados as $r): ?>
                                <tr>
                                    <td>#<?= htmlspecialchars($r['idreserva'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($r['nome'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
                                    <td><?= isset($r['data_checkin_previsto']) ? date('d/m/Y', strtotime($r['data_checkin_previsto'])) : '' ?></td>
                                    <td><?= isset($r['data_checkout_previsto']) ? date('d/m/Y', strtotime($r['data_checkout_previsto'])) : '' ?></td>
                                    <td><?= htmlspecialchars($r['quarto_numero'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($r['status'] ?? '') ?></td>
                                    <td>R$ <?= isset($r['valor_reserva']) ? number_format($r['valor_reserva'], 2, ',', '.') : '' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (empty($resultados)): ?>
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h3>Nenhum resultado encontrado</h3>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
