<?php
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "dbname";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Definir a quantidade de voos por página
$limite = 9; 
$pagina = isset($_GET['pagina']) ? intval($_GET['pagina']) : 1;
$offset = ($pagina - 1) * $limite;

// Pesquisa personalizada
// Pesquisa personalizada
$searchQuery = "WHERE 1=1"; 
if (!empty($_GET['search_destino'])) {
    $search_destino = $conn->real_escape_string($_GET['search_destino']);
    $searchQuery .= " AND v.destino LIKE '%$search_destino%'";
}
if (!empty($_GET['search_horario'])) {
    $search_horario = $conn->real_escape_string($_GET['search_horario']);
    $searchQuery .= " AND v.horario = '$search_horario'";
}
if (!empty($_GET['search_id'])) {  // 🔥 Novo filtro para pesquisar por ID
    $search_id = intval($_GET['search_id']); // Certifica-se que o ID é um número
    $searchQuery .= " AND v.id = $search_id";
}


// Obtenção dos voos com paginação
$sql = "SELECT v.id, v.destino, v.preco, v.horario, v.metamask FROM voos v 
        $searchQuery ORDER BY v.id DESC LIMIT $limite OFFSET $offset";

$result = $conn->query($sql);

// Contar o total de voos para calcular páginas
$sqlTotal = "SELECT COUNT(*) AS total FROM voos v $searchQuery";
$resultTotal = $conn->query($sqlTotal);
$totalVoos = $resultTotal->fetch_assoc()['total'];
$totalPaginas = ceil($totalVoos / $limite);

$conn->close();
// Verifica se a cotação está armazenada e se não expirou (atualiza a cada 5 minutos)
if (!isset($_SESSION['bnb_rate']) || (time() - $_SESSION['bnb_rate_time'] > 300)) { 
    $apiUrl = "https://api.coingecko.com/api/v3/simple/price?ids=binancecoin&vs_currencies=brl";
    $response = @file_get_contents($apiUrl); // Usa '@' para evitar erros caso a API falhe

    if ($response !== false) { // Verifica se conseguiu obter dados da API
        $data = json_decode($response, true);
        $_SESSION['bnb_rate'] = $data["binancecoin"]["brl"];
        $_SESSION['bnb_rate_time'] = time(); // Guarda o tempo da última atualização
    } else {
        $_SESSION['bnb_rate'] = null;
    }
}

// Define a variável para uso no sistema
$taxaCambioBNB = $_SESSION['bnb_rate'];


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <title>Voos Disponíveis</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background-color: #f9f9f9; }
        .form-container { display: flex; justify-content: center; gap: 20px; margin-bottom: 20px; }
        .product-container { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
        .product-card { width: calc(33.33% - 20px); max-width: 300px; background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .pagination { text-align: center; margin-top: 20px; }
        .pagination a { padding: 8px 15px; margin: 5px; background: #ff9800; color: white; text-decoration: none; border-radius: 5px; }
        .btn { background: #4CAF50; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; }
  .product-card p {
    word-wrap: break-word; /* Quebra palavras longas */
    overflow-wrap: break-word; /* Garante que não ultrapasse a div */
}
.metamask-info {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

  
    </style>
</head>
<body>

    <h2>✈️ Voos Disponíveis</h2>
    <center><a href="../" class="btn">🏠 Início</a></center>
    <br>

    <!-- Formulários de Pesquisa -->
    <form method="get" action="" class="form-container">
        <div>
            <label for="search_destino">Pesquisar por Destino:</label>
            <input type="text" id="search_destino" name="search_destino">
        </div>
        <div>
            <label for="search_horario">Pesquisar por Horário:</label>
            <input type="time" id="search_horario" name="search_horario">
        </div>
        <div>
    <label for="search_id">Pesquisar por ID:</label>
    <input type="number" id="search_id" name="search_id">
</div>

        <div>
            <input type="submit" value="Pesquisar" class="btn">
        </div>
    </form>

    <div class="product-container">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='product-card'>";
                echo "<h3>✈️ Destino: " . htmlspecialchars($row["destino"]) . "</h3>";
                echo "<p><strong>ID:</strong> " . htmlspecialchars($row["id"]) . "</p>";
                $precoBRL = $row["preco"] * $taxaCambio; 
$precoBRL = $row["preco"] * $taxaCambioBNB; // ✅ Agora está correto!
echo "<p><strong>Preço:</strong> BNB " . number_format($row["preco"], 8, ',', '.') . 
    " (~R$ " . number_format($precoBRL, 2, ',', '.') . ")</p>";

                echo "<p><strong>Horário:</strong> " . htmlspecialchars($row["horario"]) . "</p>";
                echo "<p class='metamask-info'><strong>Pagamento MetaMask:</strong> " . htmlspecialchars($row["metamask"]) . "</p>";
                echo "<p><a href='https://carlitoslocacoes.com/site/reservapassagem.php?id=" . $row["id"] . "' class='btn'>🛫 Reservar Voo</a></p>";
                echo "</div>";
            }
        } else {
            echo "<p>Nenhum voo encontrado.</p>";
        }
        ?>
    </div>

    <!-- Paginação -->
    <div class='pagination'>
        <?php for ($i = 1; $i <= $totalPaginas; $i++) { ?>
            <a href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
        <?php } ?>
    </div>

</body>
