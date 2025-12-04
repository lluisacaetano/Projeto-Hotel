<?php

require_once __DIR__ . '/../controller/FuncionarioController.php';
require_once __DIR__ . '/../model/Funcionario.php';
require_once __DIR__ . '/../model/Pessoa.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../utils/Validacoes.php';
require_once __DIR__ . '/../utils/Formatter.php';

use Controller\FuncionarioController;

session_start();

$mensagem = '';
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new FuncionarioController();
    
    // Remover acentos dos valores antes de enviar
    $dados = $_POST;
    $dados['cargo'] = str_replace(['á', 'é', 'í', 'ó', 'ú'], ['a', 'e', 'i', 'o', 'u'], $dados['cargo']);
    $dados['turno'] = str_replace(['ã', 'á', 'é', 'í', 'ó', 'ú'], ['a', 'a', 'e', 'i', 'o', 'u'], $dados['turno']);
    
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
    <title>Cadastrar Funcionário - Palácio Lumière</title>
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
                <h1><i class="fas fa-user-tie"></i> Cadastrar Funcionário</h1>
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
                    <!-- Dados Pessoais -->
                    <h3 class="form-section-title">
                        <i class="fas fa-id-card"></i> Dados Pessoais
                    </h3>

                    <div class="form-group">
                        <label for="nome" class="form-label required-field">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" 
                               value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cpf" class="form-label required-field">CPF</label>
                                <input type="text" class="form-control" id="cpf" name="cpf" 
                                       placeholder="000.000.000-00" maxlength="14"
                                       value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control" id="data_nascimento" name="data_nascimento"
                                       value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sexo" class="form-label">Sexo</label>
                                <select class="form-select" id="sexo" name="sexo">
                                    <option value="">Selecione...</option>
                                    <option value="M" <?= ($_POST['sexo'] ?? '') == 'M' ? 'selected' : '' ?>>Masculino</option>
                                    <option value="F" <?= ($_POST['sexo'] ?? '') == 'F' ? 'selected' : '' ?>>Feminino</option>
                                    <option value="Outro" <?= ($_POST['sexo'] ?? '') == 'Outro' ? 'selected' : '' ?>>Outro</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label required-field">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       placeholder="exemplo@email.com"
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                <small class="text-muted" id="emailError" style="color: #d32f2f; display: none;">
                                    <i class="fas fa-exclamation-circle"></i> Email inválido. Deve conter "@"
                                </small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefone" class="form-label required-field">Telefone</label>
                                <input type="text" class="form-control" id="telefone" name="telefone"
                                       placeholder="(00) 00000-0000" maxlength="15"
                                       value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Dados Profissionais -->
                    <h3 class="form-section-title">
                        <i class="fas fa-briefcase"></i> Dados Profissionais
                    </h3>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cargo" class="form-label">Cargo</label>
                                <select class="form-select" id="cargo" name="cargo">
                                    <option value="">Selecione...</option>
                                    <option value="Recepcionista" <?= ($_POST['cargo'] ?? '') == 'Recepcionista' ? 'selected' : '' ?>>Recepcionista</option>
                                    <option value="Gerente" <?= ($_POST['cargo'] ?? '') == 'Gerente' ? 'selected' : '' ?>>Gerente</option>
                                    <option value="Camareira" <?= ($_POST['cargo'] ?? '') == 'Camareira' ? 'selected' : '' ?>>Camareira</option>
                                    <option value="Seguranca" <?= ($_POST['cargo'] ?? '') == 'Seguranca' ? 'selected' : '' ?>>Segurança</option>
                                    <option value="Manutencao" <?= ($_POST['cargo'] ?? '') == 'Manutencao' ? 'selected' : '' ?>>Manutenção</option>
                                    <option value="Chef" <?= ($_POST['cargo'] ?? '') == 'Chef' ? 'selected' : '' ?>>Chef</option>
                                    <option value="Garcom" <?= ($_POST['cargo'] ?? '') == 'Garcom' ? 'selected' : '' ?>>Garçom</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="salario" class="form-label">Salário (R$)</label>
                                <input type="text" class="form-control" id="salario" name="salario"
                                       placeholder="R$ 0,00"
                                       value="<?= htmlspecialchars($_POST['salario'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="turno" class="form-label">Turno</label>
                                <select class="form-select" id="turno" name="turno">
                                    <option value="">Selecione...</option>
                                    <option value="Manha" <?= ($_POST['turno'] ?? '') == 'Manha' ? 'selected' : '' ?>>Manhã</option>
                                    <option value="Tarde" <?= ($_POST['turno'] ?? '') == 'Tarde' ? 'selected' : '' ?>>Tarde</option>
                                    <option value="Noite" <?= ($_POST['turno'] ?? '') == 'Noite' ? 'selected' : '' ?>>Noite</option>
                                    <option value="Integral" <?= ($_POST['turno'] ?? '') == 'Integral' ? 'selected' : '' ?>>Integral</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="data_contratacao" class="form-label">Data de Contratação</label>
                                <input type="date" class="form-control" id="data_contratacao" name="data_contratacao"
                                       value="<?= htmlspecialchars($_POST['data_contratacao'] ?? date('Y-m-d')) ?>">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="numero_ctps" class="form-label">Número CTPS</label>
                                <input type="text" class="form-control" id="numero_ctps" name="numero_ctps"
                                    placeholder="0000000/0000" maxlength="12"
                                    value="<?= htmlspecialchars($_POST['numero_ctps'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Endereço -->
                    <h3 class="form-section-title">
                        <i class="fas fa-map-marker-alt"></i> Endereço
                    </h3>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="cep" class="form-label">CEP</label>
                                <input type="text" class="form-control" id="cep" name="cep"
                                       placeholder="00000-000" maxlength="9"
                                       value="<?= htmlspecialchars($_POST['cep'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="endereco" class="form-label">Rua/Logradouro</label>
                                <input type="text" class="form-control" id="endereco" name="endereco"
                                       value="<?= htmlspecialchars($_POST['endereco'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="numero" class="form-label">Número</label>
                                <input type="text" class="form-control" id="numero" name="numero"
                                       value="<?= htmlspecialchars($_POST['numero'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="bairro" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="bairro" name="bairro"
                                       value="<?= htmlspecialchars($_POST['bairro'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="cidade" class="form-label required-field">Cidade</label>
                                <input type="text" class="form-control" id="cidade" name="cidade"
                                       value="<?= htmlspecialchars($_POST['cidade'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="estado" class="form-label required-field">Estado (UF)</label>
                                <input type="text" class="form-control" id="estado" name="estado"
                                       placeholder="Ex: MG" maxlength="2"
                                       value="<?= htmlspecialchars($_POST['estado'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- Botões -->
                    <div class="btn-group-actions">
                        <button type="submit" class="btn-primary-custom">
                            <i class="fas fa-save"></i> Cadastrar Funcionário
                        </button>
                        <a href="lista_funcionario.php" class="btn-secondary-custom">
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

    // Validação de Email
    document.getElementById('email').addEventListener('blur', function(e) {
        const email = e.target.value;
        const errorSpan = document.getElementById('emailError');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email && !emailRegex.test(email)) {
            errorSpan.style.display = 'block';
            e.target.style.borderColor = '#d32f2f';
            e.target.style.boxShadow = '0 0 0 3px rgba(211, 47, 47, 0.2)';
        } else {
            errorSpan.style.display = 'none';
            e.target.style.borderColor = '#ddd';
            e.target.style.boxShadow = 'none';
        }
    });

    // Validar ao submeter o formulário
    document.querySelector('form').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email && !emailRegex.test(email)) {
            e.preventDefault();
            document.getElementById('emailError').style.display = 'block';
            document.getElementById('email').focus();
        }
    });

    // Formatação de CPF
    document.getElementById('cpf').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        
        if (value.length > 6) {
            value = value.slice(0, 3) + '.' + value.slice(3, 6) + '.' + value.slice(6, 9) + '-' + value.slice(9);
        } else if (value.length > 3) {
            value = value.slice(0, 3) + '.' + value.slice(3, 6) + (value.length > 6 ? '.' + value.slice(6) : '');
        } else if (value.length > 0) {
            value = value;
        }
        
        e.target.value = value;
    });

    // Formatação de Telefone
    document.getElementById('telefone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        
        if (value.length > 6) {
            value = '(' + value.slice(0, 2) + ') ' + value.slice(2, 7) + '-' + value.slice(7);
        } else if (value.length > 2) {
            value = '(' + value.slice(0, 2) + ') ' + value.slice(2);
        } else if (value.length > 0) {
            value = '(' + value;
        }
        
        e.target.value = value;
    });

    // Formatação de CEP
    document.getElementById('cep').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 8) value = value.slice(0, 8);
        
        if (value.length > 5) {
            value = value.slice(0, 5) + '-' + value.slice(5);
        }
        
        e.target.value = value;
    });

    // Formatação de Salário: R$ 1.234,56
    document.getElementById('salario').addEventListener('input', function(e) {
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

    // Formatação de CTPS: 0000000/0000
    document.getElementById('numero_ctps').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 11) value = value.slice(0, 11);
        
        if (value.length > 7) {
            value = value.slice(0, 7) + '/' + value.slice(7);
        }
        
        e.target.value = value;
    });
    </script>
</body>
</html>