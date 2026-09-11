<?php
// ============================================================
// EDIÇÃO DE PRODUTO
// ============================================================
require_once "conexao.php";
session_start();

// 1. Verificar autenticação
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Salvar alterações do produto (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $id     = intval($_POST['id']);
    $nome   = trim($_POST['nome']);
    $custo  = floatval($_POST['preco_custo']);
    $venda  = floatval($_POST['preco_venda']);
    $qtd    = intval($_POST['quantidade']);
    $minimo = intval($_POST['estoque_minimo']);

    $stmt = $pdo->prepare("UPDATE produtos SET nm_produto = ?, vl_custo = ?, vl_venda = ?, qt_estoque = ?, qt_estoque_minimo = ? WHERE cd_produto = ?");
    $stmt->execute([$nome, $custo, $venda, $qtd, $minimo, $id]);

    header("Location: index.php");
    exit;
}

// 3. Buscar dados atuais do produto para preencher o formulário
$stmt = $pdo->prepare("SELECT * FROM produtos WHERE cd_produto = ?");
$stmt->execute([$id]);
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto - Controle de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Barra de Navegação -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><span class="text-primary">Controle</span> de Estoque</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active fw-bold text-primary" href="index.php">Produtos</a></li>
                <li class="nav-item"><a class="nav-link" href="compras.php">Compras</a></li>
                <li class="nav-item"><a class="nav-link" href="vendas.php">Vendas</a></li>
                <li class="nav-item"><a class="nav-link" href="relatorios.php">Relatórios</a></li>
            </ul>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm fw-bold">Sair</a>
            </div>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3 border-bottom border-primary border-3">
                    <h5 class="mb-0 fw-bold"><span class="text-primary">Editar Produto</span> #<?php echo $produto['cd_produto']; ?></h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <form method="POST" action="editar_produto.php">
                        <input type="hidden" name="salvar" value="1">
                        <input type="hidden" name="id" value="<?php echo $produto['cd_produto']; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Nome do Produto</label>
                            <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($produto['nm_produto']); ?>" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Preço de Custo (R$)</label>
                                <input type="number" step="0.01" name="preco_custo" class="form-control" value="<?php echo $produto['vl_custo']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Preço de Venda (R$)</label>
                                <input type="number" step="0.01" name="preco_venda" class="form-control" value="<?php echo $produto['vl_venda']; ?>" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Quantidade em Estoque</label>
                                <input type="number" name="quantidade" class="form-control" value="<?php echo $produto['qt_estoque']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Estoque Mínimo</label>
                                <input type="number" name="estoque_minimo" class="form-control" value="<?php echo $produto['qt_estoque_minimo']; ?>" required>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold px-4">Salvar Alterações</button>
                            <a href="index.php" class="btn btn-secondary px-4">Cancelar</a>
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
