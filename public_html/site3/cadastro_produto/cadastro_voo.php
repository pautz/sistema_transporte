<?php
// Inicializar a sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$eq_user = $_SESSION["username"];

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: https://carlitoslocacoes.com/site/login.php");
    exit;
}

// Conexão com o banco de dados
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "dbname";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$eq_user = $_SESSION["username"];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Cadastro de Quartos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        form { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9; }
        label { display: block; margin-bottom: 8px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 5px; }
        input[type="submit"] { width: 100%; padding: 10px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; font-size: 16px; }
    </style>
</head>
<body>
    <h2>Cadastro de Quartos</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <label for="nome">Nome do Quarto:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="preco">Preço:</label>
        <input type="number" id="preco" name="preco" step="0.01" required>

        <label for="cidade">Cidade:</label>
        <input type="text" id="cidade" name="cidade" required>

        <label for="estado">Estado:</label>
        <input type="text" id="estado" name="estado" required>
        
        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone" required>
        
        <label for="metamask">Endereço MetaMask:</label>
        <input type="text" id="metamask" name="metamask" required>


        <label for="imagem">Imagens do Quarto:</label>
        <input type="file" id="imagem" name="imagens[]" multiple required>

        <input type="submit" value="Cadastrar">
    </form>

    <?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = htmlspecialchars($_POST['nome']);
    $preco = floatval($_POST['preco']);
    $cidade = htmlspecialchars($_POST['cidade']);
    $estado = htmlspecialchars($_POST['estado']);
    $telefone = htmlspecialchars($_POST['telefone']);
    $eq_user = $_SESSION['username'];
    $metamask = htmlspecialchars($_POST['metamask']); // Novo campo para MetaMask

    // Diretório correto para upload das imagens
    $target_dir = "uploads/quartos/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Inserir no banco de dados, incluindo o endereço MetaMask
    $stmt = $conn->prepare("INSERT INTO quartos (nome, preco, cidade, estado, telefone, eq_user, metamask) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sdsssss", $nome, $preco, $cidade, $estado, $telefone, $eq_user, $metamask);

        if ($stmt->execute()) {
            $quarto_id = $stmt->insert_id;

            // Upload de imagens
            foreach ($_FILES['imagens']['name'] as $key => $imagem) {
                $target_file = $target_dir . basename($imagem);
                if (move_uploaded_file($_FILES['imagens']['tmp_name'][$key], $target_file)) {
                    $stmt_img = $conn->prepare("INSERT INTO imagens_quarto (quarto_id, imagem) VALUES (?, ?)");
                    if ($stmt_img) {
                        $stmt_img->bind_param("is", $quarto_id, $target_file);
                        $stmt_img->execute();
                    }
                }
            }

            echo "<h3 style='color: green;'>Quarto cadastrado com sucesso, incluindo endereço MetaMask!</h3>";
        } else {
            echo "<h3 style='color: red;'>Erro ao cadastrar quarto com endereço MetaMask.</h3>";
        }
        $stmt->close();
    }
}

$conn->close();
?>

</body>
</html>
