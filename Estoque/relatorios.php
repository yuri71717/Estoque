<?php
require_once "conexao.php";
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$mais_vendidos = $pdo->query("
    SELECT p.cd_produto, p.nm_produto, p.vl_venda, p.qt_estoque,
           SUM(iv.qt_produto) AS total_vendido,
           SUM(iv.vl_total) AS total_faturado
    FROM itens_vendas iv
    JOIN produtos p ON iv.cd_produto = p.cd_produto
    GROUP BY p.cd_produto, p.nm_produto, p.vl_venda, p.qt_estoque
    ORDER BY total_vendido DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$menos_vendidos = $pdo->query("
    SELECT p.cd_produto, p.nm_produto, p.vl_venda, p.qt_estoque,
           COALESCE(SUM(iv.qt_produto), 0) AS total_vendido,
           COALESCE(SUM(iv.vl_total), 0) AS total_faturado
    FROM produtos p
    LEFT JOIN itens_vendas iv ON p.cd_produto = iv.cd_produto
    GROUP BY p.cd_produto, p.nm_produto, p.vl_venda, p.qt_estoque
    ORDER BY total_vendido ASC, p.qt_estoque DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$total_pecas = $pdo->query("SELECT SUM(qt_produto) FROM itens_vendas")->fetchColumn() ?: 0;
$faturamento = $pdo->query("SELECT SUM(vl_total) FROM vendas")->fetchColumn() ?: 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Controle de Estoque</title>
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
                <li class="nav-item"><a class="nav-link" href="vendas.php">Vendas</a></li>
                <li class="nav-item"><a class="nav-link active fw-bold text-primary" href="relatorios.php">Relatórios</a></li>
            </ul>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Olá, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm fw-bold">Sair</a>
            </div>
        </div>
    </div>
</nav>

<div class="container mb-5">
    <div class="mb-3">
        <h3 class="fw-bold text-dark mb-0">Relatórios de Vendas</h3>
        <small class="text-muted">Acompanhe os produtos que mais e menos saíram do estoque</small>
    </div>

    <!-- Cards com Totais -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 bg-white p-3 border-start border-primary border-4">
                <div class="text-muted small fw-bold">TOTAL DE PRODUTOS VENDIDOS</div>
                <h2 class="fw-bold text-primary mb-0 mt-1"><?php echo $total_pecas; ?> unidades</h2>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 bg-white p-3 border-start border-primary border-4">
                <div class="text-muted small fw-bold">FATURAMENTO TOTAL</div>
                <h2 class="fw-bold text-dark mb-0 mt-1">R$ <?php echo number_format($faturamento, 2, ',', '.'); ?></h2>
            </div>
        </div>
    </div>

    <!-- Tabelas Lado a Lado -->
    <div class="row g-4">
        <!-- Mais Vendidos -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><span class="text-primary">Mais Vendidos</span> (Top 5)</h5>
                    <span class="badge bg-primary">Maior Saída</span>
                </div>
                <div class="card-body p-0 bg-white">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center text-nowrap" style="width: 45px;">#</th>
                                    <th>PRODUTO</th>
                                    <th class="text-nowrap text-end" style="width: 110px;">PREÇO</th>
                                    <th class="text-nowrap text-center" style="width: 110px;">VENDIDOS</th>
                                    <th class="text-nowrap text-end" style="width: 120px;">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($mais_vendidos)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Nenhuma venda registrada ainda.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $pos = 1; foreach ($mais_vendidos as $m): ?>
                                        <tr>
                                            <td class="text-center text-nowrap"><strong><?php echo $pos; ?>º</strong></td>
                                            <td><strong class="text-dark"><?php echo htmlspecialchars($m['nm_produto']); ?></strong></td>
                                            <td class="text-nowrap text-end">R$ <?php echo number_format($m['vl_venda'], 2, ',', '.'); ?></td>
                                            <td class="text-nowrap text-center"><span class="badge bg-success">+<?php echo $m['total_vendido']; ?> un</span></td>
                                            <td class="text-nowrap text-end"><strong class="text-dark">R$ <?php echo number_format($m['total_faturado'], 2, ',', '.'); ?></strong></td>
                                        </tr>
                                    <?php $pos++; endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menos Vendidos -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><span class="text-primary">Menos Vendidos</span> (Top 5)</h5>
                    <span class="badge bg-secondary">Menor Saída</span>
                </div>
                <div class="card-body p-0 bg-white">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered mb-0 align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-center text-nowrap" style="width: 45px;">#</th>
                                    <th>PRODUTO</th>
                                    <th class="text-nowrap text-end" style="width: 110px;">PREÇO</th>
                                    <th class="text-nowrap text-center" style="width: 110px;">VENDIDOS</th>
                                    <th class="text-nowrap text-center" style="width: 120px;">ESTOQUE</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($menos_vendidos)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Nenhum produto cadastrado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php $pos = 1; foreach ($menos_vendidos as $item): ?>
                                        <tr>
                                            <td class="text-center text-nowrap"><strong><?php echo $pos; ?>º</strong></td>
                                            <td><strong class="text-dark"><?php echo htmlspecialchars($item['nm_produto']); ?></strong></td>
                                            <td class="text-nowrap text-end">R$ <?php echo number_format($item['vl_venda'], 2, ',', '.'); ?></td>
                                            <td class="text-nowrap text-center">
                                                <?php if ($item['total_vendido'] == 0): ?>
                                                    <span class="badge bg-danger">0 un</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary"><?php echo $item['total_vendido']; ?> un</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-nowrap text-center"><?php echo $item['qt_estoque']; ?> un</td>
                                        </tr>
                                    <?php $pos++; endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
