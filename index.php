<?php
require 'auth.php';
require 'config/database.php';

$uid = $_SESSION['usuario_id'];

$faturamento = $pdo->query("SELECT COALESCE(SUM(valor),0) FROM vendas WHERE status='Pago' AND usuario_id = $uid")->fetchColumn();
$despesas    = $pdo->query("SELECT COALESCE(SUM(valor),0) FROM financeiro WHERE tipo='Despesa' AND usuario_id = $uid")->fetchColumn();
$vendas      = $pdo->query("SELECT COUNT(*) FROM vendas WHERE usuario_id = $uid")->fetchColumn();

$ultimas = $pdo->query("
    SELECT c.nome AS cliente, v.status, v.valor, v.data_venda
    FROM vendas v
    JOIN clientes c ON v.cliente_id = c.id
    WHERE v.usuario_id = $uid
    ORDER BY v.data_venda DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>GestaUp - Visão Geral</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="brand"><h2>Gesta<span>Up</span></h2></div>
        <nav class="nav-menu">
            <a href="index.php" class="nav-item active">📊 Visão Geral</a>
            <a href="financeiro.php" class="nav-item">💰 Financeiro</a>
            <a href="vendas.php" class="nav-item">📦 Vendas & Estoque</a>
            <a href="clientes.php" class="nav-item">👥 Clientes</a>
            <a href="configuracoes.php" class="nav-item">⚙️ Configurações</a>
        </nav>
        <div class="user-profile">
            <div class="avatar">👤</div>
            <div class="user-info">
                <p class="user-name"><?= htmlspecialchars($_SESSION['usuario_nome']) ?></p>
                <p class="user-role"><a href="logout.php" style="color:#94a3b8">Sair</a></p>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <div class="welcome-text">
                <h1>Olá, <?= htmlspecialchars($_SESSION['usuario_nome']) ?></h1>
                <p>Resumo do seu negócio hoje.</p>
            </div>
        </header>

        <section class="metrics-grid">
            <div class="card metric-card card-green">
                <div class="card-header"><span>Faturamento</span><span class="icon">💵</span></div>
                <h3>R$ <?= number_format($faturamento, 2, ',', '.') ?></h3>
            </div>
            <div class="card metric-card card-red">
                <div class="card-header"><span>Despesas</span><span class="icon">📉</span></div>
                <h3>R$ <?= number_format($despesas, 2, ',', '.') ?></h3>
            </div>
            <div class="card metric-card card-blue">
                <div class="card-header"><span>Vendas</span><span class="icon">🛒</span></div>
                <h3><?= $vendas ?> Pedidos</h3>
            </div>
        </section>

        <section class="card table-card">
            <h2>Últimas Atividades</h2>
            <table class="data-table">
                <thead>
                    <tr><th>Cliente</th><th>Status</th><th>Valor</th><th>Data</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimas as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['cliente']) ?></td>
                        <td>
                            <span class="status-badge status-<?= 
                                $v['status'] == 'Pago' ? 'success' : 
                                ($v['status'] == 'Pendente' ? 'pending' : 'error') 
                            ?>"><?= $v['status'] ?></span>
                        </td>
                        <td>R$ <?= number_format($v['valor'], 2, ',', '.') ?></td>
                        <td><?= date('d/m/Y', strtotime($v['data_venda'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>