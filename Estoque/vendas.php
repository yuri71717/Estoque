<?php
require_once "conexao.php";
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$erro_venda = null;

if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    try {
        $itens = $pdo->prepare("SELECT cd_produto, qt_produto FROM itens_vendas WHERE cd_venda = ?");
        $itens->execute([$id]);
        foreach ($itens->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $pdo->prepare("UPDATE produtos SET qt_estoque = qt_estoque + ? WHERE cd_produto = ?")
                ->execute([$item['qt_produto'], $item['cd_produto']]);
        }
        $pdo->prepare("DELETE FROM itens_vendas WHERE cd_venda = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM vendas WHERE cd_venda = ?")->execute([$id]);
    } catch (Exception $e) {
    }
    header("Location: vendas.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_venda'])) {
    $produto  = intval($_POST['produto']);
    $qtd      = intval($_POST['quantidade']);
    $unitario = floatval($_POST['valor_unitario']);
    $total    = $qtd * $unitario;
    $data     = $_POST['data'];
    $usuario  = $_SESSION['cd_usuario'] ?? 1;

    try {
        $stmt = $pdo->prepare("INSERT INTO vendas (dt_venda, vl_total, cd_usuario) VALUES (?, ?, ?)");
        $stmt->execute([$data, $total, $usuario]);
        $cd_venda = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO itens_vendas (cd_venda, cd_produto, qt_produto, vl_unitario, vl_total) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$cd_venda, $produto, $qtd, $unitario, $total]);

        header("Location: vendas.php");
        exit;
    } catch (Exception $e) {
        $erro_venda = "Estoque insuficiente para realizar esta venda!";
    }
}

$vendas = $pdo->query("
    SELECT v.cd_venda, v.dt_venda, v.vl_total,
           p.cd_produto, p.nm_produto,
           iv.cd_item_venda, iv.qt_produto, iv.vl_unitario,
           u.nm_usuario
    FROM vendas v
    JOIN itens_vendas iv ON v.cd_venda = iv.cd_venda
    JOIN produtos p ON iv.cd_produto = p.cd_produto
    JOIN usuarios u ON v.cd_usuario = u.cd_usuario
    ORDER BY v.cd_venda DESC
")->fetchAll(PDO::FETCH_ASSOC);

$produtos = $pdo->query("SELECT * FROM produtos WHERE qt_estoque > 0 ORDER BY nm_produto ASC")->fetchAll(PDO::FETCH_ASSOC);
$total_vendas = $pdo->query("SELECT SUM(vl_total) FROM vendas")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendas - Controle de Estoque</title>
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-dark mb-0">Saída e Vendas de Estoque</h3>
        <span class="text-muted">Total Faturado: <strong class="text-primary">R$ <?php echo number_format($total_vendas, 2, ',', '.'); ?></strong></span>
    </div>

    <?php if ($erro_venda): ?>
        <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
            <?php echo $erro_venda; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Formulário de Venda -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white py-3 border-bottom border-primary border-3">
            <h5 class="mb-0 fw-bold"><span class="text-primary">Registrar</span> Nova Venda</h5>
        </div>
        <div class="card-body p-4 bg-white">
            <form method="POST" action="vendas.php">
                <input type="hidden" name="cadastrar_venda" value="1">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-bold text-dark">Produto</label>
                        <select class="form-select" name="produto" required>
                            <option value="" disabled selected>Selecione um produto...</option>
                            <?php foreach ($produtos as $p): ?>
                                <option value="<?php echo $p['cd_produto']; ?>">
                                    <?php echo htmlspecialchars($p['nm_produto']) . ' (Estoque: ' . $p['qt_estoque'] . ' | R$ ' . number_format($p['vl_venda'], 2, ',', '.') . ')'; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-dark">Quantidade</label>
                        <input type="number" min="1" name="quantidade" class="form-control" value="1" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-dark">Preço Unitário (R$)</label>
                        <input type="number" step="0.01" name="valor_unitario" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold text-dark">Data da Venda</label>
                        <input type="date" name="data" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary fw-bold px-4">Salvar Venda</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabela de Vendas -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Histórico de Vendas</h5>
            <span class="badge bg-primary"><?php echo count($vendas); ?> registros</span>
        </div>
        <div class="card-body p-0 bg-white">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center text-nowrap" style="width: 50px;">#</th>
                            <th class="text-nowrap" style="width: 110px;">DATA</th>
                            <th>PRODUTO</th>
                            <th class="text-nowrap text-center" style="width: 120px;">QUANTIDADE</th>
                            <th class="text-nowrap text-end" style="width: 130px;">PREÇO UNIT.</th>
                            <th class="text-nowrap text-end" style="width: 130px;">TOTAL</th>
                            <th class="text-nowrap" style="width: 140px;">VENDEDOR</th>
                            <th class="text-nowrap text-center" style="width: 160px;">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($vendas)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Nenhuma venda registrada.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($vendas as $v): ?>
                                <tr>
                                    <td class="text-center text-muted"><?php echo $v['cd_venda']; ?></td>
                                    <td class="text-nowrap"><?php echo date('d/m/Y', strtotime($v['dt_venda'])); ?></td>
                                    <td><strong class="text-dark"><?php echo htmlspecialchars($v['nm_produto']); ?></strong></td>
                                    <td class="text-nowrap text-center"><span class="badge bg-secondary">-<?php echo $v['qt_produto']; ?> un</span></td>
                                    <td class="text-nowrap text-end">R$ <?php echo number_format($v['vl_unitario'], 2, ',', '.'); ?></td>
                                    <td class="text-nowrap text-end"><strong class="text-primary">R$ <?php echo number_format($v['vl_total'], 2, ',', '.'); ?></strong></td>
                                    <td class="text-nowrap"><?php echo htmlspecialchars($v['nm_usuario']); ?></td>
                                    <td class="text-nowrap text-center">
                                        <a href="editar_venda.php?id=<?php echo $v['cd_venda']; ?>" class="btn btn-primary btn-sm fw-bold me-1">Editar</a>
                                        <a href="vendas.php?excluir=<?php echo $v['cd_venda']; ?>" class="btn btn-danger btn-sm fw-bold" onclick="return confirm('Deseja realmente excluir esta venda?')">Excluir</a>
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
