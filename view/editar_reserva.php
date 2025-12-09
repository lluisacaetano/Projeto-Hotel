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
$reserva = null;

// Verificar se ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: lista_reservas.php');
    exit;
}

$id = (int)$_GET['id'];

// Buscar dados para os selects
try {
    $reservaController = new ReservaController();
    $hospedeController = new HospedeController();
    $quartoController = new QuartoController();
    $funcionarioController = new FuncionarioController();

    // Buscar reserva
    $resultado = $reservaController->buscarPorId($id);
    if (!$resultado['sucesso']) {
        $_SESSION['mensagem_erro'] = 'Reserva não encontrada.';
        header('Location: lista_reservas.php');
        exit;
    }
    $reserva = $resultado['dados'];

    // NOVO: Atualiza status para "em andamento" se dentro do período vigente
    $hoje = date('Y-m-d');
    if (
        isset($reserva['data_checkin_previsto'], $reserva['data_checkout_previsto']) &&
        $reserva['status'] !== 'em andamento'
    ) {
        $checkin = $reserva['data_checkin_previsto'];
        $checkout = $reserva['data_checkout_previsto'];
        if ($checkin <= $hoje && $checkout >= $hoje) {
            // Atualiza no banco e na variável local
            $reservaController->atualizar($id, array_merge($reserva, ['status' => 'em andamento']));
            $resultado = $reservaController->buscarPorId($id);
            $reserva = $resultado['dados'];
        }
    }

    // Buscar dados para os selects
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
    $dados = [
        'hospede_id' => $_POST['hospede_id'] ?? 0,
        'quarto_id' => $_POST['quarto_id'] ?? 0,
        'funcionario_id' => $_POST['funcionario_id'] ?? 0,
        'data_checkin_previsto' => $_POST['data_checkin'] ?? '',
        'data_checkout_previsto' => $_POST['data_checkout'] ?? '',
        'num_hospedes' => $_POST['num_hospedes'] ?? 1,
        'status' => $_POST['status'] ?? 'pendente',
        'observacoes' => $_POST['observacoes'] ?? null
    ];
    
    $resultado = $reservaController->atualizar($id, $dados);
    
    if ($resultado['sucesso']) {
        $mensagem = $resultado['mensagem'];
        // Recarregar dados da reserva
        $resultado = $reservaController->buscarPorId($id);
        $reserva = $resultado['dados'];
    } else {
        $erros = $resultado['erros'];
    }
}

$isFinalizada = in_array($reserva['status'] ?? '', ['finalizada', 'cancelada']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Reserva - Palácio Lumière</title>
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
                <h1><i class="fas fa-edit"></i> Editar Reserva #<?= $id ?></h1>
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
                                <select class="form-select" id="hospede_id" name="hospede_id" required <?= $isFinalizada ? 'disabled' : '' ?>>
                                    <option value="">Selecione um hóspede...</option>
                                    <?php foreach ($hospedes as $hospede): ?>
                                        <option value="<?= $hospede['id'] ?>" <?= $reserva['id_hospede'] == $hospede['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($hospede['nome']) ?> (<?= htmlspecialchars($hospede['telefone'] ?? '') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quarto_id" class="form-label required-field">Quarto</label>
                                <select class="form-select" id="quarto_id" name="quarto_id" required <?= $isFinalizada ? 'disabled' : '' ?>>
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
                                <select class="form-select" id="funcionario_id" name="funcionario_id" required <?= $isFinalizada ? 'disabled' : '' ?>>
                                    <option value="">Selecione um funcionário...</option>
                                    <?php foreach ($funcionarios as $funcionario): ?>
                                        <option value="<?= $funcionario['id'] ?>" <?= $reserva['id_funcionario'] == $funcionario['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($funcionario['nome']) ?> - <?= htmlspecialchars($funcionario['cargo'] ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status" class="form-label required-field">Status</label>
                                <select class="form-select" id="status" name="status" required <?= $isFinalizada ? 'disabled' : '' ?>>
                                    <option value="pendente" <?= $reserva['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                    <option value="confirmada" <?= $reserva['status'] == 'confirmada' ? 'selected' : '' ?>>Confirmada</option>
                                    <option value="cancelada" <?= $reserva['status'] == 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                                    <?php if ($reserva['status'] == 'em andamento'): ?>
                                        <option value="em andamento" selected>Em Andamento</option>
                                    <?php endif; ?>
                                    <option value="finalizada" <?= $reserva['status'] == 'finalizada' ? 'selected' : '' ?>>Finalizada</option>
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
                                       value="<?= htmlspecialchars($reserva['data_checkin_previsto']) ?>" required <?= $isFinalizada ? 'disabled' : '' ?>>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="data_checkout" class="form-label required-field">Data de Check-out</label>
                                <input type="date" class="form-control" id="data_checkout" name="data_checkout"
                                       value="<?= htmlspecialchars($reserva['data_checkout_previsto']) ?>" required <?= $isFinalizada ? 'disabled' : '' ?>>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="num_hospedes" class="form-label required-field">Nº de Hóspedes</label>
                                <input type="number" class="form-control" id="num_hospedes" name="num_hospedes"
                                       min="1" max="10" value="<?= htmlspecialchars($reserva['num_hospedes'] ?? '1') ?>" required <?= $isFinalizada ? 'disabled' : '' ?>>
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
                                  <?= $isFinalizada ? 'disabled' : '' ?>
                                  placeholder="Ex: Preferências especiais, requisições, notas importantes..."><?= htmlspecialchars($reserva['observacoes'] ?? '') ?></textarea>
                    </div>

                    <!-- Botões -->
                    <div class="btn-group-actions">
                        <?php if (!$isFinalizada): ?>
                            <button type="submit" class="btn-primary-custom">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                        <?php endif; ?>
                        <a href="lista_reservas.php" class="btn-secondary-custom">
                            <i class="fas fa-list"></i> Ver Lista
                        </a>
                        <a href="../index.php" class="btn-secondary-custom">
                            <i class="fas fa-home"></i> Voltar ao Painel
                        </a>
                        <?php if (!$isFinalizada): ?>
                            <button type="button" class="btn-secondary-custom" 
                                    style="background-color: #ffebee; color: #d32f2f; border-color: #ffebee; margin-left: auto;"
                                    onclick="confirmarExclusao(<?= $id ?>, '<?= htmlspecialchars($reserva['hospede_nome']) ?>')">
                                <i class="fas fa-trash"></i> Excluir Reserva
                            </button>
                        <?php endif; ?>
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

function confirmarExclusao(id, hospede) {
    if (confirm('⚠️ ATENÇÃO!\n\nTem certeza que deseja EXCLUIR a reserva do hóspede "' + hospede + '"?\n\nEsta ação NÃO pode ser desfeita!')) {
        window.location.href = 'deletar_reserva.php?id=' + id;
    }
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

    precoInput.value = 'R$ ' + preco.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    noitesInput.value = noites;
    valorTotalInput.value = 'R$ ' + valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Validação de Data de Check-out
document.getElementById('data_checkout').addEventListener('blur', function() {
    if (!this.value) return;
    
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

// Atualizar preço ao mudar datas
document.getElementById('data_checkin').addEventListener('change', atualizarPreco);
document.getElementById('data_checkout').addEventListener('change', atualizarPreco);

// Carregar valores ao carregar a página
document.addEventListener('DOMContentLoaded', function() {
    atualizarPreco();
});

// Validação final ao submeter
document.querySelector('form').addEventListener('submit', function(e) {
    const dataCheckin = document.getElementById('data_checkin').value;
    const dataCheckout = document.getElementById('data_checkout').value;

    if (!dataCheckin || !dataCheckout) {
        e.preventDefault();
        alert('⚠️ Por favor, preencha as datas de check-in e check-out!');
        return false;
    }

    const checkin = new Date(dataCheckin + 'T00:00:00');
    const checkout = new Date(dataCheckout + 'T00:00:00');

    if (checkout <= checkin) {
        e.preventDefault();
        alert('⚠️ A data de check-out deve ser pelo menos 1 dia após o check-in!');
        document.getElementById('data_checkout').focus();
        document.getElementById('data_checkout').style.borderColor = '#d32f2f';
        return false;
    }

    return true;
});

function habilitarQuartoESincronizarEditar() {
    const checkin = document.getElementById('data_checkin').value;
    const checkout = document.getElementById('data_checkout').value;
    const quartoSelect = document.getElementById('quarto_id');
    const quartoAtual = "<?= $reserva['id_quarto'] ?>";

    if (checkin && checkout) {
        quartoSelect.disabled = true;
        quartoSelect.innerHTML = '<option value="">Carregando quartos disponíveis...</option>';

        fetch('../ajax_quartos_disponiveis.php?checkin=' + encodeURIComponent(checkin) + '&checkout=' + encodeURIComponent(checkout) + '&reserva_id=<?= $id ?>')
            .then(res => res.json())
            .then(data => {
                quartoSelect.innerHTML = '';
                let quartoDisponivel = false;
                if (Array.isArray(data)) {
                    if (data.length === 0) {
                        quartoSelect.innerHTML = '<option value="">Nenhum quarto disponível</option>';
                    } else {
                        quartoSelect.innerHTML = '<option value="">Selecione um quarto...</option>';
                        data.forEach(function(q) {
                            const selected = (q.id_quarto == quartoAtual) ? 'selected' : '';
                            if (selected) quartoDisponivel = true;
                            quartoSelect.innerHTML += `<option value="${q.id_quarto}" data-preco="${q.valor_diaria}" data-tipo="${q.tipo_quarto}" data-numero="${q.numero}" ${selected}>
                                Quarto ${q.numero} - ${q.tipo_quarto} (R$ ${parseFloat(q.valor_diaria).toLocaleString('pt-BR', {minimumFractionDigits:2})}/dia)
                            </option>`;
                        });
                    }
                    quartoSelect.disabled = false;
                    if (!quartoDisponivel) quartoSelect.value = ""; // Desmarca se não disponível
                } else if (data.error) {
                    quartoSelect.innerHTML = '<option value="">Erro ao buscar quartos: ' + data.error + '</option>';
                    quartoSelect.disabled = true;
                }
                atualizarPreco();
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

document.getElementById('data_checkin').addEventListener('change', habilitarQuartoESincronizarEditar);
document.getElementById('data_checkout').addEventListener('change', habilitarQuartoESincronizarEditar);
document.getElementById('quarto_id').addEventListener('change', atualizarPreco);

// Habilitar quarto e sincronizar ao carregar
document.addEventListener('DOMContentLoaded', function() {
    habilitarQuartoESincronizarEditar();
});
</script>
</body>
</html>