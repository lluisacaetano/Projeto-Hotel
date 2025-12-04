<?php

use controller\QuartoController;

require_once __DIR__ . '/../controller/QuartoController.php';

session_start();

$mensagem = '';
$erros = [];
$quarto = null;

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: lista_quartos.php');
    exit;
}

$id = (int)$_GET['id'];

$controller = new QuartoController();
$resultado = $controller->buscarPorId($id);

if (!$resultado['sucesso']) {
    $_SESSION['mensagem_erro'] = 'Quarto não encontrado.';
    header('Location: lista_quartos.php');
    exit;
}

$quarto = $resultado['dados'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultadoUpdate = $controller->atualizar($id, $_POST);
    
    if ($resultadoUpdate['sucesso']) {
        $mensagem = $resultadoUpdate['mensagem'];
        $resultado = $controller->buscarPorId($id);
        $quarto = $resultado['dados'];
    } else {
        $erros = $resultadoUpdate['erros'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Quarto - Palácio Lumière</title>
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
                <h1><i class="fas fa-edit"></i> Editar Quarto Nº <?= htmlspecialchars($quarto['numero']) ?></h1>
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

                <form method="POST" action="">
                    <!-- Informações Básicas -->
                    <h3 class="form-section-title">
                        <i class="fas fa-info-circle"></i> Informações Básicas
                    </h3>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="numero" class="form-label required-field">Número do Quarto</label>
                                <input type="number" class="form-control" id="numero" name="numero" 
                                       value="<?= htmlspecialchars($quarto['numero']) ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="andar" class="form-label required-field">Andar</label>
                                <input type="number" class="form-control" id="andar" name="andar" 
                                       value="<?= htmlspecialchars($quarto['andar']) ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacidade_maxima" class="form-label required-field">Capacidade Máxima</label>
                                <input type="number" class="form-control" id="capacidade_maxima" name="capacidade_maxima" 
                                       min="1" max="10" value="<?= htmlspecialchars($quarto['capacidade_maxima']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes do Quarto -->
                    <h3 class="form-section-title">
                        <i class="fas fa-bed"></i> Detalhes do Quarto
                    </h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tipo_quarto" class="form-label required-field">Tipo de Quarto</label>
                                <select class="form-select" id="tipo_quarto" name="tipo_quarto" required>
                                    <option value="">Selecione...</option>
                                    <option value="Standard" <?= ($quarto['tipo_quarto'] ?? '') == 'Standard' ? 'selected' : '' ?>>Standard</option>
                                    <option value="Luxo" <?= ($quarto['tipo_quarto'] ?? '') == 'Luxo' ? 'selected' : '' ?>>Luxo</option>
                                    <option value="Suite" <?= ($quarto['tipo_quarto'] ?? '') == 'Suite' ? 'selected' : '' ?>>Suíte</option>
                                    <option value="Deluxe" <?= ($quarto['tipo_quarto'] ?? '') == 'Deluxe' ? 'selected' : '' ?>>Deluxe</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status" class="form-label required-field">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="disponivel" <?= ($quarto['status'] ?? '') == 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                                    <option value="ocupado" <?= ($quarto['status'] ?? '') == 'ocupado' ? 'selected' : '' ?>>Ocupado</option>
                                    <option value="manutencao" <?= ($quarto['status'] ?? '') == 'manutencao' ? 'selected' : '' ?>>Manutenção</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Valor da Diária -->
                    <h3 class="form-section-title">
                        <i class="fas fa-money-bill-wave"></i> Valor da Diária
                    </h3>

                    <div class="form-group">
                        <label for="valor_diaria" class="form-label required-field">Valor (R$)</label>
                        <input type="number" class="form-control" id="valor_diaria" name="valor_diaria" 
                               step="0.01" min="0" value="<?= htmlspecialchars($quarto['valor_diaria']) ?>" required>
                    </div>

                    <!-- Descrição -->
                    <h3 class="form-section-title">
                        <i class="fas fa-align-left"></i> Descrição
                    </h3>

                    <div class="form-group">
                        <label for="descricao" class="form-label">Descrição do Quarto</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4"
                                  placeholder="Ex: Quarto com ar-condicionado, TV 32\", frigobar, Wi-Fi, vista para jardim..."><?= htmlspecialchars($quarto['descricao'] ?? '') ?></textarea>
                    </div>

                    <!-- Botões -->
                    <div class="btn-group-actions">
                        <button type="submit" class="btn-primary-custom">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <a href="lista_quartos.php" class="btn-secondary-custom">
                            <i class="fas fa-list"></i> Ver Lista
                        </a>
                        <a href="../index.php" class="btn-secondary-custom">
                            <i class="fas fa-home"></i> Voltar ao Painel
                        </a>
                        <button type="button" class="btn-secondary-custom" 
                                style="background-color: #ffebee; color: #d32f2f; border-color: #ffebee; margin-left: auto;"
                                onclick="confirmarExclusao(<?= $id ?>)">
                            <i class="fas fa-trash"></i> Excluir Quarto
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
    function toggleDropdown(element) {
        const menu = element.nextElementSibling;
        menu.classList.toggle('show');
        element.classList.toggle('active');
    }

    function confirmarExclusao(id) {
        if (confirm('⚠️ ATENÇÃO!\n\nTem certeza que deseja EXCLUIR este quarto?\n\nEsta ação NÃO pode ser desfeita!')) {
            window.location.href = 'deletar_quarto.php?id=' + id;
        }
    }
    </script>
</body>
</html>
