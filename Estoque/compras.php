<?php
// ============================================================
// CONTROLE DE ESTOQUE - CRUD DE COMPRAS
// ============================================================
require_once "conexao.php";
session_start();

// 1. Verificar autenticação
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// 2. Excluir compra e atualizar estoque
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    try {
        $itens = $pdo->prepare("SELECT cd_produto, qt_produto FROM itens_compras WHERE cd_compra = ?");
        $itens->execute([$id]);
        foreach ($itens->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $pdo->prepare("UPDATE produtos SET qt_estoque = GREATEST(0, qt_estoque - ?) WHERE cd_produto = ?")
                ->execute([$item['qt_produto'], $item['cd_produto']]);
        }
        $pdo->prepare("DELETE FROM itens_compras WHERE cd_compra = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM compras WHERE cd_compra = ?")->execute([$id]);
    } catch (Exception $e) {
    }
    header("Location: compras.php");
    exit;
}

// 3. Cadastrar nova compra
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_compra'])) {
    $fornecedor = intval($_POST['fornecedor']);
    $produto    = intval($_POST['produto']);
    $qtd        = intval($_POST['quantidade']);
    $unitario   = floatval($_POST['valor_unitario']);
    $total      = $qtd * $unitario;
    $data       = $_POST['data'];
    $usuario    = $_SESSION['cd_usuario'] ?? 1;

    $stmt = $pdo->prepare("INSERT INTO compras (dt_compra, vl_total, cd_fornecedor, cd_usuario) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data, $total, $fornecedor, $usuario]);
    $cd_compra = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO itens_compras (cd_compra, cd_produto, qt_produto, vl_unitario, vl_total) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$cd_compra, $produto, $qtd, $unitario, $total]);

    header("Location: compras.php");
    exit;
}

// 4. Buscar histórico de compras e listas auxiliares
$compras = $pdo->query("
    SELECT c.cd_compra, c.dt_compra, c.vl_total, f.nm_fornecedor, p.nm_produto, ic.qt_produto, ic.vl_unitario
    FROM compras c
    JOIN fornecedores f ON c.cd_fornecedor = f.cd_fornecedor
    JOIN itens_compras ic ON c.cd_compra = ic.cd_compra
    JOIN produtos p ON ic.cd_produto = p.cd_produto
    ORDER BY c.cd_compra DESC
")->fetchAll(PDO::FETCH_ASSOC);

$fornecedores = $pdo->query("SELECT * FROM fornecedores ORDER BY nm_fornecedor ASC")->fetchAll(PDO::FETCH_ASSOC);
$produtos = $pdo->query("SELECT * FROM produtos ORDER BY nm_produto ASC")->fetchAll(PDO::FETCH_ASSOC);
$total_compras = $pdo->query("SELECT SUM(vl_total) FROM compras")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras - Controle de Estoque</title>
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-dark mb-0">Entrada e Compras de Estoque</h3>
        <span class="text-muted">Total Gasto: <strong class="text-dark">R$ <?php echo number_format($total_compras, 2, ',', '.'); ?></strong></span>
    </div>

    <!-- Formulário de Compra -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white py-3 border-bottom border-primary border-3">
            <h5 class="mb-0 fw-bold"><span class="text-primary">Registrar</span> Nova Compra</h5>
        </div>
        <div class="card-body p-4 bg-white">
            <form method="POST" action="compras.php">
                <input type="hidden" name="cadastrar_compra" value="1">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-dark">Fornecedor</label>
                        <select class="form-select" name="fornecedor" required>
                            <option value="" disabled selected>Selecione...</option>
                            <?php foreach ($fornecedores as $f): ?>
                                <option value="<?php echo $f['cd_fornecedor']; ?>"><?php echo htmlspecialchars($f['nm_fornecedor']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-dark">Produto</label>
                        <select class="form-select" name="produto" required>
                            <option value="" disabled selected>Selecione...</option>
                            <?php foreach ($produtos as $p): ?>
                                <option value="<?php echo $p['cd_produto']; ?>"><?php echo htmlspecialchars($p['nm_produto']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-dark">Quantidade</label>
                        <input type="number" min="1" name="quantidade" class="form-control" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-dark">Valor Unitário (R$)</label>
                        <input type="number" step="0.01" name="valor_unitario" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-dark">Data</label>
                        <input type="date" name="data" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary fw-bold px-4">Salvar Compra</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Compras -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Histórico de Compras</h5>
            <span class="badge bg-primary"><?php echo count($compras); ?> registros</span>
        </div>
        <div class="card-body p-0 bg-white">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center text-nowrap" style="width: 50px;">#</th>
                            <th class="text-nowrap" style="width: 110px;">DATA</th>
                            <th class="text-nowrap" style="width: 200px;">FORNECEDOR</th>
                            <th>PRODUTO</th>
                            <th class="text-nowrap text-center" style="width: 120px;">QUANTIDADE</th>
                            <th class="text-nowrap text-end" style="width: 130px;">VALOR UNIT.</th>
                            <th class="text-nowrap text-end" style="width: 130px;">TOTAL</th>
                            <th class="text-nowrap text-center" style="width: 160px;">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($compras)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Nenhuma compra registrada.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($compras as $c): ?>
                                <tr>
                                    <td class="text-center text-muted"><?php echo $c['cd_compra']; ?></td>
                                    <td class="text-nowrap"><?php echo date('d/m/Y', strtotime($c['dt_compra'])); ?></td>
                                    <td><strong><?php echo htmlspecialchars($c['nm_fornecedor']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($c['nm_produto']); ?></td>
                                    <td class="text-nowrap text-center"><span class="badge bg-primary">+<?php echo $c['qt_produto']; ?> un</span></td>
                                    <td class="text-nowrap text-end">R$ <?php echo number_format($c['vl_unitario'], 2, ',', '.'); ?></td>
                                    <td class="text-nowrap text-end"><strong class="text-dark">R$ <?php echo number_format($c['vl_total'], 2, ',', '.'); ?></strong></td>
                                    <td class="text-nowrap text-center">
                                        <a href="editar_compra.php?id=<?php echo $c['cd_compra']; ?>" class="btn btn-primary btn-sm fw-bold me-1">Editar</a>
                                        <a href="compras.php?excluir=<?php echo $c['cd_compra']; ?>" class="btn btn-danger btn-sm fw-bold" onclick="return confirm('Deseja realmente excluir esta compra?')">Excluir</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
