<?php
require_once "conexao.php";
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar'])) {
    $cd_compra  = intval($_POST['id']);
    $cd_item    = intval($_POST['id_item']);
    $cd_produto = intval($_POST['produto']);
    $fornecedor = intval($_POST['fornecedor']);
    $nova_qtd   = intval($_POST['quantidade']);
    $unitario   = floatval($_POST['valor_unitario']);
    $total      = $nova_qtd * $unitario;
    $data       = $_POST['data'];

    $stmt = $pdo->prepare("SELECT qt_produto FROM itens_compras WHERE cd_item_compra = ?");
    $stmt->execute([$cd_item]);
    $antiga_qtd = $stmt->fetchColumn() ?: 0;

    $diff = $nova_qtd - $antiga_qtd;

    $stmt = $pdo->prepare("UPDATE itens_compras SET qt_produto = ?, vl_unitario = ?, vl_total = ? WHERE cd_item_compra = ?");
    $stmt->execute([$nova_qtd, $unitario, $total, $cd_item]);

    $stmt = $pdo->prepare("UPDATE compras SET dt_compra = ?, vl_total = ?, cd_fornecedor = ? WHERE cd_compra = ?");
    $stmt->execute([$data, $total, $fornecedor, $cd_compra]);

    if ($diff != 0) {
        $stmt = $pdo->prepare("UPDATE produtos SET qt_estoque = qt_estoque + ? WHERE cd_produto = ?");
        $stmt->execute([$diff, $cd_produto]);
    }

    header("Location: compras.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.cd_compra, c.dt_compra, c.vl_total, c.cd_fornecedor,
           p.cd_produto, p.nm_produto,
           ic.cd_item_compra, ic.qt_produto, ic.vl_unitario
    FROM compras c
    JOIN itens_compras ic ON c.cd_compra = ic.cd_compra
    JOIN produtos p ON ic.cd_produto = p.cd_produto
    WHERE c.cd_compra = ?
");
$stmt->execute([$id]);
$compra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$compra) {
    header("Location: compras.php");
    exit;
}

// 4. Buscar lista de fornecedores
$fornecedores = $pdo->query("SELECT * FROM fornecedores ORDER BY nm_fornecedor ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Compra - Controle de Estoque</title>
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
                <li class="nav-item"><a class="nav-link active fw-bold text-primary" href="compras.php">Compras</a></li>
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
                    <h5 class="mb-0 fw-bold"><span class="text-primary">Editar Compra</span> #<?php echo $compra['cd_compra']; ?></h5>
                </div>
                <div class="card-body p-4 bg-white">
                    <form method="POST" action="editar_compra.php">
                        <input type="hidden" name="salvar" value="1">
                        <input type="hidden" name="id" value="<?php echo $compra['cd_compra']; ?>">
                        <input type="hidden" name="id_item" value="<?php echo $compra['cd_item_compra']; ?>">
                        <input type="hidden" name="produto" value="<?php echo $compra['cd_produto']; ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Produto</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($compra['nm_produto']); ?>" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark">Fornecedor</label>
                            <select class="form-select" name="fornecedor" required>
                                <?php foreach ($fornecedores as $f): ?>
                                    <option value="<?php echo $f['cd_fornecedor']; ?>" <?php echo ($f['cd_fornecedor'] == $compra['cd_fornecedor']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($f['nm_fornecedor']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Quantidade</label>
                                <input type="number" min="1" name="quantidade" class="form-control" value="<?php echo $compra['qt_produto']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">Valor Unitário (R$)</label>
                                <input type="number" step="0.01" name="valor_unitario" class="form-control" value="<?php echo $compra['vl_unitario']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Data da Compra</label>
                            <input type="date" name="data" class="form-control" value="<?php echo $compra['dt_compra']; ?>" required>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary fw-bold px-4">Salvar Alterações</button>
                            <a href="compras.php" class="btn btn-secondary px-4">Cancelar</a>
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
