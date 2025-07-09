<?php
include 'includes/session.php';
require_once 'includes/conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['folder_name'])) {
        $folder_name = $_POST['folder_name'];

        try {
            // 1. Buscar carpeta por nombre
            $stmt = $pdo->prepare("SELECT * FROM folders WHERE name = ?");
            $stmt->execute([$folder_name]);
            $folder = $stmt->fetch();

            if (!$folder) {
                $_SESSION['error'] = "Carpeta no encontrada en la base de datos.";
                header('Location: folders.php');
                exit;
            }

            // 2. Insertar en trash con fecha actual
            $insert = $pdo->prepare("INSERT INTO trash (original_id, name, cue, folder_path, location, deleted_on) VALUES (?, ?, ?, ?, ?, CURDATE())");
            $insert->execute([
                $folder['id'],
                $folder['name'],
                $folder['cue'],
                $folder['folder_path'],
                $folder['location']
            ]);

            // 3. Eliminar de tabla folders
            $delete = $pdo->prepare("DELETE FROM folders WHERE id = ?");
            $delete->execute([$folder['id']]);

            // 4. Mover carpeta física al directorio /trash
            $old_path = 'folders/' . $folder['name'];
            $new_path = 'trash/' . $folder['name'];

            if (is_dir($old_path)) {
                if (!is_dir('trash')) {
                    mkdir('trash', 0755, true);
                }

                // Función recursiva para mover carpeta completa
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

            $_SESSION['success'] = "Carpeta eliminada correctamente y movida a la papelera.";
            header('Location: folders.php');
            exit;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al eliminar la carpeta: " . $e->getMessage();
            header('Location: folders.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "Nombre de carpeta no especificado.";
        header('Location: folders.php');
        exit;
    }
} else {
    header('Location: folders.php');
    exit;
}
?>
