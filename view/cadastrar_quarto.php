<?php

use controller\QuartoController;
use database\Database;
use model\Quarto;

require_once __DIR__ . '/../controller/QuartoController.php';
require_once __DIR__ . '/../database/Database.php';  

$mensagem = '';
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new QuartoController();
    
    $dados = [
        'numero' => $_POST['numero'] ?? 0,
        'andar' => $_POST['andar'] ?? 0,
        'tipo_quarto' => $_POST['tipo_quarto'] ?? '',
        'valor_diaria' => $_POST['valor_diaria'] ?? 0,
        'capacidade_maxima' => $_POST['capacidade_maxima'] ?? 1,
        'descricao' => $_POST['descricao'] ?? null,
        'status' => $_POST['status'] ?? 'disponivel'
    ];
    
    $resultado = $controller->criar($dados);
    
    if ($resultado['sucesso']) {
        $mensagem = $resultado['mensagem'];
        $_POST = [];
    } else {
        $erros = $resultado['erros'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Quarto - Palácio Lumière</title>
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
                <h1><i class="fas fa-door-open"></i> Cadastrar Quarto</h1>
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
                                       value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="andar" class="form-label required-field">Andar</label>
                                <input type="number" class="form-control" id="andar" name="andar" 
                                       value="<?= htmlspecialchars($_POST['andar'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="capacidade_maxima" class="form-label required-field">Capacidade Máxima</label>
                                <input type="number" class="form-control" id="capacidade_maxima" name="capacidade_maxima" 
                                       min="1" max="10" value="<?= htmlspecialchars($_POST['capacidade_maxima'] ?? '2') ?>" required>
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
                                    <option value="Standard" <?= ($_POST['tipo_quarto'] ?? '') == 'Standard' ? 'selected' : '' ?>>Standard</option>
                                    <option value="Luxo" <?= ($_POST['tipo_quarto'] ?? '') == 'Luxo' ? 'selected' : '' ?>>Luxo</option>
                                    <option value="Suite" <?= ($_POST['tipo_quarto'] ?? '') == 'Suite' ? 'selected' : '' ?>>Suíte</option>
                                    <option value="Deluxe" <?= ($_POST['tipo_quarto'] ?? '') == 'Deluxe' ? 'selected' : '' ?>>Deluxe</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status" class="form-label required-field">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="disponivel" <?= ($_POST['status'] ?? '') == 'disponivel' ? 'selected' : '' ?>>Disponível</option>
                                    <option value="ocupado" <?= ($_POST['status'] ?? '') == 'ocupado' ? 'selected' : '' ?>>Ocupado</option>
                                    <option value="manutencao" <?= ($_POST['status'] ?? '') == 'manutencao' ? 'selected' : '' ?>>Manutenção</option>
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
                        <input type="text" class="form-control" id="valor_diaria" name="valor_diaria_display" 
                            placeholder="R$ 0,00"
                            value="<?= htmlspecialchars($_POST['valor_diaria'] ?? '') ?>" required>
                    </div>

                    <!-- Descrição -->
                    <h3 class="form-section-title">
                        <i class="fas fa-align-left"></i> Descrição
                    </h3>

                    <div class="form-group">
                        <label for="descricao" class="form-label">Descrição do Quarto</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="4"
                                  placeholder="Ex: Quarto com ar-condicionado, TV 32\", frigobar, Wi-Fi, vista para jardim..."><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
                    </div>

                    <!-- Botões -->
                    <div class="btn-group-actions">
                        <button type="submit" class="btn-primary-custom">
                            <i class="fas fa-save"></i> Cadastrar Quarto
                        </button>
                        <a href="lista_quartos.php" class="btn-secondary-custom">
                            <i class="fas fa-list"></i> Ver Lista
                        </a>
                        <a href="../index.php" class="btn-secondary-custom">
                            <i class="fas fa-home"></i> Voltar ao Painel
                        </a>
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

    // Permitir apenas números - Número do Quarto
    document.getElementById('numero').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    // Permitir apenas números - Andar
    document.getElementById('andar').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    // Permitir apenas números - Capacidade Máxima
    document.getElementById('capacidade_maxima').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    // Formatação de Valor da Diária: R$ 1.234,56 (IGUAL AO SALÁRIO)
    document.getElementById('valor_diaria').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length === 0) {
            e.target.value = '';
            return;
        }
        
        // Garante 2 casas decimais
        if (value.length <= 2) {
            value = ('00' + value).slice(-2);
        } else {
            value = value.slice(0, -2) + ',' + value.slice(-2);
        }
        
        // Adiciona separador de milhares
        const partes = value.split(',');
        partes[0] = partes[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        
        e.target.value = 'R$ ' + partes.join(',');
    });

    // Remover formatação antes de enviar
    document.querySelector('form').addEventListener('submit', function(e) {
        const valorInput = document.getElementById('valor_diaria');
        
        // Extrair apenas os números e converter
        let valor = valorInput.value.replace(/\D/g, '');
        if (valor) {
            valor = (parseInt(valor) / 100).toFixed(2);
        }
        
        // Criar input hidden com o valor correto
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'valor_diaria';
        hiddenInput.value = valor;
        
        valorInput.removeAttribute('name');
        
        this.appendChild(hiddenInput);
    });
    </script>
</body>
</html>