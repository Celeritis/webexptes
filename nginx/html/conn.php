<?php
$host = 'localhost:3306';
$user = 'webexptes';
$password = 'a1234567879A!$webexptes';
$dbname = 'webexptes';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
} else {
    echo "¡Conexión exitosa!";
    $conn->close();
}
?>