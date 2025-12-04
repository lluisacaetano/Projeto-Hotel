<?php
require_once __DIR__ . '/../controller/FuncionarioController.php';
require_once __DIR__ . '/../utils/Formatter.php';

use Controller\FuncionarioController;

session_start();

$mensagem = '';
$erros = [];
$funcionarios = [];

$controller = new FuncionarioController();

// Se for DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $resultado = $controller->deletar($_POST['id'] ?? 0);
    if ($resultado['sucesso']) {
        $mensagem = $resultado['mensagem'];
    } else {
        $erros = $resultado['erros'];
    }
}

// Buscar todos os funcionários
$resultado = $controller->lista();
if ($resultado['sucesso']) {
    $funcionarios = $resultado['dados'];
} else {
    $erros = $resultado['erros'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Funcionários - Palácio Lumière</title>
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
                <h1><i class="fas fa-list"></i> Listar Funcionários</h1>
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
                    <a href="cadastrar_funcionario.php" class="btn-primary-custom">
                        <i class="fas fa-plus-circle"></i> Novo Funcionário
                    </a>
                    <a href="../index.php" class="btn-secondary-custom">
                        <i class="fas fa-home"></i> Voltar ao Painel
                    </a>
                </div>

                <!-- Tabela de Funcionários -->
                <?php if (!empty($funcionarios)): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> ID</th>
                                    <th><i class="fas fa-user"></i> Nome</th>
                                    <th><i class="fas fa-id-card"></i> CPF</th>
                                    <th><i class="fas fa-envelope"></i> Email</th>
                                    <th><i class="fas fa-briefcase"></i> Cargo</th>
                                    <th><i class="fas fa-clock"></i> Turno</th>
                                    <th><i class="fas fa-cog"></i> Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($funcionarios as $funcionario): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($funcionario['id'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($funcionario['nome'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($funcionario['cpf'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($funcionario['email'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($funcionario['cargo'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($funcionario['turno'] ?? '') ?></td>
                                        <td class="table-actions">
                                            <a href="editar_funcionario.php?id=<?= $funcionario['id'] ?>" 
                                               class="btn-action btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="" style="display: inline;" 
                                                  onsubmit="return confirm('Tem certeza que deseja deletar este funcionário?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $funcionario['id'] ?>">
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
                        <p>Total de funcionários: <strong><?= count($funcionarios) ?></strong></p>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Nenhum funcionário cadastrado</h3>
                        <p>Comece a adicionar funcionários ao seu hotel.</p>
                        <a href="cadastrar_funcionario.php" class="btn-primary-custom">
                            <i class="fas fa-plus-circle"></i> Cadastrar Primeiro Funcionário
                        </a>
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
