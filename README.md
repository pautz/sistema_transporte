# sistema_transporte
https://carlitoslocacoes.com/site2/
https://www.youtube.com/shorts/ogECl5LXvU0
https://www.youtube.com/watch?v=8-an9CTjsuk

O que é o sistema?
O sistema deste repositório é chamado S.T.C: Sistema de Transporte Cooperativo para deficientes visuais.

Objetivo do sistema
O S.T.C foi desenvolvido para facilitar a reserva, o cadastro e o gerenciamento de passagens de transporte especialmente voltadas a pessoas com deficiência visual. O sistema utiliza integração com blockchain (BNB/MetaMask) para pagamento e registro seguro das reservas.



Principais características e funcionamento
Cadastro de Passagens (Voos):
Um operador (administrador) cadastra previamente os voos/passagens no sistema, informando dados como origem, destino, data, assentos disponíveis, preço e carteira BNB para recebimento.
Essas passagens ficam disponíveis para os usuários na interface web, que podem consultá-las e efetuar reservas.
O cadastro normalmente ocorre em uma área administrativa com formulário específico (ex: cadastro_voo.php), que grava os dados no banco de dados.



Reserva de Passagens pelo Usuário:
Usuários se cadastram no sistema e inserem informações de identificação.
Consultam os voos disponíveis e escolhem o assento e a data desejada.
Antes de finalizar a reserva, o sistema verifica em tempo real se o assento já não foi reservado na data escolhida.
O pagamento é feito via MetaMask utilizando BNB, integrando com a blockchain para dar mais segurança e rastreabilidade.
Após o pagamento confirmado, a reserva é registrada no banco de dados.



Confirmação de Reserva e Embarque:
Depois da reserva e pagamento, o usuário pode gerar um comprovante com QR Code.
No embarque, esse QR Code é verificado para liberar o embarque do passageiro – o sistema impede embarques duplicados para a mesma reserva.



Consulta e Gerenciamento:
Usuários e operadores podem consultar o histórico completo de reservas e embarques, com filtros por usuário, voo, data, etc.
O sistema integra com uma API de câmbio para converter o valor da passagem de BNB para reais em tempo real.



Arquitetura básica
Front-end: Interface web para cadastro, reserva, consulta e embarque.
Back-end: Scripts PHP que processam cadastros, reservas, pagamentos, embarques e interagem com o banco de dados.



Banco de Dados: MySQL para armazenar usuários, reservas, voos, etc.

Segurança: Uso obrigatório de HTTPS, políticas de segurança e proteção contra ataques comuns.



Recursos e demonstrações
Sistema disponível em produção: https://carlitoslocacoes.com/site2/

Vídeos demonstrativos:
Demonstração 1 - Shorts
https://www.youtube.com/shorts/ogECl5LXvU0

Demonstração 2 - Completa
https://www.youtube.com/watch?v=8-an9CTjsuk

<br><br>
Altere as configurações de conexão de todos arquivos. create_dbname.sql para criar o banco de dados.
Necessario saldo BNB para reservar passagem, pode enviar para sua propria carteira, utilizando seu endereço no cadastro de voo.

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

