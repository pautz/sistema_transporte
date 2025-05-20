<?php
session_start();
$cx = new mysqli("127.0.0.1", "", "", "");
if ($cx->connect_error) {
    die("Erro de conexão: " . $cx->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $voo_id = intval($_POST["voo_id"]);
    $assento = htmlspecialchars($_POST["assento"]);
    $data_reserva = $_POST["data_reserva"];
    $transacao_hash = $_POST["transacao_hash"];

    // Inserir reserva no banco
    $stmt = $cx->prepare("INSERT INTO reservas (voo_id, assento, data_reserva, transacao_hash, pago) VALUES (?, ?, ?, ?, 1)");
    $stmt->bind_param("isss", $voo_id, $assento, $data_reserva, $transacao_hash);
    if ($stmt->execute()) {
        echo "Reserva registrada com sucesso!";
    } else {
        echo "Erro ao registrar reserva.";
    }
    $stmt->close();
}
?>
