<?php
file_put_contents('C:/devs/Logs-webextes/logs.TXT', date('Y-m-d H:i:s').' - Usuario: '.($_SESSION['usuario'] ?? 'NO_SESSION').PHP_EOL, FILE_APPEND);


file_put_contents('C:/devs/Logs-webextes/logs.TXT', date('Y-m-d H:i:s').' - Usuario: '.($_SESSION['usuario'] ?? 'NO_SESSION').PHP_EOL, FILE_APPEND);

?>


Guardar un arreglo asociativo

<?php
$datos = [
    "usuario" => "renerivero",
    "nombre" => "René Rivero",
    "rol" => "Administrador"
];

$linea = json_encode($datos);
file_put_contents("datos_usuario.txt", $linea . PHP_EOL);  // o FILE_APPEND para acumular
?>
