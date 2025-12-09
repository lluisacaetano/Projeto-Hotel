<?php

require_once __DIR__ . '/../controller/ReservaController.php';
require_once __DIR__ . '/../controller/HospedeController.php';
require_once __DIR__ . '/../controller/QuartoController.php';
require_once __DIR__ . '/../controller/FuncionarioController.php';

use Controller\ReservaController;
use Controller\HospedeController;
use Controller\QuartoController;
use Controller\FuncionarioController;

session_start();

$mensagem = '';
$erros = [];

// Buscar dados para os selects
try {
    $hospedeController = new HospedeController();
    $quartoController = new QuartoController();
    $funcionarioController = new FuncionarioController();

    $hospedes_resultado = $hospedeController->lista();
    $hospedes = $hospedes_resultado['sucesso'] ? $hospedes_resultado['dados'] : [];

    $quartos_resultado = $quartoController->lista();
    $quartos = $quartos_resultado['sucesso'] ? $quartos_resultado['dados'] : [];

    $funcionarios_resultado = $funcionarioController->lista();
    $funcionarios = $funcionarios_resultado['sucesso'] ? $funcionarios_resultado['dados'] : [];
} catch (Exception $e) {
    $erros[] = "Erro ao carregar dados: " . $e->getMessage();
}

// Filtrar funcionários (apenas gerente e recepcionista)
$funcionarios = array_filter($funcionarios, function($f) {
    $cargo = strtolower($f['cargo'] ?? '');
    return $cargo === 'gerente' || $cargo === 'recepcionista';
});

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new ReservaController();
    
    $dados = [
        'hospede_id' => $_POST['hospede_id'] ?? 0,
        'quarto_id' => $_POST['quarto_id'] ?? 0,
        'funcionario_id' => $_POST['funcionario_id'] ?? 0,
        'data_checkin' => $_POST['data_checkin'] ?? '',
        'data_checkout' => $_POST['data_checkout'] ?? '',
        'num_hospedes' => $_POST['num_hospedes'] ?? 1,
        'status' => $_POST['status'] ?? 'pendente',
        'observacoes' => $_POST['observacoes'] ?? null
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
    <title>Criar Reserva - Palácio Lumière</title>
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
                <h1><i class="fas fa-calendar-check"></i> Nova Reserva</h1>
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
                    <!-- Seleção de Hóspede e Quarto -->
                    <h3 class="form-section-title">
                        <i class="fas fa-user-check"></i> Informações Principais
                    </h3>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="hospede_id" class="form-label required-field">Hóspede</label>
                                <select class="form-select" id="hospede_id" name="hospede_id" required>
                                    <option value="">Selecione um hóspede...</option>
                                    <?php foreach ($hospedes as $hospede): ?>
                                        <option value="<?= $hospede['id'] ?>" <?= ($_POST['hospede_id'] ?? '') == $hospede['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($hospede['nome']) ?> (<?= htmlspecialchars($hospede['telefone'] ?? '') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quarto_id" class="form-label required-field">Quarto</label>
                                <select class="form-select" id="quarto_id" name="quarto_id" required disabled>
                                    <option value="">Selecione um quarto...</option>
                                    <!-- Opções serão preenchidas via JS -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="funcionario_id" class="form-label required-field">Responsável</label>
                                <select class="form-select" id="funcionario_id" name="funcionario_id" required>
                                    <option value="">Selecione um funcionário...</option>
                                    <?php foreach ($funcionarios as $funcionario): ?>
                                        <option value="<?= $funcionario['id'] ?>" <?= ($_POST['funcionario_id'] ?? '') == $funcionario['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($funcionario['nome']) ?> - <?= htmlspecialchars($funcionario['cargo'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status" class="form-label required-field">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="pendente" <?= ($_POST['status'] ?? '') == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                    <option value="confirmada" <?= ($_POST['status'] ?? '') == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                                    <option value="cancelada" <?= ($_POST['status'] ?? '') == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Datas e Hóspedes -->
                    <h3 class="form-section-title">
                        <i class="fas fa-calendar-days"></i> Datas e Quantidade
                    </h3>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="data_checkin" class="form-label required-field">Data de Check-in</label>
                                <input type="date" class="form-control" id="data_checkin" name="data_checkin"
                                       value="<?= htmlspecialchars($_POST['data_checkin'] ?? '') ?>" required onchange="atualizarPreco()">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="data_checkout" class="form-label required-field">Data de Check-out</label>
                                <input type="date" class="form-control" id="data_checkout" name="data_checkout"
                                       value="<?= htmlspecialchars($_POST['data_checkout'] ?? '') ?>" required onchange="atualizarPreco()">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="num_hospedes" class="form-label required-field">Nº de Hóspedes</label>
                                <input type="number" class="form-control" id="num_hospedes" name="num_hospedes"
                                       min="1" max="10" value="<?= htmlspecialchars($_POST['num_hospedes'] ?? '1') ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Resumo Financeiro -->
                    <h3 class="form-section-title">
                        <i class="fas fa-money-bill-wave"></i> Resumo Financeiro
                    </h3>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Preço da Diária</label>
                                <input type="text" class="form-control" id="preco_diaria" readonly value="R$ 0,00" 
                                    style="background-color: #f8f9fa; font-weight: 500;">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Nº de Noites</label>
                                <input type="text" class="form-control" id="noites" readonly value="0"
                                    style="background-color: #f8f9fa; text-align: center; font-weight: 500;">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label"><strong>Valor Total</strong></label>
                                <input type="text" class="form-control" id="valor_total" readonly value="R$ 0,00" 
                                    style="font-weight: bold; font-size: 1.1rem; background-color: #e8f5e9; color: #2e7d32; text-align: right;">
                            </div>
                        </div>
                    </div>

                    <!-- Observações -->
                    <h3 class="form-section-title">
                        <i class="fas fa-note-sticky"></i> Observações
                    </h3>

                    <div class="form-group">
                        <label for="observacoes" class="form-label">Observações Adicionais</label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3"
                                  placeholder="Ex: Preferências especiais, requisições, notas importantes..."><?= htmlspecialchars($_POST['observacoes'] ?? '') ?></textarea>
                    </div>

                    <!-- Botões -->
                    <div class="btn-group-actions">
                        <button type="submit" class="btn-primary-custom">
                            <i class="fas fa-save"></i> Criar Reserva
                        </button>
                        <a href="lista_reservas.php" class="btn-secondary-custom">
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

function atualizarPreco() {
    const quartoSelect = document.getElementById('quarto_id');
    const dataCheckin = document.getElementById('data_checkin').value;
    const dataCheckout = document.getElementById('data_checkout').value;
    
    const precoInput = document.getElementById('preco_diaria');
    const noitesInput = document.getElementById('noites');
    const valorTotalInput = document.getElementById('valor_total');

    if (!quartoSelect.value || !dataCheckin || !dataCheckout) {
        precoInput.value = 'R$ 0,00';
        noitesInput.value = '0';
        valorTotalInput.value = 'R$ 0,00';
        return;
    }

    const opcaoSelecionada = quartoSelect.options[quartoSelect.selectedIndex];
    const preco = parseFloat(opcaoSelecionada.dataset.preco);

    const checkin = new Date(dataCheckin + 'T00:00:00');
    const checkout = new Date(dataCheckout + 'T00:00:00');
    const diffTime = checkout - checkin;
    const noites = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (noites < 1) {
        precoInput.value = 'R$ ' + preco.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        noitesInput.value = '0';
        valorTotalInput.value = 'R$ 0,00';
        return;
    }

    const valorTotal = preco * noites;

    // Formatar com R$ e separadores brasileiros
    precoInput.value = 'R$ ' + preco.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    noitesInput.value = noites;
    valorTotalInput.value = 'R$ ' + valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Função para habilitar o campo de quarto e sincronizar opções disponíveis
function habilitarQuartoESincronizar() {
    const checkin = document.getElementById('data_checkin').value;
    const checkout = document.getElementById('data_checkout').value;
    const quartoSelect = document.getElementById('quarto_id');

    if (checkin && checkout) {
        quartoSelect.disabled = true;
        quartoSelect.innerHTML = '<option value="">Carregando quartos disponíveis...</option>';

        fetch('../ajax_quartos_disponiveis.php?checkin=' + encodeURIComponent(checkin) + '&checkout=' + encodeURIComponent(checkout))
            .then(res => res.json())
            .then(data => {
                quartoSelect.innerHTML = '';
                if (Array.isArray(data)) {
                    if (data.length === 0) {
                        quartoSelect.innerHTML = '<option value="">Nenhum quarto disponível</option>';
                    } else {
                        quartoSelect.innerHTML = '<option value="">Selecione um quarto...</option>';
                        data.forEach(function(q) {
                            quartoSelect.innerHTML += `<option value="${q.id_quarto}" data-preco="${q.valor_diaria}" data-tipo="${q.tipo_quarto}" data-numero="${q.numero}">
                                Quarto ${q.numero} - ${q.tipo_quarto} (R$ ${parseFloat(q.valor_diaria).toLocaleString('pt-BR', {minimumFractionDigits:2})}/dia)
                            </option>`;
                        });
                    }
                    quartoSelect.disabled = false;
                } else if (data.error) {
                    quartoSelect.innerHTML = '<option value="">Erro ao buscar quartos: ' + data.error + '</option>';
                    quartoSelect.disabled = true;
                }
                atualizarPreco(); // Garante cálculo após AJAX
            })
            .catch(() => {
                quartoSelect.innerHTML = '<option value="">Erro ao buscar quartos</option>';
                quartoSelect.disabled = true;
                atualizarPreco();
            });
    } else {
        quartoSelect.disabled = true;
        quartoSelect.innerHTML = '<option value="">Selecione as datas primeiro</option>';
        atualizarPreco();
    }
}

document.getElementById('data_checkin').addEventListener('change', habilitarQuartoESincronizar);
document.getElementById('data_checkout').addEventListener('change', habilitarQuartoESincronizar);

// NOVO: Atualiza cálculo ao trocar o quarto
document.getElementById('quarto_id').addEventListener('change', atualizarPreco);

// Validação de Data de Check-in (apenas quando terminar de preencher)
document.getElementById('data_checkin').addEventListener('blur', function() {
    if (!this.value) return; // Não validar se campo vazio
    
    const dataCheckin = new Date(this.value + 'T00:00:00');
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);

    // Permitir check-in igual ao dia de hoje
    if (dataCheckin < hoje) {
        alert('⚠️ A data de check-in não pode ser anterior à data atual!');
        this.value = '';
        this.style.borderColor = '#d32f2f';
        atualizarPreco();
    } else {
        this.style.borderColor = '';
        // Atualizar data mínima do checkout
        const dataMinCheckout = new Date(dataCheckin);
        dataMinCheckout.setDate(dataMinCheckout.getDate() + 1);
        const dataMinFormatada = dataMinCheckout.toISOString().split('T')[0];
        document.getElementById('data_checkout').setAttribute('min', dataMinFormatada);
        atualizarPreco();
    }
});

// Validação de Data de Check-out (apenas quando terminar de preencher)
document.getElementById('data_checkout').addEventListener('blur', function() {
    if (!this.value) return; // Não validar se campo vazio
    
    const dataCheckin = document.getElementById('data_checkin').value;
    
    if (!dataCheckin) {
        alert('⚠️ Por favor, selecione primeiro a data de check-in!');
        this.value = '';
        this.style.borderColor = '#d32f2f';
        return;
    }

    const checkin = new Date(dataCheckin + 'T00:00:00');
    const checkout = new Date(this.value + 'T00:00:00');

    if (checkout <= checkin) {
        alert('⚠️ A data de check-out deve ser pelo menos 1 dia após o check-in!');
        this.value = '';
        this.style.borderColor = '#d32f2f';
        atualizarPreco();
    } else {
        this.style.borderColor = '';
        atualizarPreco();
    }
});

// Atualizar preço ao mudar datas (sem validar)
document.getElementById('data_checkin').addEventListener('change', function() {
    if (this.value) {
        atualizarPreco();
    }
});

document.getElementById('data_checkout').addEventListener('change', function() {
    if (this.value) {
        atualizarPreco();
    }
});

// Definir data mínima para check-in (hoje)
document.addEventListener('DOMContentLoaded', function() {
    const hoje = new Date();
    const dataFormatada = hoje.toISOString().split('T')[0];
    document.getElementById('data_checkin').setAttribute('min', dataFormatada);
    
    atualizarPreco();
});

// Validação final ao submeter o formulário
document.querySelector('form').addEventListener('submit', function(e) {
    const dataCheckin = document.getElementById('data_checkin').value;
    const dataCheckout = document.getElementById('data_checkout').value;

    if (!dataCheckin || !dataCheckout) {
        e.preventDefault();
        alert('⚠️ Por favor, preencha as datas de check-in e check-out!');
        return false;
    }

    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0);
    const checkin = new Date(dataCheckin + 'T00:00:00');
    const checkout = new Date(dataCheckout + 'T00:00:00');

    // Permitir check-in igual ao dia de hoje
    if (checkin < hoje) {
        e.preventDefault();
        alert('⚠️ A data de check-in não pode ser anterior à data atual!');
        document.getElementById('data_checkin').focus();
        document.getElementById('data_checkin').style.borderColor = '#d32f2f';
        return false;
    }

    // Validar checkout deve ser depois do checkin
    if (checkout <= checkin) {
        e.preventDefault();
        alert('⚠️ A data de check-out deve ser pelo menos 1 dia após o check-in!');
        document.getElementById('data_checkout').focus();
        document.getElementById('data_checkout').style.borderColor = '#d32f2f';
        return false;
    }

    return true;
});
</script>
</body>
</html>
