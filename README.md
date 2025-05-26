# sistema_transporte
https://carlitoslocacoes.com/site2/ <br>
https://www.youtube.com/shorts/ogECl5LXvU0 <br>
https://www.youtube.com/watch?v=8-an9CTjsuk <br>

O que é o sistema?<br>
O sistema deste repositório é chamado S.T.C: Sistema de Transporte Cooperativo para deficientes visuais.<br>

Objetivo do sistema<br>
O S.T.C foi desenvolvido para facilitar a reserva, o cadastro e o gerenciamento de passagens de transporte especialmente voltadas a pessoas com deficiência visual. O sistema utiliza integração com blockchain (BNB/MetaMask) para pagamento e registro seguro das reservas.<br>



Principais características e funcionamento<br>
Cadastro de Passagens (Voos):<br>
Um operador (administrador) cadastra previamente os voos/passagens no sistema, informando dados como origem, destino, data, assentos disponíveis, preço e carteira BNB para recebimento.
Essas passagens ficam disponíveis para os usuários na interface web, que podem consultá-las e efetuar reservas.<br>
O cadastro normalmente ocorre em uma área administrativa com formulário específico (ex: cadastro_voo.php), que grava os dados no banco de dados.



Reserva de Passagens pelo Usuário:<br>
Usuários se cadastram no sistema e inserem informações de identificação.<br>
Consultam os voos disponíveis e escolhem o assento e a data desejada.<br>
Antes de finalizar a reserva, o sistema verifica em tempo real se o assento já não foi reservado na data escolhida.<br>
O pagamento é feito via MetaMask utilizando BNB, integrando com a blockchain para dar mais segurança e rastreabilidade.<br>
Após o pagamento confirmado, a reserva é registrada no banco de dados.<br>



Confirmação de Reserva e Embarque:<br>
Depois da reserva e pagamento, o usuário pode gerar um comprovante com QR Code.<br>
No embarque, esse QR Code é verificado para liberar o embarque do passageiro – o sistema impede embarques duplicados para a mesma reserva.<br>



Consulta e Gerenciamento:<br>
Usuários e operadores podem consultar o histórico completo de reservas e embarques, com filtros por usuário, voo, data, etc.<br>
O sistema integra com uma API de câmbio para converter o valor da passagem de BNB para reais em tempo real.<br>



Arquitetura básica<br>
Front-end: Interface web para cadastro, reserva, consulta e embarque.<br>
Back-end: Scripts PHP que processam cadastros, reservas, pagamentos, embarques e interagem com o banco de dados.<br>



Banco de Dados: MySQL para armazenar usuários, reservas, voos, etc.<br>

Segurança: Uso obrigatório de HTTPS, políticas de segurança e proteção contra ataques comuns.<br>



Recursos e demonstrações<br>
Sistema disponível em produção: https://carlitoslocacoes.com/site2/ <br>

Vídeos demonstrativos:<br>
Demonstração 1 - Shorts <br>
https://www.youtube.com/shorts/ogECl5LXvU0 <br>

Demonstração 2 - Completa <br>
https://www.youtube.com/watch?v=8-an9CTjsuk <br>

<br><br>
Altere as configurações de conexão de todos arquivos. create_dbname.sql para criar o banco de dados.<br>
Necessario saldo BNB para reservar passagem, pode enviar para sua propria carteira, utilizando seu endereço no cadastro de voo.<br>

<br><br>config.php e register.php

<br>
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'username');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'dbname');
<br><br>

cadastro_voo.php e voos.php
<br><br>
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "dbname";
<br><br>
reservapassagem.php
<br><br>
$cx = new mysqli("127.0.0.1", "username", "password", "dbname");


<br>
![image](https://github.com/user-attachments/assets/0bd7abbb-f34a-44fd-b48e-0ec03d20706f)
<br>
![image](https://github.com/user-attachments/assets/6018f584-ecc6-4b38-a713-937c7bde746b)
<br>
![image](https://github.com/user-attachments/assets/9b2842d8-883a-4bc4-b0f4-eea226530e7e)

