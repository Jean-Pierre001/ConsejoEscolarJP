<?php
// Incluir la conexión
require_once 'includes/conn.php';

// Abrir conexión
$conn = $pdo->open();

try {
    // Datos de prueba
    $email = 'usuario' . rand(1000, 9999) . '@ejemplo.com';
    $password = password_hash('contrasena123', PASSWORD_DEFAULT);
    $type = 1;
    $first_name = 'Juan';
    $last_name = 'Pérez';
    $address = 'Calle Falsa 123';
    $contact_info = '555-1234567';
    $photo = 'foto.jpg';
    $status = 1;
    $activation_code = substr(md5(uniqid(rand(), true)), 0, 15);
    $reset_code = substr(md5(uniqid(rand(), true)), 0, 15);
    $created_on = date('Y-m-d');

    // Consulta SQL
    $sql = "INSERT INTO users (
                email, password, type, first_name, last_name, address, contact_info,
                photo, status, activation_code, reset_code, created_on
            ) VALUES (
                :email, :password, :type, :first_name, :last_name, :address, :contact_info,
                :photo, :status, :activation_code, :reset_code, :created_on
            )";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':email' => $email,
        ':password' => $password,
        ':type' => $type,
        ':first_name' => $first_name,
        ':last_name' => $last_name,
        ':address' => $address,
        ':contact_info' => $contact_info,
        ':photo' => $photo,
        ':status' => $status,
        ':activation_code' => $activation_code,
        ':reset_code' => $reset_code,
        ':created_on' => $created_on,
    ]);

    echo "Registro insertado correctamente.";

} catch (PDOException $e) {
    echo "Error al insertar: " . $e->getMessage();
}

// Cerrar conexión
$pdo->close();
?>
