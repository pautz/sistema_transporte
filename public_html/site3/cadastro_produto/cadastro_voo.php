<?php
// Inicializar a sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: https://carlitoslocacoes.com/site/login.php");
    exit;
}

// Conexão com o banco de dados
$servername = "localhost";
$username = "";
$password = "";
$dbname = "";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$eq_user = $_SESSION["username"];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Cadastro de Voos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; }
        input[type="submit"] { width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; font-size: 16px; }
    </style>
</head>
<body>
    <h2>Cadastro de Voos</h2>
    <form action="" method="post">
        <label for="destino">Destino:</label>
        <input type="text" id="destino" name="destino" required>

        <label for="preco">Preço em BNB:</label>
<input type="number" id="preco" name="preco" step="0.000000001" required>


        <label for="quantidade_assentos">Quantidade de Assentos:</label>
        <input type="number" id="quantidade_assentos" name="quantidade_assentos" required>

        <label for="metamask">Endereço MetaMask para Pagamento:</label>
        <input type="text" id="metamask" name="metamask" required>
        
        <label for="horario">Horário do Voo:</label>
<input type="time" id="horario" name="horario" required>


        <input type="submit" value="Cadastrar Voo">
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $destino = htmlspecialchars($_POST['destino']);
        $preco = floatval($_POST['preco']);
        $quantidade_assentos = intval($_POST['quantidade_assentos']);
        $metamask = htmlspecialchars($_POST['metamask']);
        $eq_user = $_SESSION['username'];
        $horario = htmlspecialchars($_POST['horario']);

        // Inserir voo no banco
        $stmt = $conn->prepare("INSERT INTO voos (destino, preco, metamask, horario) VALUES (?, ?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("sdss", $destino, $preco, $metamask, $horario);


            if ($stmt->execute()) {
                $voo_id = $stmt->insert_id;

                // Criar os assentos automaticamente
                for ($i = 1; $i <= $quantidade_assentos; $i++) {
                    $numero_assento = "A" . $i;
                    $stmt_assento = $conn->prepare("INSERT INTO assentos (voo_id, numero_assento, pago) VALUES (?, ?, 0)");
                    if ($stmt_assento) {
                        $stmt_assento->bind_param("is", $voo_id, $numero_assento);
                        $stmt_assento->execute();
                    }
                }

                echo "<h3 style='color: green;'>Voo cadastrado com sucesso, incluindo $quantidade_assentos assentos!</h3>";
            } else {
                echo "<h3 style='color: red;'>Erro ao cadastrar voo.</h3>";
            }
            $stmt->close();
        }
    }

    $conn->close();
    ?>
</body>
</html>
