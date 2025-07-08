<?php
session_start();
include 'includes/conn.php'; 
$conn = $pdo->open();

if (isset($_POST['save'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $curr_password = $_POST['curr_password'];
    $id = $_SESSION['user_data']['id'];

    // Traer datos actuales
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $admin = $stmt->fetch();

    if (!$admin) {
        die('Usuario no encontrado.');
    }

    if (!password_verify($curr_password, $admin['password'])) {
        $_SESSION['error'] = "Contraseña actual incorrecta.";
        header('Location: '.$_GET['return']);
        exit();
    }

    if (!empty($password)) {
        $new_password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $new_password = $admin['password'];
    }

    // Procesar foto
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photo_tmp = $_FILES['photo']['tmp_name'];
        $photo_name = $_FILES['photo']['name'];
        $ext = pathinfo($photo_name, PATHINFO_EXTENSION);
        $new_photo_name = uniqid() . '.' . $ext;
        $upload_path = 'images/' . $new_photo_name;

        if (move_uploaded_file($photo_tmp, $upload_path)) {
            $photo_to_save = $new_photo_name;
            if (!empty($admin['photo']) && file_exists('images/' . $admin['photo'])) {
                unlink('images/' . $admin['photo']);
            }
        } else {
            $_SESSION['error'] = "Error al subir la foto.";
            header('Location: '.$_GET['return']);
            exit();
        }
    } else {
        $photo_to_save = $admin['photo'];
    }

    $update_sql = "UPDATE users SET email = :email, password = :password, first_name = :firstname, last_name = :lastname, photo = :photo WHERE id = :id";
    $stmt = $conn->prepare($update_sql);
    if ($stmt->execute([
        'email' => $email,
        'password' => $new_password,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'photo' => $photo_to_save,
        'id' => $id
    ])) {
        $_SESSION['user_data']['email'] = $email;
        $_SESSION['user_data']['first_name'] = $firstname;
        $_SESSION['user_data']['last_name'] = $lastname;
        $_SESSION['user_data']['photo'] = $photo_to_save;

        $_SESSION['success'] = "Perfil actualizado correctamente.";
    } else {
        $_SESSION['error'] = "Error al actualizar perfil.";
    }

    header('Location: '.$_GET['return']);
    exit();
}
?>
