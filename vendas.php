<?php
require 'auth.php';
require 'config/database.php';

$uid = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO vendas (cliente_id, valor, status, usuario_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['cliente_id'] ?? 0,
        $_POST['valor'] ?? 0,
        $_POST['status'] ?? 'Pendente',
        $uid
    ]);
    header('Location: vendas.php');
    exit;
}

if (isset($_GET['excluir'])) {
    $stmt = $pdo->prepare("DELETE FROM vendas WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$_GET['excluir'], $uid]);
    header('Location: vendas.php');
    exit;
}

// Clientes do usuário logado
$stmt = $pdo->prepare("SELECT id, nome FROM clientes WHERE usuario_id = ? ORDER BY nome");
$stmt->execute([$uid]);
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Vendas do usuário logado
$stmt = $pdo->prepare("
    SELECT v.id, c.nome AS cliente, v.valor, v.status, v.data_venda
    FROM vendas v
    JOIN clientes c ON v.cliente_id = c.id
    WHERE v.usuario_id = ?
    ORDER BY v.data_venda DESC
");
$stmt->execute([$uid]);
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>GestaUp - Vendas & Estoque</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="brand"><h2>Gesta<span>Up</span></h2></div>
        <nav class="nav-menu">
            <a href="index.php" class="nav-item">📊 Visão Geral</a>
            <a href="financeiro.php" class="nav-item">💰 Financeiro</a>
            <a href="vendas.php" class="nav-item active">📦 Vendas & Estoque</a>
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
                <h1>📦 Vendas & Estoque</h1>
                <p>Gerencie pedidos e vendas.</p>
            </div>
        </header>

        <section class="card">
            <h2>Nova Venda</h2>
            <form method="POST" class="form-grid">
                <select name="cliente_id" required>
                    <option value="">Cliente...</option>
                    <?php foreach ($clientes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" step="0.01" name="valor" placeholder="Valor (R$)" required>
                <select name="status" required>
                    <option value="Pago">Pago</option>
                    <option value="Pendente" selected>Pendente</option>
                    <option value="Cancelado">Cancelado</option>
                </select>
                <button type="submit" class="btn-primary">Cadastrar Venda</button>
            </form>
        </section>

        <section class="card table-card">
            <h2>Vendas Realizadas</h2>
            <?php if (empty($vendas)): ?>
                <p style="margin-top:15px;color:#64748b">Nenhuma venda cadastrada ainda.</p>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Cliente</th><th>Valor</th><th>Status</th><th>Data</th><th>Ações</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($vendas as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['cliente']) ?></td>
                        <td>R$ <?= number_format($v['valor'], 2, ',', '.') ?></td>
                        <td>
                            <span class="status-badge status-<?= 
                                $v['status'] == 'Pago' ? 'success' : 
                                ($v['status'] == 'Pendente' ? 'pending' : 'error') 
                            ?>"><?= $v['status'] ?></span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($v['data_venda'])) ?></td>
                        <td><a href="?excluir=<?= $v['id'] ?>" onclick="return confirm('Excluir?')">🗑️</a></td>
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