<?php
require_once __DIR__ . '../controller/HospedeController.php';
require_once __DIR__ . '../utils/Formatter.php';

$controller = new HospedeController();
$resultado = $controller->listar();

$hospedes = $resultado['sucesso'] ? $resultado['dados'] : [];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Hóspedes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Lista de Hóspedes</h2>
            <div>
                <a href="cadastrar_hospede.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Novo Hóspede
                </a>
                <a href="../index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-house"></i> Menu
                </a>
            </div>
        </div>

        <?php if (empty($hospedes)): ?>
            <div class="alert alert-info">
                Nenhum hóspede cadastrado ainda.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>CPF</th>
                            <th>Email</th>
                            <th>Telefone</th>
                            <th>Cadastro</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hospedes as $hospede): ?>
                            <tr>
                                <td><?= htmlspecialchars($hospede['id']) ?></td>
                                <td><?= htmlspecialchars($hospede['nome']) ?></td>
                                <td><?= Formatter::formatarCPF($hospede['cpf']) ?></td>
                                <td><?= htmlspecialchars($hospede['email']) ?></td>
                                <td><?= Formatter::formatarTelefone($hospede['telefone']) ?></td>
                                <td><?= Formatter::formatarData($hospede['data_criacao']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="editar_hospede.php?id=<?= $hospede['id'] ?>" 
                                           class="btn btn-warning" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="historico_hospede.php?id=<?= $hospede['id'] ?>" 
                                           class="btn btn-info" title="Histórico">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
                                        <button onclick="confirmarExclusao(<?= $hospede['id'] ?>)" 
                                                class="btn btn-danger" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-light">
                <strong>Total:</strong> <?= count($hospedes) ?> hóspede(s) cadastrado(s)
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarExclusao(id) {
            if (confirm('Tem certeza que deseja excluir este hóspede?\n\nAtenção: Não será possível excluir se houver reservas ativas.')) {
                window.location.href = 'deletar_hospede.php?id=' + id;
            }
        }
    </script>
</body>
</html>