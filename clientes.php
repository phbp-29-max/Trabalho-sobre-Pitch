<?php
require 'auth.php';
require 'config/database.php';

$uid = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, usuario_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nome'] ?? '',
        $_POST['email'] ?? '',
        $_POST['telefone'] ?? '',
        $uid
    ]);
    header('Location: clientes.php');
    exit;
}

if (isset($_GET['excluir'])) {
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$_GET['excluir'], $uid]);
    header('Location: clientes.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM clientes WHERE usuario_id = ? ORDER BY nome");
$stmt->execute([$uid]);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>GestaUp - Clientes</title>
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
            <a href="clientes.php" class="nav-item active">👥 Clientes</a>
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
                <h1>👥 Clientes</h1>
                <p>Cadastro e gestão de clientes.</p>
            </div>
        </header>

        <section class="card">
            <h2>Novo Cliente</h2>
            <form method="POST" class="form-grid">
                <input type="text" name="nome" placeholder="Nome completo" required>
                <input type="email" name="email" placeholder="E-mail">
                <input type="text" name="telefone" placeholder="Telefone">
                <button type="submit" class="btn-primary">Cadastrar</button>
            </form>
        </section>

        <section class="card table-card">
            <h2>Clientes Cadastrados (<?= count($clientes) ?>)</h2>
            <?php if (empty($clientes)): ?>
                <p style="margin-top:15px;color:#64748b">Nenhum cliente cadastrado ainda.</p>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Nome</th><th>E-mail</th><th>Telefone</th><th>Ações</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['nome']) ?></td>
                        <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
                        <td><?= htmlspecialchars($c['telefone'] ?? '') ?></td>
                        <td><a href="?excluir=<?= $c['id'] ?>" onclick="return confirm('Excluir?')">🗑️</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>