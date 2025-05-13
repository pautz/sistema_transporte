# sistema_transporte
S.T.C: Sistema de Transporte Cooperativo para deficientes visuais
<br>
Altere as configurações de database de todos arquivos.
<br>config.php e register.php

<br>
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'username');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'dbname');
<br>

cadastro_voo.php e voos.php
<br>
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "dbname";
<br>
reservapassagem.php
<br>
$cx = new mysqli("127.0.0.1", "username", "password", "dbname");
