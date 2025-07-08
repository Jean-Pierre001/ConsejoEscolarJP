<?php
include 'includes/session.php';
include 'includes/conn.php'; // conexión a $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['folder_id'])) {
        $folder_id = intval($_POST['folder_id']);
        
        try {
            // 1. Obtener datos de la carpeta a eliminar
            $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ?");
            $stmt->execute([$folder_id]);
            $folder = $stmt->fetch();

            if (!$folder) {
                $_SESSION['error'] = "Carpeta no encontrada.";
                header('Location: folders.php');
                exit;
            }

            // 2. Insertar en trash
            $insert = $pdo->prepare("INSERT INTO trash (original_id, name, cue, folder_path, location, deleted_on) VALUES (?, ?, ?, ?, ?, CURDATE())");
            $insert->execute([
                $folder['id'],
                $folder['name'],
                $folder['cue'],
                $folder['folder_path'],
                $folder['location']
            ]);

            // 3. Borrar carpeta de folders
            $delete = $pdo->prepare("DELETE FROM folders WHERE id = ?");
            $delete->execute([$folder_id]);

            // 4. Mover la carpeta física en el servidor (opcional)
            $old_path = 'folders/' . $folder['name'];
            $new_path = 'trash/' . $folder['name'];
            if (is_dir($old_path)) {
                if (!is_dir('trash')) {
                    mkdir('trash', 0755, true);
                }
                rename($old_path, $new_path);
            }

            $_SESSION['success'] = "Carpeta eliminada y movida a la papelera correctamente.";
            header('Location: folders.php');
            exit;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al eliminar carpeta: " . $e->getMessage();
            header('Location: folders.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "ID de carpeta no especificado.";
        header('Location: folders.php');
        exit;
    }
} else {
    header('Location: folders.php');
    exit;
}
?>
