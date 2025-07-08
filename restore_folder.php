<?php
include 'includes/session.php';
include 'includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folder_id'])) {
    $folder_id = intval($_POST['folder_id']);

    $trash_path = 'trash/';
    $folders_path = 'folders/';

    try {
        $database = new Database();
        $pdo = $database->open();

        // Obtener datos carpeta en trash
        $stmt = $pdo->prepare("SELECT * FROM trash WHERE id = ?");
        $stmt->execute([$folder_id]);
        $folder = $stmt->fetch();

        if (!$folder) {
            $_SESSION['error'] = "Carpeta no encontrada en la papelera.";
            header('Location: trash.php');
            exit;
        }

        $folder_name = $folder['name'];
        $old_path = $trash_path . $folder_name;
        $new_path = $folders_path . $folder_name;

        if (!is_dir($old_path)) {
            $_SESSION['error'] = "La carpeta física no existe en la papelera.";
            header('Location: trash.php');
            exit;
        }

        // Insertar en folders
        $insert = $pdo->prepare("INSERT INTO folders (name, cue, folder_path, location, created_on) VALUES (?, ?, ?, ?, CURDATE())");
        $insert->execute([
            $folder['name'],
            $folder['cue'],
            'folders/' . $folder['name'],
            $folder['location']
        ]);

        // Borrar de trash
        $delete = $pdo->prepare("DELETE FROM trash WHERE id = ?");
        $delete->execute([$folder_id]);

        // Mover carpeta física de trash a folders
        if (!is_dir($folders_path)) {
            mkdir($folders_path, 0755, true);
        }
        rename($old_path, $new_path);

        $_SESSION['success'] = "Carpeta '$folder_name' restaurada correctamente.";
        $database->close();
        header('Location: trash.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al restaurar carpeta: " . $e->getMessage();
        header('Location: trash.php');
        exit;
    }
} else {
    header('Location: trash.php');
    exit;
}
