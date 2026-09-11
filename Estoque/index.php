<?php
require_once "conexao.php";
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    try {
        $pdo->prepare("DELETE FROM itens_vendas WHERE cd_produto = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM itens_compras WHERE cd_produto = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM produtos WHERE cd_produto = ?")->execute([$id]);
    } catch (Exception $e) {
    }
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar'])) {
    $nome   = trim($_POST['nome']);
    $custo  = floatval($_POST['preco_custo']);
    $venda  = floatval($_POST['preco_venda']);
    $qtd    = intval($_POST['quantidade']);
    $minimo = intval($_POST['estoque_minimo']);

    $stmt = $pdo->prepare("INSERT INTO produtos (nm_produto, vl_custo, vl_venda, qt_estoque, qt_estoque_minimo, cd_categoria, cd_fornecedor) VALUES (?, ?, ?, ?, ?, 1, 1)");
    $stmt->execute([$nome, $custo, $venda, $qtd, $minimo]);
    header("Location: index.php");
    exit;
}

$produtos = $pdo->query("SELECT * FROM produtos ORDER BY cd_produto DESC")->fetchAll(PDO::FETCH_ASSOC);

$total_produtos = count($produtos);
$produtos_baixo_estoque = 0;
$valor_total_estoque = 0;

foreach ($produtos as $p) {
    if ($p['qt_estoque'] <= $p['qt_estoque_minimo']) {
        $produtos_baixo_estoque++;
    }
    $valor_total_estoque += ($p['vl_venda'] * $p['qt_estoque']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - Controle de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><span class="text-primary">Controle</span> de Estoque</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active fw-bold text-primary" href="index.php">Produtos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="compras.php">Compras</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="vendas.php">Vendas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="relatorios.php">Relatórios</a>
                </li>
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
        <h3 class="fw-bold text-dark mb-0">Produtos em Estoque</h3>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-white p-3 border-start border-primary border-4">
                <div class="text-muted small fw-bold">TOTAL DE PRODUTOS</div>
                <h2 class="fw-bold text-primary mb-0 mt-1"><?php echo $total_produtos; ?></h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-white p-3 border-start border-primary border-4">
                <div class="text-muted small fw-bold">ESTOQUE BAIXO</div>
                <h2 class="fw-bold mb-0 mt-1 <?php echo ($produtos_baixo_estoque > 0) ? 'text-danger' : 'text-dark'; ?>">
                    <?php echo $produtos_baixo_estoque; ?>
                </h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-white p-3 border-start border-primary border-4">
                <div class="text-muted small fw-bold">VALOR TOTAL EM ESTOQUE</div>
                <h2 class="fw-bold text-dark mb-0 mt-1">R$ <?php echo number_format($valor_total_estoque, 2, ',', '.'); ?></h2>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-dark text-white py-3 border-bottom border-primary border-3">
            <h5 class="mb-0 fw-bold"><span class="text-primary">Cadastrar</span> Novo Produto</h5>
        </div>
        <div class="card-body p-4 bg-white">
            <form method="POST" action="index.php">
                <input type="hidden" name="cadastrar" value="1">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold text-dark">Nome do Produto</label>
                        <input type="text" name="nome" class="form-control" placeholder="Ex: Teclado Mecânico Aula F75" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-dark">Preço de Custo (R$)</label>
                        <input type="number" step="0.01" name="preco_custo" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-dark">Preço de Venda (R$)</label>
                        <input type="number" step="0.01" name="preco_venda" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-dark">Quantidade</label>
                        <input type="number" name="quantidade" class="form-control" value="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold text-dark">Estoque Mínimo</label>
                        <input type="number" name="estoque_minimo" class="form-control" value="5" required>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary fw-bold px-4">Salvar Produto</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Lista de Produtos</h5>
            <span class="badge bg-primary"><?php echo $total_produtos; ?> itens</span>
        </div>
        <div class="card-body p-0 bg-white">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center text-nowrap" style="width: 50px;">#</th>
                            <th>NOME DO PRODUTO</th>
                            <th class="text-nowrap text-end" style="width: 130px;">PREÇO CUSTO</th>
                            <th class="text-nowrap text-end" style="width: 130px;">PREÇO VENDA</th>
                            <th class="text-nowrap text-center" style="width: 110px;">ESTOQUE</th>
                            <th class="text-nowrap text-center" style="width: 130px;">STATUS</th>
                            <th class="text-nowrap text-center" style="width: 160px;">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($produtos)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Nenhum produto cadastrado.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($produtos as $p): ?>
                                <tr>
                                    <td class="text-center text-muted"><?php echo $p['cd_produto']; ?></td>
                                    <td><strong class="text-dark"><?php echo htmlspecialchars($p['nm_produto']); ?></strong></td>
                                    <td class="text-nowrap text-end">R$ <?php echo number_format($p['vl_custo'], 2, ',', '.'); ?></td>
                                    <td class="text-nowrap text-end"><strong class="text-primary">R$ <?php echo number_format($p['vl_venda'], 2, ',', '.'); ?></strong></td>
                                    <td class="text-nowrap text-center"><strong><?php echo $p['qt_estoque']; ?></strong> un</td>
                                    <td class="text-nowrap text-center">
                                        <?php if ($p['qt_estoque'] == 0): ?>
                                            <span class="badge bg-secondary">Esgotado</span>
                                        <?php elseif ($p['qt_estoque'] <= $p['qt_estoque_minimo']): ?>
                                            <span class="badge bg-danger">Estoque Baixo</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap text-center">
                                        <a href="editar_produto.php?id=<?php echo $p['cd_produto']; ?>" class="btn btn-primary btn-sm fw-bold me-1">Editar</a>
                                        <a href="index.php?excluir=<?php echo $p['cd_produto']; ?>" class="btn btn-danger btn-sm fw-bold" onclick="return confirm('Deseja realmente excluir este produto?')">Excluir</a>
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
