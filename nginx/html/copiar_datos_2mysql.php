<?php
// Configuración de ambas bases de datos


$sourceHost = 'localhost:3306';             // Host de origen
$sourceUser = 'webexptes';                  // Usuario de origen
$sourcePass = 'a1234567879A!$webexptes';    // Contraseña de origen
$sourceDB = 'webexptes';                   // Base de datos origen
$tableName = 'usuarios';                    // Nombre de la tabla

$targetHost = 'RDRLAPTOP:3306';            // Host de destino
$targetUser = 'webexptes';                  // Usuario de destino
$targetPass = 'a1234567879A!$webexptes';    // Contraseña de destino
$targetDB = 'webexptes';                    // Base de datos destino

// ID a copiar
$idToCopy = 2;

// 1. Conectar a ambas bases de datos
try {
    // Conexión a origen
    $sourceConn = new mysqli($sourceHost, $sourceUser, $sourcePass, $sourceDB);
    if ($sourceConn->connect_error) {
        throw new Exception("Conexión origen falló: " . $sourceConn->connect_error);
    }

    // Conexión a destino
    $targetConn = new mysqli($targetHost, $targetUser, $targetPass, $targetDB);
    if ($targetConn->connect_error) {
        throw new Exception("Conexión destino falló: " . $targetConn->connect_error);
    }

    // 2. Obtener datos del ID especificado
    $query = "SELECT * FROM $tableName WHERE id = ?";
    $stmt = $sourceConn->prepare($query);
    $stmt->bind_param('i', $idToCopy);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("No se encontró el ID $idToCopy en la tabla origen");
    }

    $row = $result->fetch_assoc();

    // 3. Preparar inserción en destino (opcional: quitar ID para auto-incremento)
    // unset($row['id']);

    $columns = implode(", ", array_keys($row));
    $placeholders = implode(", ", array_fill(0, count($row), '?'));
    $types = str_repeat('s', count($row));

    // 4. Insertar en destino
    $insertQuery = "INSERT INTO $tableName ($columns) VALUES ($placeholders)";
    $insertStmt = $targetConn->prepare($insertQuery);
    $insertStmt->bind_param($types, ...array_values($row));

    if ($insertStmt->execute()) {
        echo "✅ Datos copiados exitosamente!";
        echo "<br>Nuevo ID insertado: " . $targetConn->insert_id;
    } else {
        throw new Exception("Error al insertar: " . $insertStmt->error);
    }

} catch (Exception $e) {
    die("⚠️ Error: " . $e->getMessage());
} finally {
    // Cerrar conexiones
    if (isset($sourceConn)) $sourceConn->close();
    if (isset($targetConn)) $targetConn->close();
}
?>