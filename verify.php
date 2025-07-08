<?php
include 'includes/session.php'; // Asegura que session_start() ya se llamó
include_once 'includes/conn.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $conn = $pdo->open();

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            // Verificar contraseña (ajusta según cómo la guardás)
            if (password_verify($password, $user['password'])) {
                // Guardar todos los datos en sesión
                $_SESSION['user'] = $user;            // Para tipo de usuario y lógica general
                $_SESSION['user_data'] = $user;       // Para mostrar en navbar

                // Redirigir según tipo de usuario
                switch ($user['type']) {
                    case 1:
                        header('Location: index.php');
                        break;
                    default:
                        header('Location: index.php');
                        break;
                }
                exit();
            } else {
                $_SESSION['error'] = 'Contraseña incorrecta.';
                header('Location: login.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Usuario no encontrado.';
            header('Location: login.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error de base de datos: " . $e->getMessage();
        header('Location: login.php');
        exit();
    }

    $pdo->close();
} else {
    // Acceso directo sin envío del formulario
    header('Location: login.php');
    exit();
}
