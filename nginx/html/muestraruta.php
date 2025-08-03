<?php
echo "Ruta calculada: " . __DIR__ . '/../config/DB.php';
echo "<br>";
echo "¿Existe? " . (file_exists(__DIR__ . '/../config/DB.php') ? 'Sí' : 'No');
