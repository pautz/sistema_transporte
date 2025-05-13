# sistema_transporte
S.T.C: Sistema de Transporte Cooperativo para deficientes visuais

Altere as configurações de database de todos arquivos.
config.php e register.php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'username');
define('DB_PASSWORD', 'password');
define('DB_NAME', 'dbname');

cadastro_voo.php e voos.php
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "dbname";

reservapassagem.php
$cx = new mysqli("127.0.0.1", "username", "password", "dbname");
