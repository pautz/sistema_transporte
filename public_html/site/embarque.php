<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Conectar ao banco de dados
$cx = new mysqli("127.0.0.1", "username", "password", "dbname");
if ($cx->connect_error) {
    die("Erro na conexão: " . $cx->connect_error);
}

// Obtendo usuário autenticado
$eq_user = $_SESSION["username"];

// Verificar se o passageiro já embarcou com transação hash e usuário da sessão
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["transacao_hash"])) {
    $transacao_hash = $_POST["transacao_hash"];

    // Verifica se a reserva pertence ao usuário logado e se já embarcou
    $query_check = "SELECT embarcado FROM reservas_voo WHERE eq_user = ? AND transacao_hash = ?";
    $stmt_check = $cx->prepare($query_check);
    $stmt_check->bind_param("ss", $eq_user, $transacao_hash);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $row = $result_check->fetch_assoc();

    if (!$row) {
        echo "<p class='text-danger text-center'>Erro: Esta reserva não pertence ao usuário logado.</p>";
    } elseif ($row["embarcado"] == 1) {
        echo "<p class='text-danger text-center'>Este passageiro já embarcou e não pode ser alterado.</p>";
    } else {
        // Registra o embarque com timestamp imutável
        $query_update = "UPDATE reservas_voo SET embarcado = 1, data_embarque = NOW() WHERE eq_user = ? AND transacao_hash = ?";
        $stmt_update = $cx->prepare($query_update);
        $stmt_update->bind_param("ss", $eq_user, $transacao_hash);
        if ($stmt_update->execute()) {
            echo "<p class='text-success text-center'>Embarque registrado com sucesso!</p>";
        } else {
            echo "<p class='text-danger text-center'>Erro ao registrar embarque.</p>";
        }
        $stmt_update->close();
    }

    $stmt_check->close();
}

$cx->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Embarque</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="text-center mb-4">Registro de Embarque</h2>

    <form method="POST" class="text-center">
        <label for="transacao_hash" class="form-label">Digite o Hash da Transação:</label>
        <input type="text" id="transacao_hash" name="transacao_hash" class="form-control w-50 mx-auto" required>
        <button type="submit" class="btn btn-primary mt-2">Registrar Embarque</button>
    </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
