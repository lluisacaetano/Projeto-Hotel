<?php
require_once __DIR__ . '/../controller/HospedeController.php';
require_once __DIR__ . '/../model/Hospede.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../utils/Validacoes.php';
require_once __DIR__ . '/../utils/Formatter.php';

$mensagem = '';
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new HospedeController();
    $resultado = $controller->criar($_POST);
    
    if ($resultado['sucesso']) {
        $mensagem = $resultado['mensagem'];
        // Limpar formulário após sucesso
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
    <title>Cadastrar Hóspede</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <h2 class="mb-4">Cadastrar Hóspede</h2>
                
                <?php if ($mensagem): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= htmlspecialchars($mensagem) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($erros)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            <?php foreach ($erros as $erro): ?>
                                <li><?= htmlspecialchars($erro) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome Completo *</label>
                        <input type="text" class="form-control" id="nome" name="nome" 
                               value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cpf" class="form-label">CPF *</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" 
                                   placeholder="000.000.000-00"
                                   value="<?= htmlspecialchars($_POST['cpf'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                            <input type="date" class="form-control" id="data_nascimento" name="data_nascimento"
                                   value="<?= htmlspecialchars($_POST['data_nascimento'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">E-mail *</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="telefone" class="form-label">Telefone *</label>
                            <input type="text" class="form-control" id="telefone" name="telefone"
                                   placeholder="(00) 00000-0000"
                                   value="<?= htmlspecialchars($_POST['telefone'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="endereco" class="form-label">Endereço</label>
                        <textarea class="form-control" id="endereco" name="endereco" rows="2"><?= htmlspecialchars($_POST['endereco'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Cadastrar</button>
                        <a href="listar_hospedes.php" class="btn btn-secondary">Ver Lista</a>
                        <a href="../index.php" class="btn btn-outline-secondary">Voltar ao Menu</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>