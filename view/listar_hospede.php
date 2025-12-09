<?php

require_once __DIR__ . '/../controller/HospedeController.php';

use Controller\HospedeController;

session_start();

$mensagem = '';
$erros = [];
$hospedes = [];

$controller = new HospedeController();

// Se for DELETE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $resultado = $controller->deletar($_POST['id'] ?? 0);
    if ($resultado['sucesso']) {
        $mensagem = $resultado['mensagem'];
    } else {
        $erros = $resultado['erros'];
    }
}

$pesquisa = $_GET['pesquisa'] ?? '';

// Buscar hóspedes (com filtro de pesquisa)
if (!empty($pesquisa) && strlen($pesquisa) >= 3) {
    $resultado = $controller->pesquisar($pesquisa);
} else {
    $resultado = $controller->lista();
}

if ($resultado['sucesso']) {
    $hospedes = $resultado['dados'];
} else {
    $erros = $resultado['erros'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Hóspedes - Palácio Lumière</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Estilos para a página de listar hóspedes */
        .actions-search-wrapper {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .btn-group-actions {
            display: flex !important;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center !important;
        }
        .search-form-hospede {
            display: flex !important;
            align-items: center !important;
            gap: 8px;
        }
        .search-input-custom {
            padding: 0 16px !important;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
            min-width: 220px;
            background: #f9f9f9;
            color: #6B5111;
            height: 44px !important;
            box-sizing: border-box !important;
            line-height: normal !important;
        }
        .btn-search-custom {
            background: #FBBD24 !important;
            color: #6B5111 !important;
            border: none;
            border-radius: 6px;
            padding: 0 20px !important;
            font-size: 1rem;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.2s;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px;
            height: 44px !important;
            box-sizing: border-box !important;
            white-space: nowrap;
        }
        .btn-search-custom:hover {
            background: #e6a82a !important;
        }
        
        /* Garantir que os botões principais tenham a mesma altura */
        .btn-primary-custom,
        .btn-secondary-custom {
            height: 44px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 8px;
            box-sizing: border-box !important;
            padding: 0 20px !important;
            white-space: nowrap;
        }
    </style>
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
                <h1><i class="fas fa-list"></i> Listar Hóspedes</h1>
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
                            <?php if (count($erros) === 1): ?>
                                <?= htmlspecialchars($erros[0]) ?>
                            <?php else: ?>
                                <strong>Atenção:</strong>
                                <ul style="margin-top: 8px;">
                                    <?php foreach ($erros as $erro): ?>
                                        <li><?= htmlspecialchars($erro) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Botões de Ação à esquerda, Pesquisa à direita -->
                <div class="actions-search-wrapper">
                    <div class="btn-group-actions">
                        <a href="cadastrar_hospede.php" class="btn-primary-custom">
                            <i class="fas fa-plus-circle"></i> Novo Hóspede
                        </a>
                        <a href="../index.php" class="btn-secondary-custom">
                            <i class="fas fa-home"></i> Voltar ao Painel
                        </a>
                    </div>
                    <form method="GET" class="search-form-hospede" action="">
                        <input 
                            type="text" 
                            name="pesquisa" 
                            class="search-input-custom" 
                            placeholder="Pesquisar" 
                            value="<?= htmlspecialchars($pesquisa) ?>"
                            minlength="3"
                            title="Digite ao menos 3 caracteres para pesquisar"
                        >
                        <button type="submit" class="btn-search-custom">
                            <i class="fas fa-search"></i> Pesquisar
                        </button>
                    </form>
                </div>

                <!-- Tabela de Hóspedes -->
                <?php if (!empty($hospedes)): ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag"></i> ID</th>
                                    <th><i class="fas fa-user"></i> Nome</th>
                                    <th><i class="fas fa-id-card"></i> CPF</th>
                                    <th><i class="fas fa-envelope"></i> Email</th>
                                    <th><i class="fas fa-phone"></i> Telefone</th>
                                    <th><i class="fas fa-calendar"></i> Data Nasc.</th>
                                    <th class="text-center"><i class="fas fa-cog"></i> Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($hospedes as $hospede): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($hospede['id'] ?? $hospede['id_pessoa'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($hospede['nome'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($hospede['documento'] ?? $hospede['cpf'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($hospede['email'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($hospede['telefone'] ?? '') ?></td>
                                        <td class="text-center">
                                            <?php 
                                            if (!empty($hospede['data_nascimento'])) {
                                                echo date('d/m/Y', strtotime($hospede['data_nascimento']));
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td class="table-actions">
                                            <a href="editar_hospede.php?id=<?= $hospede['id'] ?? $hospede['id_pessoa'] ?>" 
                                               class="btn-action btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="" style="display: inline;" 
                                                  onsubmit="return confirmarExclusao('<?= htmlspecialchars($hospede['nome']) ?>');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $hospede['id'] ?? $hospede['id_pessoa'] ?>">
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
                        <p>Total de hóspedes: <strong><?= count($hospedes) ?></strong></p>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h3>Nenhum hóspede cadastrado</h3>
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
    
    function confirmarExclusao(nomeHospede) {
        return confirm(
            'ATENÇÃO: Tem certeza que deseja deletar o hóspede "' + nomeHospede + '"?\n\n' +
            '⚠️ Esta ação NÃO pode ser desfeita!\n\n' +
            '⚠️ Se este hóspede tiver reservas vinculadas, a exclusão será bloqueada.\n\n' +
            'Clique em OK para confirmar a exclusão.'
        );
    }
    </script>
</body>
</html>