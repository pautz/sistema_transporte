<?php
require('fpdf/fpdf.php'); // Biblioteca FPDF para gerar PDFs
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Conectar ao banco de dados
$cx = new mysqli("127.0.0.1", "", "", "");
if ($cx->connect_error) {
    die("Erro na conexão: " . $cx->connect_error);
}

// Obtendo usuário logado e filtros
$eq_user = $_SESSION["username"];
$data_filtro = isset($_GET['data_reserva']) ? $_GET['data_reserva'] : '';
$destino_filtro = isset($_GET['destino']) ? $_GET['destino'] : '';
$voo_id_filtro = isset($_GET['voo_id']) ? $_GET['voo_id'] : '';

// Criando consulta dinâmica
$query = "
    SELECT r.voo_id, v.destino, v.preco, r.numero_assento, r.data_reserva, r.transacao_hash 
    FROM reservas_voo r
    JOIN voos v ON r.voo_id = v.id
    WHERE r.eq_user = ?
";

$params = [$eq_user];
$types = "s";

if (!empty($data_filtro)) {
    $query .= " AND r.data_reserva = ?";
    $params[] = $data_filtro;
    $types .= "s";
}
if (!empty($destino_filtro)) {
    $query .= " AND v.destino = ?";
    $params[] = $destino_filtro;
    $types .= "s";
}
if (!empty($voo_id_filtro)) {
    $query .= " AND r.voo_id = ?";
    $params[] = $voo_id_filtro;
    $types .= "s";
}

// Preparar e executar consulta
$stmt = $cx->prepare($query);
if (!$stmt) {
    die("Erro na consulta: " . $cx->error);
}

$stmt->bind_param($types, ...$params);
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

    <form method="GET" class="mb-4 text-center">
        <label for="data_reserva" class="form-label">Filtrar por Data:</label>
        <input type="date" id="data_reserva" name="data_reserva" class="form-control w-50 mx-auto" value="<?php echo htmlspecialchars($data_filtro); ?>">

        <label for="destino" class="form-label mt-3">Filtrar por Destino:</label>
        <input type="text" id="destino" name="destino" class="form-control w-50 mx-auto" value="<?php echo htmlspecialchars($destino_filtro); ?>">

        <label for="voo_id" class="form-label mt-3">Filtrar por ID do Voo:</label>
        <input type="text" id="voo_id" name="voo_id" class="form-control w-50 mx-auto" value="<?php echo htmlspecialchars($voo_id_filtro); ?>">

        <button type="submit" class="btn btn-primary mt-2">Filtrar</button>
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
                            <td><?php echo htmlspecialchars($row['voo_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['destino']); ?></td>
                            <td><?php echo number_format($row['preco'], 8, ',', '.'); ?></td>
                            <td><?php echo htmlspecialchars($row['numero_assento']); ?></td>
                            <td><?php echo htmlspecialchars($row['data_reserva']); ?></td>
                            <td><?php echo htmlspecialchars($row['transacao_hash']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="gerar_pdf.php?data_reserva=<?php echo urlencode($data_filtro); ?>&destino=<?php echo urlencode($destino_filtro); ?>&voo_id=<?php echo urlencode($voo_id_filtro); ?>" class="btn btn-success">Baixar PDF</a>
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
