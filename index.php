<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gerenciamento Hoteleiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
          background-color: #a8d5ba;
min-height: 100vh;


            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .menu-card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .menu-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .header-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="header-card p-4 mb-5 text-center">
            <h1 class="display-4 fw-bold text-primary">
                <i class="bi bi-building"></i> Sistema de Gerenciamento Hoteleiro
            </h1>
            <p class="lead text-muted">Gerencie hóspedes, funcionarios, quartos e reservas</p>
        </div>

        <div class="row g-4">
            <!-- HÓSPEDES -->
            <div class="col-md-6 col-lg-3">
                <div class="card menu-card border-0 shadow">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-person-circle text-primary menu-icon"></i>
                        <h4 class="card-title">Hóspedes</h4>
                        <p class="card-text text-muted">Gerencie cadastro de hóspedes</p>
                        <div class="d-grid gap-2">
                            <a href="view/cadastrar_hospede.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Cadastrar
                            </a>
                            <a href="view/listar_hospede.php" class="btn btn-outline-primary">
                                <i class="bi bi-list-ul"></i> Listar
                            </a>
                            </a>
                             <a href="view/deletar_hospede.php" class="btn btn-outline-primary">
                                <i class="bi bi-trash"></i> Deletar
                            </a>
                             <a href="view/editar_hospede.php" class="btn btn-outline-primary">
                                <i class="bi bi-pencil-square"></i> Editar
                            </a>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FUNCIONÁRIOS -->
            <div class="col-md-6 col-lg-3">
                <div class="card menu-card border-0 shadow">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-person-badge text-success menu-icon"></i>
                        <h4 class="card-title">Funcionarios</h4>
                        <p class="card-text text-muted">Gerencie equipe do hotel</p>
                        <div class="d-grid gap-2">
                            <a href="view/cadastrar_funcionario.php" class="btn btn-success">
                                <i class="bi bi-plus-circle"></i>Cadastrar
                            </a>
                            <a href="view/lista_funcionario.php" class="btn btn-outline-success">
                                <i class="bi bi-list-ul"></i> Listar
                            </a>
                             <a href="view/deletar_funcionario.php" class="btn btn-outline-success">
                                <i class="bi bi-trash"></i> Deletar
                            </a>
                             <a href="view/editar_funcionario.php" class="btn btn-outline-success">
                                <i class="bi bi-pencil-square"></i> Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QUARTOS -->
            <div class="col-md-6 col-lg-3">
                <div class="card menu-card border-0 shadow">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-door-open text-warning menu-icon"></i>
                        <h4 class="card-title">Quartos</h4>
                        <p class="card-text text-muted">Gerencie quartos disponíveis</p>
                        <div class="d-grid gap-2">
                            <a href="view/cadastrar_quarto.php" class="btn btn-warning">
                                <i class="bi bi-plus-circle"></i> Cadastrar
                            </a>
                            <a href="view/lista_quartos.php" class="btn btn-outline-warning">
                                <i class="bi bi-list-ul"></i> Listar
                            </a>
                            <a href="view/deletar_quarto.php" class="btn btn-outline-warning">
                                <i class="bi bi-trash"></i> Deletar
                            </a>
                            <a href="view/editar_quartos.php" class="btn btn-outline-warning">
                                <i class="bi bi-pencil-square"></i> Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RESERVAS -->
            <div class="col-md-6 col-lg-3">
                <div class="card menu-card border-0 shadow">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-calendar-check text-danger menu-icon"></i>
                        <h4 class="card-title">Reservas</h4>
                        <p class="card-text text-muted">Gerencie reservas do hotel</p>
                        <div class="d-grid gap-2">
                            <a href="view/criar_reserva.php" class="btn btn-danger">
                                <i class="bi bi-plus-circle"></i> Nova Reserva
                            </a>
                            <a href="view/lista_reservas.php" class="btn btn-outline-danger">
                                <i class="bi bi-list-ul"></i> Listar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>