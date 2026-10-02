<?php
session_start();
require 'config/database.php';

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if ($nome && $email && $senha) {
        // Verifica se e-mail já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erro = "Este e-mail já está cadastrado!";
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $email, $hash]);

            $_SESSION['usuario_id'] = $pdo->lastInsertId();
            $_SESSION['usuario_nome'] = $nome;
            header('Location: index.php');
            exit;
        }
    } else {
        $erro = "Preencha todos os campos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>GestaUp - Cadastro</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="auth-box">
        <h1>Gesta<span>Up</span></h1>
        <h2>Criar conta</h2>
        <?php if ($erro): ?><p class="auth-erro"><?= $erro ?></p><?php endif; ?>
        <form method="POST">
            <input type="text" name="nome" placeholder="Nome da empresa" required>
            <input type="email" name="email" placeholder="E-mail" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button type="submit" class="btn-primary">Cadastrar</button>
        </form>
        <p class="auth-link">Já tem conta? <a href="login.php">Entrar</a></p>
    </div>
</body>
</html>