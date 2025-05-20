<?php
session_start();

// Conectar ao banco de dados
$cx = new mysqli("127.0.0.1", "", "", "");
if ($cx->connect_error) {
    die("Erro na conexão: " . $cx->connect_error);
}

// Verifica se o hash da transação foi enviado via GET
$transacao_hash = isset($_GET['transacao_hash']) ? trim($_GET['transacao_hash']) : '';

$reserva = null;

if (!empty($transacao_hash)) {
    // Consulta ao banco de dados
    $stmt = $cx->prepare("
        SELECT r.voo_id, v.destino, v.preco, r.numero_assento, r.data_reserva, r.transacao_hash, r.eq_user 
        FROM reservas_voo r
        JOIN voos v ON r.voo_id = v.id
        WHERE r.transacao_hash = ?
    ");

    if (!$stmt) die("Erro na consulta: " . $cx->error);

    $stmt->bind_param("s", $transacao_hash);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $reserva = $result->fetch_assoc();
    }

    $stmt->close();
}

$cx->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Consultar Reserva</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center">Buscar Reserva por Hash de Transação</h2>
    
    <form method="GET" class="text-center">
        <div class="mb-3">
            <label for="transacao_hash" class="form-label">Transação Hash:</label>
            <input type="text" id="transacao_hash" name="transacao_hash" class="form-control w-50 mx-auto" required>
        </div>
        <button type="submit" class="btn btn-primary">Buscar</button>
    </form>

    <?php if ($reserva): ?>
        <div class="mt-4">
            <h3 class="text-center">Detalhes da Reserva</h3>
            <div class="table-responsive">
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
                        <tr>
                            <td><?php echo htmlspecialchars($reserva['eq_user']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['voo_id']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['destino']); ?></td>
                            <td><?php echo number_format($reserva['preco'], 8, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($reserva['numero_assento']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['data_reserva']); ?></td>
                            <td><?php echo htmlspecialchars($reserva['transacao_hash']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    <?php elseif (!empty($transacao_hash)): ?>
        <p class="text-center text-danger mt-4">Nenhuma reserva encontrada para esta transação.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
