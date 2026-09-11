<?php
require_once "conexao.php";
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$erro_venda = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $cd_venda   = intval($_POST['id']);
    $cd_item    = intval($_POST['id_item']);
    $cd_produto = intval($_POST['produto']);
    $nova_qtd   = intval($_POST['quantidade']);
    $unitario   = floatval($_POST['valor_unitario']);
    $total      = $nova_qtd * $unitario;
    $data       = $_POST['data'];

    $stmt = $pdo->prepare("SELECT qt_produto FROM itens_vendas WHERE cd_item_venda = ?");
    $stmt->execute([$cd_item]);
    $antiga_qtd = $stmt->fetchColumn() ?: 0;

    $stmt = $pdo->prepare("SELECT qt_estoque FROM produtos WHERE cd_produto = ?");
    $stmt->execute([$cd_produto]);
    $estoque_atual = $stmt->fetchColumn() ?: 0;

    $diff = $nova_qtd - $antiga_qtd;

    if ($diff > 0 && $estoque_atual < $diff) {
        $erro_venda = "Estoque insuficiente para esta alteração! Disponível adicional no estoque: " . $estoque_atual . " un.";
    } else {
        $stmt = $pdo->prepare("UPDATE itens_vendas SET qt_produto = ?, vl_unitario = ?, vl_total = ? WHERE cd_item_venda = ?");
        $stmt->execute([$nova_qtd, $unitario, $total, $cd_item]);

        $stmt = $pdo->prepare("UPDATE vendas SET dt_venda = ?, vl_total = ? WHERE cd_venda = ?");
        $stmt->execute([$data, $total, $cd_venda]);

        if ($diff != 0) {
            $stmt = $pdo->prepare("UPDATE produtos SET qt_estoque = qt_estoque - ? WHERE cd_produto = ?");
            $stmt->execute([$diff, $cd_produto]);
        }

        header("Location: vendas.php");
        exit;
    }
}

$stmt = $pdo->prepare("
    SELECT v.cd_venda, v.dt_venda, v.vl_total,
           p.cd_produto, p.nm_produto, p.qt_estoque,
           iv.cd_item_venda, iv.qt_produto, iv.vl_unitario
    FROM vendas v
    JOIN itens_vendas iv ON v.cd_venda = iv.cd_venda
    JOIN produtos p ON iv.cd_produto = p.cd_produto
    WHERE v.cd_venda = ?
");
$stmt->execute([$id]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    header("Location: vendas.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Venda - Controle de Estoque</title>
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
                <li class="nav-item"><a class="nav-link" href="index.php">Produtos</a></li>
                <li class="nav-item"><a class="nav-link" href="compras.php">Compras</a></li>
                <li class="nav-item"><a class="nav-link active fw-bold text-primary" href="vendas.php">Vendas</a></li>
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
                    <h5 class="mb-0 fw-bold"><span class="text-primary">Editar Venda</span> #<?php echo $venda['cd_venda']; ?></h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <?php if ($erro_venda): ?>
                        <div class="alert alert-danger py-2 small"><?php echo $erro_venda; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="editar_venda.php">
                        <input type="hidden" name="salvar" value="1">
                        <input type="hidden" name="id" value="<?php echo $venda['cd_venda']; ?>">
                        <input type="hidden" name="id_item" value="<?php echo $venda['cd_item_venda']; ?>">
                        <input type="hidden" name="produto" value="<?php echo $venda['cd_produto']; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Produto</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($venda['nm_produto']); ?>" readonly>
                            <small class="text-muted">Estoque disponível atual: <?php echo $venda['qt_estoque']; ?> unidades</small>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Quantidade</label>
                                <input type="number" min="1" name="quantidade" class="form-control" value="<?php echo $venda['qt_produto']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Preço Unitário (R$)</label>
                                <input type="number" step="0.01" name="valor_unitario" class="form-control" value="<?php echo $venda['vl_unitario']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Data da Venda</label>
                            <input type="date" name="data" class="form-control" value="<?php echo $venda['dt_venda']; ?>" required>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold px-4">Salvar Alterações</button>
                            <a href="vendas.php" class="btn btn-secondary px-4">Cancelar</a>
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
