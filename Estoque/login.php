<?php
// ============================================================
// TELA DE LOGIN
// ============================================================
require_once "conexao.php";
session_start();

$erro_login = null;

// Processar formulário de login (POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["usuario"] ?? "");
    $senha = trim($_POST["senha"] ?? "");

    // Buscar usuário no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE ds_login = ? AND ds_senha = ?");
    $stmt->execute([$login, $senha]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Salvar dados na sessão e redirecionar para os produtos
        $_SESSION["usuario"] = $user["nm_usuario"];
        $_SESSION["cd_usuario"] = $user["cd_usuario"];
        header("Location: index.php");
        exit;
    } else {
        $erro_login = "Login ou senha incorretos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Controle de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white text-center py-3 border-bottom border-primary border-3">
                    <h4 class="mb-0 fw-bold"><span class="text-primary">Controle</span> de Estoque</h4>
                    <small class="text-white-50">Faça login para continuar</small>
                </div>
                <div class="card-body p-4">
                    <?php if ($erro_login): ?>
                        <div class="alert alert-danger py-2 small" role="alert">
                            <?php echo $erro_login; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Usuário</label>
                            <input type="text" name="usuario" class="form-control" value="admin" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Senha</label>
                            <input type="password" name="senha" class="form-control" value="123456" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mb-3">
                            Entrar no Sistema
                        </button>

                        <div class="text-center">
                            <a href="cadastro.php" class="text-decoration-none fw-semibold">
                                Não tem uma conta? Cadastre-se
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
