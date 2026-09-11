<?php
require_once "conexao.php";
session_start();

$mensagem = "";
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome  = trim($_POST["nome"] ?? "");
    $login = trim($_POST["login"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = trim($_POST["senha"] ?? "");

    if (empty($nome) || empty($login) || empty($senha)) {
        $erro = "Preencha todos os campos obrigatórios!";
    } else {
        $stmt = $pdo->prepare("SELECT cd_usuario FROM usuarios WHERE ds_login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $erro = "Este login já existe. Escolha outro.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nm_usuario, ds_login, ds_senha, ds_email, ds_tipo) VALUES (?, ?, ?, ?, 'PADRAO')");
            $stmt->execute([$nome, $login, $email, $senha]);
            $mensagem = "Conta criada com sucesso! Agora você pode entrar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Conta - Controle de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white text-center py-3 border-bottom border-primary border-3">
                    <h4 class="mb-0 fw-bold"><span class="text-primary">Criar</span> Nova Conta</h4>
                    <small class="text-white-50">Preencha os dados abaixo</small>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($erro)): ?>
                        <div class="alert alert-danger py-2 small" role="alert"><?php echo $erro; ?></div>
                    <?php endif; ?>

                    <?php if (!empty($mensagem)): ?>
                        <div class="alert alert-success py-3 text-center" role="alert">
                            <div><?php echo $mensagem; ?></div>
                            <div class="mt-2">
                                <a href="login.php" class="btn btn-primary btn-sm fw-bold">Ir para o Login</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="cadastro.php">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Nome Completo</label>
                                <input type="text" name="nome" class="form-control" placeholder="Ex: João Silva" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Usuário (Login)</label>
                                <input type="text" name="login" class="form-control" placeholder="Ex: joao" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">E-mail</label>
                                <input type="email" name="email" class="form-control" placeholder="Ex: joao@email.com">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold text-dark">Senha</label>
                                <input type="password" name="senha" class="form-control" placeholder="Digite sua senha" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mb-3">
                                Cadastrar Conta
                            </button>

                            <div class="text-center">
                                <a href="login.php" class="text-decoration-none fw-semibold">
                                    Já possui uma conta? Faça login
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
