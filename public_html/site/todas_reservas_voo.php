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

// Array para armazenar as reservas
$reservas = [];

// Criar consulta dinâmica
$query = "
    SELECT r.voo_id, v.destino, v.preco, r.numero_assento, r.data_reserva, r.transacao_hash, r.eq_user 
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

// Preparar e executar consulta
$stmt = $cx->prepare($query);
if (!$stmt) die("Erro na consulta: " . $cx->error);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $reservas[] = $row;
}

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
            <input type="text" id="transacao_hash" name="transacao_hash" class="form-control w-50 mx-auto">
        </div>
        <div class="mb-3">
            <label for="voo_id" class="form-label">Voo ID:</label>
            <input type="number" id="voo_id" name="voo_id" class="form-control w-50 mx-auto">
        </div>
        <div class="mb-3">
            <label for="data_reserva" class="form-label">Data da Reserva:</label>
            <input type="date" id="data_reserva" name="data_reserva" class="form-control w-50 mx-auto">
        </div>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $reserva): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($reserva['eq_user']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['voo_id']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['destino']); ?></td>
                            <td><?php echo number_format($reserva['preco'], 8, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($reserva['numero_assento']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['data_reserva']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['transacao_hash']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center text-danger mt-4">Nenhuma reserva encontrada.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
