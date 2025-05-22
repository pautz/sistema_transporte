<?php
session_start();

// Exibir erros para depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verificar se o usuário está logado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$_SESSION["eq_user"] = $_SESSION["username"];
$eq_user = $_SESSION["eq_user"];

// Conectar ao banco de dados
$cx = new mysqli("127.0.0.1", "", "", "");
if ($cx->connect_error) {
    die("Erro na conexão com o banco: " . $cx->connect_error);
}

$username = htmlspecialchars($_SESSION["username"]);
$voo_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$vooSelecionado = null;
$enderecoDestino = "";
$assentosDisponiveis = [];
$datasReservadasPorAssento = [];

if ($voo_id) {
    // Buscar detalhes do voo
    $stmt = $cx->prepare("SELECT id, destino, preco, metamask FROM voos WHERE id = ?");
    $stmt->bind_param("i", $voo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $vooSelecionado = $result->fetch_assoc();
        $enderecoDestino = $vooSelecionado['metamask'];
    } else {
        die("Erro: Voo não encontrado.");
    }
    $stmt->close();

    // Buscar assentos disponíveis
    $stmt = $cx->prepare("SELECT numero_assento FROM assentos WHERE voo_id = ? AND pago = 0");
    $stmt->bind_param("i", $voo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $assentosDisponiveis[] = $row['numero_assento'];
    }
    $stmt->close();

    // Buscar datas reservadas por assento com transação confirmada
    $stmt = $cx->prepare("SELECT numero_assento, data_reserva FROM reservas_voo WHERE voo_id = ? AND pago = 1 AND transacao_hash IS NOT NULL");
    $stmt->bind_param("i", $voo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $datasReservadasPorAssento[$row['numero_assento']][] = $row['data_reserva'];
    }
    $stmt->close();
}

// Buscar taxa de câmbio para BNB
$bnbToBrlRate = 0;
$url = "https://api.coingecko.com/api/v3/simple/price?ids=binancecoin&vs_currencies=brl";
$json = @file_get_contents($url);
if ($json !== false) {
    $data = json_decode($json, true);
    $bnbToBrlRate = $data["binancecoin"]["brl"];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <style>
    .assento-indisponivel {
        background-color: #ffcccc !important;
        color: gray !important;
    }
</style>
    <meta charset="UTF-8">
    <title>Escolha seu Assento e Data</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/web3/1.7.5/web3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/pikaday.min.js"></script>
    <style>
/* Reset de estilos */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Corpo da página */
body {
    font-family: 'Arial', sans-serif;
    background: linear-gradient(to right, #6a11cb, #2575fc);
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

/* Área principal */
.wrapper {
    max-width: 500px;
    width: 100%;
    background: rgba(255, 255, 255, 0.15);
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
    text-align: center;
}

/* Títulos */
h2, h3 {
    font-weight: bold;
    margin-bottom: 15px;
}

/* Estilização de inputs e selects */
select, input {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    background-color: white;
    color: black;
}

/* Estilo para assentos ocupados */
.assento-indisponivel {
    background-color: #ff4d4d !important;
    color: white !important;
}

/* Estilização dos botões */
button {
    width: 100%;
    background: #ffcc00;
    color: black;
    border: none;
    padding: 12px;
    font-size: 18px;
    font-weight: bold;
    border-radius: 6px;
    cursor: pointer;
    margin-top: 15px;
    transition: 0.3s;
}

button:hover {
    background: #ff9900;
}

button:disabled {
    background: #cccccc;
    color: gray;
    cursor: not-allowed;
}

/* Responsividade */
@media screen and (max-width: 768px) {
    .wrapper {
        max-width: 90%;
        padding: 20px;
    }

    h2, h3 {
        font-size: 20px;
    }

    select, input {
        font-size: 18px;
    }

    button {
        font-size: 20px;
    }
}

    </style>
</head>
<body>
   <div class="wrapper">
    <h2>Olá, <b><?php echo $username; ?></b>. Escolha seu assento e a data do voo.</h2>

    <?php if ($vooSelecionado): ?>
        <h3>Destino: <?php echo htmlspecialchars($vooSelecionado['destino']); ?> - BNB <?php echo number_format($vooSelecionado['preco'], 8, ',', '.'); ?>  
        (≈ R$ <?php echo number_format($vooSelecionado['preco'] * $bnbToBrlRate, 2, ',', '.'); ?>)</h3>

        <label for="assento">Selecione um assento:</label>
       <select id="assento">
    <?php foreach ($assentosDisponiveis as $assento): ?>
        <option value="<?php echo $assento; ?>"><?php echo $assento; ?></option>
    <?php endforeach; ?>
</select>



       <label for="datepicker">Selecione uma Data:</label>
<input type="text" id="datepicker" readonly>


        <button id="confirmarReserva" disabled onclick="realizarPagamento()">Confirmar Reserva via MetaMask</button>
    <?php else: ?>
        <p>Voo não encontrado.</p>
    <?php endif; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var datasReservadasPorAssento = <?php echo json_encode($datasReservadasPorAssento); ?>;
    var assentoSelect = document.getElementById("assento");
    var picker;

    function atualizarCalendario(assentoSelecionado) {
        var datasParaDesabilitar = datasReservadasPorAssento[assentoSelecionado] || [];

        document.getElementById("datepicker").value = ""; 

        if (picker) {
            picker.destroy();
        }

        picker = new Pikaday({
            field: document.getElementById('datepicker'),
            format: 'YYYY-MM-DD',
            disableDayFn: function (date) {
                let dataStr = date.toISOString().split('T')[0];
                return datasParaDesabilitar.includes(dataStr);
            },
            onDraw: function () {
                setTimeout(() => {
                    document.querySelectorAll('.pika-single td').forEach(td => {
                        let dataStr = td.dataset.pikaDay;
                        if (datasParaDesabilitar.includes(dataStr)) {
                            td.classList.add("data-indisponivel");
                        }
                    });
                }, 100);
            },
            onSelect: function () {
                document.getElementById("confirmarReserva").disabled = false;
            }
        });
    }

    assentoSelect.addEventListener("change", function () {
        atualizarCalendario(this.value);
    });

    atualizarCalendario(assentoSelect.value);
});



document.getElementById("confirmarReserva").addEventListener("click", async function () {
    const assentoSelecionado = document.getElementById("assento").value;
    const dataSelecionada = document.getElementById("datepicker").value;

    // Verificar disponibilidade antes de abrir MetaMask
    const verificarDisponibilidade = await fetch("verificar_disponibilidade.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `voo_id=<?php echo $voo_id; ?>&assento=${assentoSelecionado}&data_reserva=${dataSelecionada}`
    });

    const resposta = await verificarDisponibilidade.text();

    if (resposta.includes("ocupado")) {
        alert("Erro: O assento " + assentoSelecionado + " já está reservado na data escolhida!");
        return; // **MetaMask não será aberta**
    }

    // Prosseguir com o pagamento via MetaMask
    const web3 = new Web3(window.ethereum);
    await window.ethereum.request({ method: "eth_requestAccounts" });

    const contas = await web3.eth.getAccounts();
    const contaOrigem = contas[0];

    if (!contaOrigem) {
        alert("Erro: Nenhuma conta MetaMask conectada.");
        return;
    }

    const enderecoDestino = "<?php echo $enderecoDestino; ?>";
    const valorReserva = web3.utils.toWei("<?php echo $vooSelecionado['preco']; ?>", "ether");

    try {
        const transacao = await web3.eth.sendTransaction({
            from: contaOrigem,
            to: enderecoDestino,
            value: valorReserva
        });

        const hashTransacao = transacao.transactionHash;
        
        if (!hashTransacao) {
            alert("Erro: Hash da transação não encontrada.");
            return;
        }

        alert("Transação enviada! Hash: " + hashTransacao);

        // Registrar reserva no banco de dados após confirmação do pagamento
        const response = await fetch("salvar_reserva.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `voo_id=<?php echo $voo_id; ?>&assento=${assentoSelecionado}&data_reserva=${dataSelecionada}&transacao_hash=${hashTransacao}&eq_user=<?php echo $eq_user; ?>`
        });

        const result = await response.text();
        alert(result);

    } catch (erro) {
        alert("Erro ao realizar pagamento: " + erro.message);
    }
});




</script>

</body>
</html>
