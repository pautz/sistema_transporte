<?php
session_start();

// Conectar ao banco de dados
$cx = new mysqli("127.0.0.1", "username", "password", "dbname");
if ($cx->connect_error) {
    die("Erro na conexão: " . $cx->connect_error);
}

// Obtendo parâmetros via GET
$transacao_hash = isset($_GET['transacao_hash']) ? trim($_GET['transacao_hash']) : '';
$voo_id = isset($_GET['voo_id']) ? trim($_GET['voo_id']) : '';
$data_reserva = isset($_GET['data_reserva']) ? trim($_GET['data_reserva']) : '';

// Parâmetros de paginação
$por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $por_pagina;

// Criar consulta dinâmica com paginação
$query = "
    SELECT r.voo_id, v.destino, v.preco, r.numero_assento, r.data_reserva, r.transacao_hash, r.eq_user,
           r.embarcado, r.data_embarque
    FROM reservas_voo r
    JOIN voos v ON r.voo_id = v.id
    WHERE 1=1
";

$params = [];
$types = "";

if (!empty($transacao_hash)) {
    $query .= " AND r.transacao_hash = ?";
    $params[] = $transacao_hash;
    $types .= "s";
}
if (!empty($voo_id)) {
    $query .= " AND r.voo_id = ?";
    $params[] = $voo_id;
    $types .= "i";
}
if (!empty($data_reserva)) {
    $query .= " AND r.data_reserva = ?";
    $params[] = $data_reserva;
    $types .= "s";
}

$query .= " LIMIT ? OFFSET ?";
$params[] = $por_pagina;
$types .= "ii";
$params[] = $offset;

// Preparar e executar consulta
$stmt = $cx->prepare($query);
if (!$stmt) die("Erro na consulta: " . $cx->error);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$reservas = [];
while ($row = $result->fetch_assoc()) {
    $reservas[] = $row;
}

// Contagem total de registros para paginação
$query_total = "SELECT COUNT(*) AS total FROM reservas_voo";
$total_result = $cx->query($query_total);
$total_registros = $total_result->fetch_assoc()['total'];
$total_paginas = ceil($total_registros / $por_pagina);

$stmt->close();
$cx->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Consultar Reservas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center">Buscar Reservas</h2>

    <form method="GET" class="text-center">
        <div class="mb-3">
            <label for="transacao_hash" class="form-label">Transação Hash:</label>
            <input type="text" id="transacao_hash" name="transacao_hash" class="form-control w-50 mx-auto"
                   value="<?= htmlspecialchars($_GET['transacao_hash'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="voo_id" class="form-label">Voo ID:</label>
            <input type="number" id="voo_id" name="voo_id" class="form-control w-50 mx-auto"
                   value="<?= htmlspecialchars($_GET['voo_id'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label for="data_reserva" class="form-label">Data da Reserva:</label>
            <input type="date" id="data_reserva" name="data_reserva" class="form-control w-50 mx-auto"
                   value="<?= htmlspecialchars($_GET['data_reserva'] ?? '') ?>">
        </div>
        <input type="hidden" name="pagina" value="1"> <!-- Sempre inicia na primeira página -->
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>

    <?php if (!empty($reservas)): ?>
        <div class="mt-4">
            <h3 class="text-center">Detalhes das Reservas</h3>
            <table class="table table-striped table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Usuário</th>
                        <th>Voo ID</th>
                        <th>Destino</th>
                        <th>Preço (BNB)</th>
                        <th>Assento</th>
                        <th>Data</th>
                        <th>Transação</th>
                        <th>Embarcou?</th>
                        <th>Data do Embarque</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td><?= htmlspecialchars($reserva['eq_user']) ?></td>
                            <td><?= htmlspecialchars($reserva['voo_id']) ?></td>
                            <td><?= htmlspecialchars($reserva['destino']) ?></td>
                            <td><?= number_format($reserva['preco'], 8, ',', '.') ?></td>
                            <td><?= htmlspecialchars($reserva['numero_assento']) ?></td>
                            <td><?= htmlspecialchars($reserva['data_reserva']) ?></td>
                            <td><?= htmlspecialchars($reserva['transacao_hash']) ?></td>
                            <td><?= $reserva['embarcado'] ? '<span class="text-success">Sim</span>' : '<span class="text-danger">Não</span>' ?></td>
                            <td><?= $reserva['data_embarque'] ? htmlspecialchars($reserva['data_embarque']) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Paginação -->
            <nav class="text-center mt-4">
                <ul class="pagination">
                    <?php if ($pagina_atual > 1): ?>
                        <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina_atual - 1 ?>">Anterior</a></li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <li class="page-item <?= ($i == $pagina_atual) ? 'active' : '' ?>">
                            <a class="page-link" href="?pagina=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($pagina_atual < $total_paginas): ?>
                        <li class="page-item"><a class="page-link" href="?pagina=<?= $pagina_atual + 1 ?>">Próximo</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php else: ?>
        <p class="text-center text-danger mt-4">Nenhuma reserva encontrada.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
