<?php
require('fpdf/fpdf.php'); // Biblioteca FPDF para gerar PDFs

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Conectar ao banco de dados
$cx = new mysqli("127.0.0.1", "", "", "");
if ($cx->connect_error) {
    die("Erro na conexão: " . $cx->connect_error);
}

// Obter o usuário logado
$eq_user = $_SESSION["eq_user"];
$data_filtro = isset($_GET['data_reserva']) ? $_GET['data_reserva'] : '';
$destino_filtro = isset($_GET['destino']) ? $_GET['destino'] : '';

// Criar consulta dinâmica com filtros
$query = "
    SELECT r.voo_id, v.destino, v.preco, r.numero_assento, r.data_reserva, r.transacao_hash 
    FROM reservas_voo r
    JOIN voos v ON r.voo_id = v.id
    WHERE r.eq_user = ?
";

if (!empty($data_filtro)) {
    $query .= " AND r.data_reserva = ?";
}
if (!empty($destino_filtro)) {
    $query .= " AND v.destino = ?";
}

$stmt = $cx->prepare($query);
if (!$stmt) die("Erro na consulta: " . $cx->error);

if (!empty($data_filtro) && !empty($destino_filtro)) {
    $stmt->bind_param("sss", $eq_user, $data_filtro, $destino_filtro);
} elseif (!empty($data_filtro)) {
    $stmt->bind_param("ss", $eq_user, $data_filtro);
} elseif (!empty($destino_filtro)) {
    $stmt->bind_param("ss", $eq_user, $destino_filtro);
} else {
    $stmt->bind_param("s", $eq_user);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minhas Reservas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">Suas Reservas</h2>

    <form method="GET" class="mb-4 text-center" aria-label="Formulário de filtro de reservas">
    <label for="data_reserva" class="form-label" id="label_data">Filtrar por Data:</label>
    <input type="date" id="data_reserva" name="data_reserva" class="form-control w-50 mx-auto" value="<?php echo htmlspecialchars($data_filtro); ?>" aria-labelledby="label_data">

    <label for="destino" class="form-label mt-3" id="label_destino">Filtrar por Destino:</label>
    <input type="text" id="destino" name="destino" class="form-control w-50 mx-auto" value="<?php echo htmlspecialchars($destino_filtro); ?>" aria-labelledby="label_destino">

    <button type="submit" class="btn btn-primary mt-2" aria-label="Botão para filtrar reservas">Filtrar</button>
</form>


    <?php if ($result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Voo ID</th>
                        <th>Destino</th>
                        <th>Preço (BNB)</th>
                        <th>Assento</th>
                        <th>Data</th>
                        <th>Transação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                           <td aria-label="Voo ID: <?php echo htmlspecialchars($row['voo_id']); ?>">
    <?php echo htmlspecialchars($row['voo_id']); ?>
</td>
<td aria-label="Destino: <?php echo htmlspecialchars($row['destino']); ?>">
    <?php echo htmlspecialchars($row['destino']); ?>
</td>
<td aria-label="Preço: BNB <?php echo number_format($row['preco'], 8, ',', '.'); ?>">
    <?php echo number_format($row['preco'], 8, ',', '.'); ?>
</td>
<td aria-label="Número do Assento: <?php echo htmlspecialchars($row['numero_assento']); ?>">
    <?php echo htmlspecialchars($row['numero_assento']); ?>
</td>
<td aria-label="Data da Reserva: <?php echo htmlspecialchars($row['data_reserva']); ?>">
    <?php echo htmlspecialchars($row['data_reserva']); ?>
</td>
<td aria-label="Transação Hash: <?php echo htmlspecialchars($row['transacao_hash']); ?>">
    <?php echo htmlspecialchars($row['transacao_hash']); ?>
</td>

                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="gerar_pdf.php?data_reserva=<?php echo urlencode($data_filtro); ?>&destino=<?php echo urlencode($destino_filtro); ?>" class="btn btn-success">Baixar PDF</a>
        </div>

    <?php else: ?>
        <p class="text-center text-danger">Nenhuma reserva encontrada com esses filtros.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$cx->close();
?>
