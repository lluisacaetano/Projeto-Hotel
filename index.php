<?php
require_once __DIR__ . '/database/Database.php';

use database\Database;

date_default_timezone_set('America/Sao_Paulo');
$hoje = date('Y-m-d');

try {
    $db = new Database();
    $conn = $db->getConnection();

    // ==========================================
    // ATUALIZAÇÃO AUTOMÁTICA DE RESERVAS ATRASADAS
    // ==========================================
    
    // Atualizar reservas com checkout vencido que ainda não foram finalizadas
    // Estas ficam "em atraso" até que o botão "Finalizar Reserva" seja acionado
    $sqlReservasAtrasadas = "SELECT idreserva, data_checkin_previsto, data_checkout_previsto, valor_reserva
                             FROM reserva 
                             WHERE DATE(data_checkout_previsto) < ? 
                             AND status = 'em andamento'";
    $stmtAtrasadas = $conn->prepare($sqlReservasAtrasadas);
    $stmtAtrasadas->execute([$hoje]);
    $reservasAtrasadas = $stmtAtrasadas->fetchAll();

    foreach ($reservasAtrasadas as $reserva) {
        // Calcular número de diárias originais
        $checkin = new DateTime($reserva['data_checkin_previsto']);
        $checkoutOriginal = new DateTime($reserva['data_checkout_previsto']);
        $diariasOriginais = $checkin->diff($checkoutOriginal)->days;
        
        // Calcular valor da diária
        $valorDiaria = $diariasOriginais > 0 ? $reserva['valor_reserva'] / $diariasOriginais : 0;
        
        // Calcular novas diárias até hoje
        $checkoutNovo = new DateTime($hoje);
        $novasDiarias = $checkin->diff($checkoutNovo)->days;
        
        // Calcular novo valor
        $novoValor = $valorDiaria * $novasDiarias;
        
        // Atualizar checkout para hoje e o valor
        $sqlUpdateAtrasada = "UPDATE reserva 
                              SET data_checkout_previsto = ?,
                                  valor_reserva = ?
                              WHERE idreserva = ?";
        $stmtUpdateAtrasada = $conn->prepare($sqlUpdateAtrasada);
        $stmtUpdateAtrasada->execute([$hoje, $novoValor, $reserva['idreserva']]);
    }

    // ==========================================
    // CARDS DO DASHBOARD
    // ==========================================

    // 1. CHECK-INS HOJE - TODAS as reservas com data_checkin_previsto = hoje
    $sqlCheckinsHoje = "SELECT COUNT(*) as total FROM reserva 
                        WHERE DATE(data_checkin_previsto) = ?";
    $stmtCheckinsHoje = $conn->prepare($sqlCheckinsHoje);
    $stmtCheckinsHoje->execute([$hoje]);
    $resultCheckinsHoje = $stmtCheckinsHoje->fetch();
    $checkins_hoje = $resultCheckinsHoje['total'] ?? 0;

    // 2. CHECK-OUTS HOJE - TODAS as reservas com data_checkout_previsto = hoje
    $sqlCheckoutsHoje = "SELECT COUNT(*) as total FROM reserva 
                         WHERE DATE(data_checkout_previsto) = ?";
    $stmtCheckoutsHoje = $conn->prepare($sqlCheckoutsHoje);
    $stmtCheckoutsHoje->execute([$hoje]);
    $resultCheckoutsHoje = $stmtCheckoutsHoje->fetch();
    $checkouts_hoje = $resultCheckoutsHoje['total'] ?? 0;

    // 3. TAXA DE OCUPAÇÃO - Quartos com status "em andamento"
    $sqlTotalQuartos = "SELECT COUNT(*) as total FROM quarto";
    $stmtTotalQuartos = $conn->prepare($sqlTotalQuartos);
    $stmtTotalQuartos->execute();
    $resultTotalQuartos = $stmtTotalQuartos->fetch();
    $total_quartos = $resultTotalQuartos['total'] ?? 1;

    // Contar quartos ocupados (reservas com status "em andamento")
    $sqlQuartosOcupados = "SELECT COUNT(DISTINCT id_quarto) as ocupados 
                           FROM reserva 
                           WHERE status = 'em andamento'";
    $stmtQuartosOcupados = $conn->prepare($sqlQuartosOcupados);
    $stmtQuartosOcupados->execute();
    $resultQuartosOcupados = $stmtQuartosOcupados->fetch();
    $ocupados = $resultQuartosOcupados['ocupados'] ?? 0;

    $taxa_ocupacao = $total_quartos > 0 ? ($ocupados / $total_quartos) * 100 : 0;

    // ==========================================
    // RESERVAS EM ANDAMENTO
    // ==========================================
    // Mostra APENAS reservas com status = 'em andamento'
    $sqlReservasAndamento = "SELECT r.*, 
                             p.nome as hospede_nome, 
                             p.email, 
                             p.telefone,
                             q.numero as quarto_numero, 
                             q.tipo_quarto
                             FROM reserva r
                             INNER JOIN hospede ho ON r.id_hospede = ho.id_pessoa
                             INNER JOIN pessoa p ON ho.id_pessoa = p.id_pessoa
                             INNER JOIN quarto q ON r.id_quarto = q.id_quarto
                             WHERE r.status = 'em andamento'
                             ORDER BY r.data_checkout_previsto ASC";
    $stmtReservasAndamento = $conn->prepare($sqlReservasAndamento);
    $stmtReservasAndamento->execute();
    $reservas_em_andamento = $stmtReservasAndamento->fetchAll();

    // ==========================================
    // PRÓXIMAS RESERVAS
    // ==========================================
    // Mostra reservas com status 'confirmada' ou 'pendente' (exceto as em andamento)
    // e que ainda não passaram do checkout
    $sqlProximasReservas = "SELECT r.*, 
                            p.nome as hospede_nome, 
                            p.email, 
                            p.telefone,
                            q.numero as quarto_numero, 
                            q.tipo_quarto
                            FROM reserva r
                            INNER JOIN hospede ho ON r.id_hospede = ho.id_pessoa
                            INNER JOIN pessoa p ON ho.id_pessoa = p.id_pessoa
                            INNER JOIN quarto q ON r.id_quarto = q.id_quarto
                            WHERE r.status IN ('confirmada', 'pendente')
                              AND r.data_checkout_previsto >= ?
                            ORDER BY r.data_checkin_previsto ASC
                            LIMIT 10";
    $stmtProximasReservas = $conn->prepare($sqlProximasReservas);
    $stmtProximasReservas->execute([$hoje]);
    $reservas_futuras = $stmtProximasReservas->fetchAll();

    // Atualize para 11 imagens de quartos
    $imagens_quartos = [];
    for ($i = 1; $i <= 11; $i++) {
        $imagens_quartos[] = "assets/img/quarto{$i}.png";
    }

} catch (Exception $e) {
    error_log("Erro ao buscar dados do painel: " . $e->getMessage());
    $checkins_hoje = 0;
    $checkouts_hoje = 0;
    $taxa_ocupacao = 0;
    $reservas_em_andamento = [];
    $reservas_futuras = [];
    $imagens_quartos = ['assets/img/quarto1.png', 'assets/img/quarto2.png', 'assets/img/quarto3.png'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controle - Palácio Lumière</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Menu Lateral (Sidebar) -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="index.php"><img src="assets/img/logo.png" alt="Palácio Lumière Logo"></a>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Painel</a>
                </div>

                <!-- Hóspedes Dropdown -->
                <div class="nav-item">
                    <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                        <span><i class="fas fa-users"></i> Hóspedes</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="view/cadastrar_hospede.php"><i class="fas fa-plus"></i> Cadastrar</a>
                        <a href="view/listar_hospede.php"><i class="fas fa-list"></i> Listar</a>
                        <a href="view/editar_hospede.php"><i class="fas fa-edit"></i> Editar</a>
                        <a href="view/deletar_hospede.php"><i class="fas fa-trash"></i> Deletar</a>
                    </div>
                </div>

                <!-- Funcionários Dropdown -->
                <div class="nav-item">
                    <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                        <span><i class="fas fa-briefcase"></i> Funcionários</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="view/cadastrar_funcionario.php"><i class="fas fa-plus"></i> Cadastrar</a>
                        <a href="view/lista_funcionario.php"><i class="fas fa-list"></i> Listar</a>
                        <a href="view/editar_funcionario.php"><i class="fas fa-edit"></i> Editar</a>
                        <a href="view/deletar_funcionario.php"><i class="fas fa-trash"></i> Deletar</a>
                    </div>
                </div>

                <!-- Quartos Dropdown -->
                <div class="nav-item">
                    <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                        <span><i class="fas fa-door-open"></i> Quartos</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="view/cadastrar_quarto.php"><i class="fas fa-plus"></i> Cadastrar</a>
                        <a href="view/lista_quartos.php"><i class="fas fa-list"></i> Listar</a>
                        <a href="view/editar_quartos.php"><i class="fas fa-edit"></i> Editar</a>
                        <a href="view/deletar_quarto.php"><i class="fas fa-trash"></i> Deletar</a>
                    </div>
                </div>

                <!-- Reservas Dropdown -->
                <div class="nav-item">
                    <div class="dropdown-toggle" onclick="toggleDropdown(this)">
                        <span><i class="fas fa-calendar-alt"></i> Reservas</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="dropdown-menu">
                        <a href="view/criar_reserva.php"><i class="fas fa-plus"></i> Nova Reserva</a>
                        <a href="view/lista_reservas.php"><i class="fas fa-list"></i> Listar</a>
                        <a href="view/editar_reserva.php"><i class="fas fa-edit"></i> Editar</a>
                        <a href="view/deletar_reserva.php"><i class="fas fa-trash"></i> Deletar</a>
                    </div>
                </div>

                <!-- Relatórios -->
                <div class="nav-item">
                    <a href="view/relatorios/relatorio_hospede.php"><i class="fas fa-chart-bar"></i> Relatórios</a>
                </div>
            </nav>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <header class="main-header">
                <h1>Painel de Controle</h1>
                <p style="color: #666; font-size: 0.9rem; margin-top: 5px;">
                    <i class="fas fa-calendar-day"></i> Hoje: <?= date('d/m/Y') ?>
                </p>
            </header>

            <!-- Cards de Resumo -->
            <div class="summary-cards">
                <div class="card">
                    <div class="card-icon"><i class="fas fa-arrow-right-to-bracket"></i></div>
                    <div class="card-info">
                        <span class="card-value"><?= $checkins_hoje ?></span>
                        <span class="card-label">Check-ins Hoje</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-arrow-right-from-bracket"></i></div>
                    <div class="card-info">
                        <span class="card-value"><?= $checkouts_hoje ?></span>
                        <span class="card-label">Check-outs Hoje</span>
                    </div>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-bed"></i></div>
                    <div class="card-info">
                        <span class="card-value"><?= number_format($taxa_ocupacao, 1) ?>%</span>
                        <span class="card-label">Taxa de Ocupação</span>
                        <small style="font-size: 0.75rem; color: #999; display: block; margin-top: 5px;">
                            <?= $ocupados ?> de <?= $total_quartos ?> quartos
                        </small>
                    </div>
                </div>
            </div>

            <!-- Seção de Reservas em Andamento -->
            <section class="reservation-section">
                <h2>Em Andamento</h2>
                <div class="reservation-grid">
                    <?php if (empty($reservas_em_andamento)): ?>
                        <p class="no-reservations">Nenhuma reserva em andamento no momento.</p>
                    <?php else: foreach ($reservas_em_andamento as $reserva): 
                        $imagem = $imagens_quartos[array_rand($imagens_quartos)];
                        $checkoutHoje = (date('Y-m-d', strtotime($reserva['data_checkout_previsto'])) == $hoje);
                        $checkoutAtrasado = (date('Y-m-d', strtotime($reserva['data_checkout_previsto'])) < $hoje);
                    ?>
                        <div class="reservation-card">
                            <img src="<?= htmlspecialchars($imagem) ?>" alt="Quarto <?= htmlspecialchars($reserva['quarto_numero']) ?>">
                            <div class="card-overlay">
                                <h4>Quarto <?= htmlspecialchars($reserva['quarto_numero']) ?> - <?= htmlspecialchars($reserva['tipo_quarto']) ?></h4>
                                <p>Check-out: <?= date('d/m/Y', strtotime($reserva['data_checkout_previsto'])) ?></p>
                                
                                <?php if ($checkoutHoje || $checkoutAtrasado): ?>
                                    <p style="color: #FBBD24; font-weight: bold; font-size: 0.85rem; margin-top: 8px;">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        <?= $checkoutAtrasado ? 'Check-out em atraso!' : 'Check-out hoje!' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <button class="btn-view-details"
                                    data-id="<?= htmlspecialchars($reserva['idreserva']) ?>"
                                    data-checkin="<?= date('d/m/Y', strtotime($reserva['data_checkin_previsto'])) ?>"
                                    data-checkout="<?= date('d/m/Y', strtotime($reserva['data_checkout_previsto'])) ?>"
                                    data-quarto="Quarto <?= htmlspecialchars($reserva['quarto_numero']) ?> - <?= htmlspecialchars($reserva['tipo_quarto']) ?>"
                                    data-hospede="<?= htmlspecialchars($reserva['hospede_nome']) ?>"
                                    data-email="<?= htmlspecialchars($reserva['email']) ?>"
                                    data-telefone="<?= htmlspecialchars($reserva['telefone']) ?>"
                                    data-valor="R$ <?= number_format($reserva['valor_reserva'], 2, ',', '.') ?>"
                                >
                                    Ver Detalhes
                                </button>
                                
                                <?php if ($checkoutHoje || $checkoutAtrasado): ?>
                                    <button class="btn-primary-custom btn-finalizar-reserva" 
                                            data-id="<?= htmlspecialchars($reserva['idreserva']) ?>" 
                                            style="margin-top:8px;background-color:#6B5111;color:#FBBD24;">
                                        <i class="fas fa-check-circle"></i> Finalizar Reserva
                                    </button>
                                <?php endif; ?>
                                
                                <span class="booking-id">ID: <?= htmlspecialchars($reserva['idreserva']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </section>

            <!-- Seção de Próximas Reservas -->
            <section class="reservation-section">
                <h2>Próximas Reservas</h2>
                <div class="reservation-grid">
                    <?php if (empty($reservas_futuras)): ?>
                        <p class="no-reservations">Nenhuma reserva futura agendada.</p>
                    <?php else: foreach ($reservas_futuras as $reserva): 
                        $imagem = $imagens_quartos[array_rand($imagens_quartos)];
                        $checkinHoje = (date('Y-m-d', strtotime($reserva['data_checkin_previsto'])) == $hoje);
                    ?>
                        <div class="reservation-card">
                            <img src="<?= htmlspecialchars($imagem) ?>" alt="Quarto <?= htmlspecialchars($reserva['quarto_numero']) ?>">
                            <div class="card-overlay">
                                <h4>Quarto <?= htmlspecialchars($reserva['quarto_numero']) ?> - <?= htmlspecialchars($reserva['tipo_quarto']) ?></h4>
                                <p>Check-in: <?= date('d/m/Y', strtotime($reserva['data_checkin_previsto'])) ?></p>
                                
                                <?php if ($checkinHoje): ?>
                                    <p style="color: #FBBD24; font-weight: bold; font-size: 0.85rem; margin-top: 8px;">
                                        <i class="fas fa-calendar-check"></i> Check-in hoje!
                                    </p>
                                <?php endif; ?>
                                
                                <button class="btn-view-details"
                                    data-id="<?= htmlspecialchars($reserva['idreserva']) ?>"
                                    data-checkin="<?= date('d/m/Y', strtotime($reserva['data_checkin_previsto'])) ?>"
                                    data-checkout="<?= date('d/m/Y', strtotime($reserva['data_checkout_previsto'])) ?>"
                                    data-quarto="Quarto <?= htmlspecialchars($reserva['quarto_numero']) ?> - <?= htmlspecialchars($reserva['tipo_quarto']) ?>"
                                    data-hospede="<?= htmlspecialchars($reserva['hospede_nome']) ?>"
                                    data-email="<?= htmlspecialchars($reserva['email']) ?>"
                                    data-telefone="<?= htmlspecialchars($reserva['telefone']) ?>"
                                    data-valor="R$ <?= number_format($reserva['valor_reserva'], 2, ',', '.') ?>"
                                >
                                    Ver Detalhes
                                </button>
                                
                                <?php if ($checkinHoje): ?>
                                    <button class="btn-primary-custom btn-iniciar-reserva" 
                                            data-id="<?= htmlspecialchars($reserva['idreserva']) ?>" 
                                            style="margin-top:8px;">
                                        <i class="fas fa-play"></i> Iniciar Reserva
                                    </button>
                                <?php endif; ?>
                                
                                <span class="booking-id">ID: <?= htmlspecialchars($reserva['idreserva']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal de Detalhes da Reserva -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <div class="modal-header-cinzel">
                <h2>Detalhes da Reserva <span id="modalReservaId"></span></h2>
            </div>
            <div class="modal-body-columns">
                <div class="column">
                    <h4><i class="fas fa-user"></i> Informações do Hóspede</h4>
                    <p><strong>Nome:</strong> <span id="modalHospedeNome"></span></p>
                    <p><strong>Email:</strong> <span id="modalHospedeEmail"></span></p>
                    <p><strong>Telefone:</strong> <span id="modalHospedeTelefone"></span></p>
                </div>
                <div class="column">
                    <h4><i class="fas fa-calendar-alt"></i> Informações da Reserva</h4>
                    <p><strong>Quarto:</strong> <span id="modalQuartoNome"></span></p>
                    <p><strong>Check-in:</strong> <span id="modalCheckin"></span></p>
                    <p><strong>Check-out:</strong> <span id="modalCheckout"></span></p>
                    <p><strong>Valor Total:</strong> <span id="modalValor"></span></p>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleDropdown(element) {
        const menu = element.nextElementSibling;
        menu.classList.toggle('show');
        element.classList.toggle('active');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('detailsModal');
        const btns = document.querySelectorAll('.btn-view-details');
        const span = modal.querySelector('.modal-close');

        // Modal de detalhes
        btns.forEach(btn => {
            btn.onclick = function() {
                document.getElementById('modalReservaId').innerText = '#' + this.dataset.id;
                document.getElementById('modalHospedeNome').innerText = this.dataset.hospede;
                document.getElementById('modalHospedeEmail').innerText = this.dataset.email;
                document.getElementById('modalHospedeTelefone').innerText = this.dataset.telefone;
                document.getElementById('modalQuartoNome').innerText = this.dataset.quarto;
                document.getElementById('modalCheckin').innerText = this.dataset.checkin;
                document.getElementById('modalCheckout').innerText = this.dataset.checkout;
                document.getElementById('modalValor').innerText = this.dataset.valor;
                
                modal.style.display = 'block';
            }
        });

        span.onclick = function() {
            modal.style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Botão Iniciar Reserva
        document.querySelectorAll('.btn-iniciar-reserva').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!confirm('Deseja iniciar esta reserva agora?')) {
                    return;
                }

                const id = this.dataset.id;
                const btnElement = this;
                btnElement.disabled = true;
                btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando...';
                
                fetch('update_reserva_status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'id=' + encodeURIComponent(id) + '&status=em andamento'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Reserva iniciada com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro ao iniciar reserva: ' + (data.error || 'Erro desconhecido'));
                        btnElement.disabled = false;
                        btnElement.innerHTML = '<i class="fas fa-play"></i> Iniciar Reserva';
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao comunicar com o servidor.');
                    btnElement.disabled = false;
                    btnElement.innerHTML = '<i class="fas fa-play"></i> Iniciar Reserva';
                });
            });
        });

        // Botão Finalizar Reserva
        document.querySelectorAll('.btn-finalizar-reserva').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (!confirm('Deseja finalizar esta reserva? Esta ação não pode ser desfeita.')) {
                    return;
                }

                const id = this.dataset.id;
                const btnElement = this;
                btnElement.disabled = true;
                btnElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Finalizando...';
                
                fetch('update_reserva_status.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'id=' + encodeURIComponent(id) + '&status=finalizada'
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Reserva finalizada com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro ao finalizar reserva: ' + (data.error || 'Erro desconhecido'));
                        btnElement.disabled = false;
                        btnElement.innerHTML = '<i class="fas fa-check-circle"></i> Finalizar Reserva';
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao comunicar com o servidor.');
                    btnElement.disabled = false;
                    btnElement.innerHTML = '<i class="fas fa-check-circle"></i> Finalizar Reserva';
                });
            });
        });
    });
    </script>
</body>
</html>