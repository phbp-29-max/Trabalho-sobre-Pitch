<?php
require 'auth.php';
require 'config/database.php';

$uid = $_SESSION['usuario_id'];

// Salvar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE configuracoes SET valor = ? WHERE chave = ? AND usuario_id = ?");
    $stmt->execute([$_POST['empresa_nome'], 'empresa_nome', $uid]);
    $stmt->execute([$_POST['empresa_email'], 'empresa_email', $uid]);
    header('Location: configuracoes.php');
    exit;
}

// Estatísticas
$stmt = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE usuario_id = ?"); $stmt->execute([$uid]); $totalClientes = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM vendas WHERE usuario_id = ?"); $stmt->execute([$uid]); $totalVendas = $stmt->fetchColumn();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM financeiro WHERE usuario_id = ?"); $stmt->execute([$uid]); $totalMov = $stmt->fetchColumn();

// Configurações
$stmt = $pdo->prepare("SELECT chave, valor FROM configuracoes WHERE usuario_id = ?");
$stmt->execute([$uid]);
$config = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $config[$r['chave']] = $r['valor'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>GestaUp - Configurações</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="brand"><h2>Gesta<span>Up</span></h2></div>
        <nav class="nav-menu">
            <a href="index.php" class="nav-item">📊 Visão Geral</a>
            <a href="financeiro.php" class="nav-item">💰 Financeiro</a>
            <a href="vendas.php" class="nav-item">📦 Vendas & Estoque</a>
            <a href="clientes.php" class="nav-item">👥 Clientes</a>
            <a href="configuracoes.php" class="nav-item active">⚙️ Configurações</a>
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
                <h1>⚙️ Configurações</h1>
                <p>Ajustes e informações do sistema.</p>
            </div>
        </header>

        <section class="card">
            <h2>Informações da Empresa</h2>
            <form method="POST" class="form-grid">
                <input type="text" name="empresa_nome" value="<?= htmlspecialchars($config['empresa_nome'] ?? '') ?>" placeholder="Nome da empresa">
                <input type="email" name="empresa_email" value="<?= htmlspecialchars($config['empresa_email'] ?? '') ?>" placeholder="E-mail">
                <input type="text" value="<?= htmlspecialchars($config['plano'] ?? '') ?>" disabled>
                <button type="submit" class="btn-primary">Salvar</button>
            </form>
        </section>

        <section class="card">
            <h2>Estatísticas</h2>
            <table class="data-table">
                <thead><tr><th>Módulo</th><th>Total</th></tr></thead>
                <tbody>
                    <tr><td>👥 Clientes</td><td><?= $totalClientes ?></td></tr>
                    <tr><td>🛒 Vendas</td><td><?= $totalVendas ?></td></tr>
                    <tr><td>💰 Movimentações</td><td><?= $totalMov ?></td></tr>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>