<?php
include 'includes/session.php';
require_once 'includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['folder_id'])) {
        $folder_id = intval($_POST['folder_id']);

        try {
            // 1. Obtener datos de la carpeta en trash
            $stmt = $pdo->prepare("SELECT * FROM trash WHERE id = ?");
            $stmt->execute([$folder_id]);
            $folder = $stmt->fetch();

            if (!$folder) {
                $_SESSION['error'] = "Carpeta no encontrada en la papelera.";
                header('Location: trash.php');
                exit;
            }

            // 2. Insertar carpeta en folders
            $insert = $pdo->prepare("INSERT INTO folders (name, cue, folder_path, location, created_on) VALUES (?, ?, ?, ?, CURDATE())");
            $insert->execute([
                $folder['name'],
                $folder['cue'],
                $folder['folder_path'],
                $folder['location']
            ]);
            $new_folder_id = $pdo->lastInsertId();

            // 3. Borrar carpeta de trash
            $delete = $pdo->prepare("DELETE FROM trash WHERE id = ?");
            $delete->execute([$folder_id]);

            // 4. Mover carpeta física de trash/ a folders/
            $old_path = 'trash/' . $folder['name'];
            $new_path = 'folders/' . $folder['name'];

            if (is_dir($old_path)) {
                if (!is_dir('folders')) {
                    mkdir('folders', 0755, true);
                }

                // Función para mover carpeta completa recursivamente
                function moveFolder($src, $dst) {
                    mkdir($dst, 0755, true);
                    foreach (scandir($src) as $file) {
                        if ($file === '.' || $file === '..') continue;

                        $srcFile = $src . DIRECTORY_SEPARATOR . $file;
                        $dstFile = $dst . DIRECTORY_SEPARATOR . $file;

                        if (is_dir($srcFile)) {
                            moveFolder($srcFile, $dstFile);
                        } else {
                            rename($srcFile, $dstFile);
                        }
                    }
                    rmdir($src);
                }

                moveFolder($old_path, $new_path);
            }

            $_SESSION['success'] = "Carpeta restaurada correctamente.";
            header('Location: trash.php');
            exit;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al restaurar la carpeta: " . $e->getMessage();
            header('Location: trash.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "ID de carpeta no especificado.";
        header('Location: trash.php');
        exit;
    }
} else {
    header('Location: trash.php');
    exit;
}
?>
