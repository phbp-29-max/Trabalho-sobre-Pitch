<?php
require 'auth.php';
require 'config/database.php';

$uid = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO financeiro (descricao, tipo, valor, data_movimento, usuario_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['descricao'] ?? '',
        $_POST['tipo'] ?? 'Despesa',
        $_POST['valor'] ?? 0,
        $_POST['data'] ?? date('Y-m-d'),
        $uid
    ]);
    header('Location: financeiro.php');
    exit;
}

if (isset($_GET['excluir'])) {
    $stmt = $pdo->prepare("DELETE FROM financeiro WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$_GET['excluir'], $uid]);
    header('Location: financeiro.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM financeiro WHERE usuario_id = ? ORDER BY data_movimento DESC");
$stmt->execute([$uid]);
$movimentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COALESCE(SUM(valor),0) FROM financeiro WHERE tipo='Receita' AND usuario_id = ?");
$stmt->execute([$uid]);
$totalReceitas = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COALESCE(SUM(valor),0) FROM financeiro WHERE tipo='Despesa' AND usuario_id = ?");
$stmt->execute([$uid]);
$totalDespesas = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>GestaUp - Financeiro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="brand"><h2>Gesta<span>Up</span></h2></div>
        <nav class="nav-menu">
            <a href="index.php" class="nav-item">📊 Visão Geral</a>
            <a href="financeiro.php" class="nav-item active">💰 Financeiro</a>
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
                <h1>💰 Financeiro</h1>
                <p>Controle de receitas e despesas.</p>
            </div>
        </header>

        <section class="metrics-grid">
            <div class="card metric-card card-green">
                <div class="card-header"><span>Receitas</span><span class="icon">💵</span></div>
                <h3>R$ <?= number_format($totalReceitas, 2, ',', '.') ?></h3>
            </div>
            <div class="card metric-card card-red">
                <div class="card-header"><span>Despesas</span><span class="icon">📉</span></div>
                <h3>R$ <?= number_format($totalDespesas, 2, ',', '.') ?></h3>
            </div>
            <div class="card metric-card card-blue">
                <div class="card-header"><span>Saldo</span><span class="icon">⚖️</span></div>
                <h3>R$ <?= number_format($totalReceitas - $totalDespesas, 2, ',', '.') ?></h3>
            </div>
        </section>

        <section class="card">
            <h2>Nova Movimentação</h2>
            <form method="POST" class="form-grid">
                <input type="text" name="descricao" placeholder="Descrição" required>
                <select name="tipo" required>
                    <option value="Receita">Receita</option>
                    <option value="Despesa" selected>Despesa</option>
                </select>
                <input type="number" step="0.01" name="valor" placeholder="Valor (R$)" required>
                <input type="date" name="data" value="<?= date('Y-m-d') ?>" required>
                <button type="submit" class="btn-primary">Cadastrar</button>
            </form>
        </section>

        <section class="card table-card">
            <h2>Movimentações</h2>
            <?php if (empty($movimentos)): ?>
                <p style="margin-top:15px;color:#64748b">Nenhuma movimentação cadastrada ainda.</p>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr><th>Descrição</th><th>Tipo</th><th>Valor</th><th>Data</th><th>Ações</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($movimentos as $m): ?>
                    <tr>
                        <td><?= htmlspecialchars($m['descricao']) ?></td>
                        <td>
                            <span class="status-badge status-<?= $m['tipo'] == 'Receita' ? 'success' : 'error' ?>">
                                <?= $m['tipo'] ?>
                            </span>
                        </td>
                        <td>R$ <?= number_format($m['valor'], 2, ',', '.') ?></td>
                        <td><?= date('d/m/Y', strtotime($m['data_movimento'])) ?></td>
                        <td><a href="?excluir=<?= $m['id'] ?>" onclick="return confirm('Excluir?')">🗑️</a></td>
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