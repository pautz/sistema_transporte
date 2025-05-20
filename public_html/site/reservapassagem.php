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

// Definir `eq_user` como o nome de usuário na sessão, caso não esteja definido
if (!isset($_SESSION["eq_user"]) && isset($_SESSION["username"])) {
    $_SESSION["eq_user"] = $_SESSION["username"]; // Agora `eq_user` pega o nome do usuário
}

$eq_user = $_SESSION["eq_user"];


// Conectar ao banco de dados
$cx = new mysqli("127.0.0.1", "", "", "");
if ($cx->connect_error) {
    die("Erro na conexão com o banco: " . $cx->connect_error);
}

// Capturar dados

$username = isset($_SESSION["username"]) ? htmlspecialchars($_SESSION["username"]) : "Usuário";
$voo_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$vooSelecionado = null;
$assentosDisponiveis = [];
$assentosPagos = [];
$datasReservadasPorAssento = [];
$enderecoDestino = "";

if ($voo_id) {
    // Buscar detalhes do voo
    $stmt = $cx->prepare("SELECT id, destino, preco, metamask FROM voos WHERE id = ?");
    if (!$stmt) die("Erro na preparação da consulta: " . $cx->error);
    
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

    // Buscar assentos disponíveis e pagos
    $stmt = $cx->prepare("SELECT numero_assento, pago FROM assentos WHERE voo_id = ?");
    if (!$stmt) die("Erro na consulta de assentos: " . $cx->error);
    
    $stmt->bind_param("i", $voo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if ($row['pago'] == 1) {
            $assentosPagos[] = $row['numero_assento'];
        } else {
            $assentosDisponiveis[] = $row['numero_assento'];
        }
    }
    $stmt->close();

    // Buscar datas reservadas por assento
    $stmt = $cx->prepare("SELECT numero_assento, data_reserva FROM reservas_voo WHERE voo_id = ? AND pago = 1");
    if (!$stmt) die("Erro na consulta de reservas: " . $cx->error);

    $stmt->bind_param("i", $voo_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $assento = $row['numero_assento'];
        $data = $row['data_reserva'];

        if (!isset($datasReservadasPorAssento[$assento])) {
            $datasReservadasPorAssento[$assento] = [];
        }

        $datasReservadasPorAssento[$assento][] = $data;
    }
    $stmt->close();
}

// Inserção da reserva no banco de dados com eq_user
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assentoSelecionado = $_POST['assento'];
    $dataSelecionada = $_POST['data_reserva'];
    $hashTransacao = $_POST['transacao_hash'];

    $stmt = $cx->prepare("INSERT INTO reservas_voo (eq_user, voo_id, numero_assento, data_reserva, transacao_hash) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) die("Erro na preparação da consulta: " . $cx->error);

    $stmt->bind_param("sisss", $eq_user, $voo_id, $assentoSelecionado, $dataSelecionada, $hashTransacao);
    $stmt->execute();
    $stmt->close();
}


if (!isset($_SESSION['bnb_rate']) || (time() - $_SESSION['bnb_rate_time'] > 300)) { // Atualiza a cada 5 minutos
    $url = "https://api.coingecko.com/api/v3/simple/price?ids=binancecoin&vs_currencies=brl";
    $json = @file_get_contents($url);

    if ($json !== false) {
        $data = json_decode($json, true);
        $_SESSION['bnb_rate'] = $data["binancecoin"]["brl"];
        $_SESSION['bnb_rate_time'] = time(); // Armazena tempo da última atualização
    } else {
        $_SESSION['bnb_rate'] = null;
    }
}

$bnbToBrlRate = $_SESSION['bnb_rate'];

?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Escolha seu Assento e Data</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/css/pikaday.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/web3/1.7.5/web3.min.js"></script>
<style>/* Reset básico para eliminar margens e paddings padrão */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Estilo geral do corpo */
body {
    font-family: Arial, sans-serif;
    background-color: #f5f5f5;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
}

/* Container principal */
.wrapper {
    max-width: 600px;
    width: 90%;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* Estilização dos elementos do formulário */
select, button, input {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

/* Botão principal */
button {
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background-color: #0056b3;
}

/* Estilo para assentos ocupados */
.ocupado {
    color: red;
    font-weight: bold;
}

/* Responsividade */
@media screen and (max-width: 768px) {
    .wrapper {
        width: 95%;
        padding: 15px;
    }

    select, button, input {
        font-size: 16px;
    }

    h2 {
        font-size: 20px;
    }
}

@media screen and (max-width: 480px) {
    .wrapper {
        width: 100%;
        padding: 10px;
    }

    select, button, input {
        font-size: 14px;
    }

    h2 {
        font-size: 18px;
    }
}
</style>
</head>
<body>
   <div class="wrapper" aria-label="Seleção de assento e data do voo">
    <h2>Olá, <b><?php echo $username; ?></b>. Escolha seu assento e a data do voo.</h2>

    <?php if ($vooSelecionado): ?>
        <h3 aria-label="Destino do voo: <?php echo htmlspecialchars($vooSelecionado['destino']); ?> - Preço: BNB <?php echo number_format($vooSelecionado['preco'], 8, ',', '.'); ?>  
        (≈ R$ <?php echo number_format($vooSelecionado['preco'] * $bnbToBrlRate, 2, ',', '.'); ?>)">
            Destino: <?php echo htmlspecialchars($vooSelecionado['destino']); ?> - BNB <?php echo number_format($vooSelecionado['preco'], 8, ',', '.'); ?>  
            (≈ R$ <?php echo number_format($vooSelecionado['preco'] * $bnbToBrlRate, 2, ',', '.'); ?>)
        </h3>

        <label for="assento" aria-label="Selecione um assento disponível">Selecione um assento:</label>
        <select id="assento">
            <?php foreach ($assentosDisponiveis as $assento): ?>
                <option value="<?php echo $assento; ?>" aria-label="Assento <?php echo $assento; ?>">
                    <?php echo $assento; ?>
                </option>
            <?php endforeach; ?>

            <?php foreach ($assentosPagos as $assento): ?>
                <option value="<?php echo $assento; ?>" disabled class="ocupado" aria-label="Assento <?php echo $assento; ?> já ocupado">
                    <?php echo $assento; ?> (Ocupado)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="datepicker" aria-label="Selecione a data do voo">Selecione uma Data:</label>
        <input type="text" id="datepicker" aria-label="Campo para escolher a data do voo">
        
        <button id="confirmarReserva" disabled onclick="realizarPagamento()" aria-label="Botão para confirmar reserva via MetaMask">
            Confirmar Reserva via MetaMask
        </button>
    <?php else: ?>
        <p aria-label="Mensagem de erro: voo não encontrado">Voo não encontrado.</p>
    <?php endif; ?>
</div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pikaday/1.8.0/pikaday.min.js"></script>
    
    <script>document.addEventListener("DOMContentLoaded", function () {
    var datasReservadasPorAssento = <?php echo json_encode($datasReservadasPorAssento); ?>;
    var picker;

    function atualizarCalendario(assentoSelecionado) {
        var datasParaDesabilitar = datasReservadasPorAssento[assentoSelecionado] || [];

        // Certifique-se de que o campo está limpo antes de recriar o calendário
        document.getElementById("datepicker").value = "";

        if (picker) {
            picker.destroy(); // Remove o calendário anterior corretamente
        }

        picker = new Pikaday({
            field: document.getElementById('datepicker'),
            format: 'YYYY-MM-DD',
            disableDayFn: function (date) {
                var formattedDate = date.toISOString().split('T')[0];
                return datasParaDesabilitar.includes(formattedDate);
            },
            onSelect: function () {
                document.getElementById("confirmarReserva").disabled = false;
            }
        });

        // Forçar atualização do campo para garantir que ele seja interativo
        setTimeout(() => {
            document.getElementById('datepicker').focus();
        }, 100);
    }

    document.getElementById("assento").addEventListener("change", function () {
        var assentoSelecionado = this.value;
        atualizarCalendario(assentoSelecionado);
    });

    // Inicializar o calendário ao carregar a página (para o primeiro assento)
    var assentoInicial = document.getElementById("assento").value;
    atualizarCalendario(assentoInicial);
    
    // Função para validar transação na blockchain
    async function validarTransacao(hash) {
        await window.ethereum.request({ method: "wallet_switchEthereumChain", params: [{ chainId: "0x38" }] }); const web3 = new Web3(window.ethereum);

        try {
            const receipt = await web3.eth.getTransactionReceipt(hash);
            return receipt && receipt.status; // Retorna 'true' se a transação foi bem-sucedida
        } catch (erro) {
            console.error("Erro ao validar transação:", erro);
            return false;
        }
    }

    // Função para realizar pagamento via MetaMask
    async function realizarPagamento() {
        const assentoSelecionado = document.getElementById("assento").value;
        const dataSelecionada = document.getElementById("datepicker").value;
        await window.ethereum.request({ method: "wallet_switchEthereumChain", params: [{ chainId: "0x38" }] }); const web3 = new Web3(window.ethereum);

        try {
            await window.ethereum.request({ method: "eth_requestAccounts" });
            const contas = await web3.eth.getAccounts();
            const contaOrigem = contas[0];
            const valorReserva = web3.utils.toWei("<?php echo $vooSelecionado['preco']; ?>", "ether"); // Ainda usa "ether" pois Web3 usa esse formato para BNB também

            // Realizar transação via MetaMask
            const transacao = await web3.eth.sendTransaction({
    from: contaOrigem,
    to: "<?php echo $enderecoDestino; ?>",
    value: valorReserva
});


            const hashTransacao = transacao.transactionHash;

            // Validar se a transação foi confirmada na blockchain
            const transacaoConfirmada = await validarTransacao(hashTransacao);

            if (transacaoConfirmada) {
                alert("Pagamento confirmado com sucesso! Hash: " + hashTransacao);

                // Enviar dados ao servidor para registrar no banco
                fetch("salvar_reserva.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `voo_id=<?php echo $voo_id; ?>&assento=${assentoSelecionado}&data_reserva=${dataSelecionada}&transacao_hash=${hashTransacao}`
                }).then(response => response.text())
                  .then(result => alert(result));
            } else {
                alert("Falha na confirmação da transação. Tente novamente.");
            }

        } catch (erro) {
            alert("Erro ao realizar pagamento: " + erro.message);
        }
    }

    document.getElementById("confirmarReserva").addEventListener("click", realizarPagamento);
});

</script>

</body>
</html>
