<?php
session_start();
require 'config/database.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($senha, $user['senha'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nome'] = $user['nome'];
        header('Location: index.php');
        exit;
    } else {
        $erro = "E-mail ou senha inválidos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>GestaUp - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="auth-box">
        <h1>Gesta<span>Up</span></h1>
        <h2>Entrar</h2>
        <?php if ($erro): ?><p class="auth-erro"><?= $erro ?></p><?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit" class="btn-primary">Entrar</button>
        </form>
        <p class="auth-link">Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>
    </div>
</body>
</html>