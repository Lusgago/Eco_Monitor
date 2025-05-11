<?php
//credenciais do PHPMyAdmin para conexão com o banco de dados via PHP
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "usuários";

//conexão com banco de dados
$conexao = new mysqli($servername, $username, $password, $dbname);

//verifica se a conexão foi bem sucedida
if ($conexao -> connect_error){
    die("Erro na conexão:" . $conexao -> connect_error);
}
?>