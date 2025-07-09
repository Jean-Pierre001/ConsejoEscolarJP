<?php
include '../includes/session.php';
require_once '../includes/conn.php';

$cue = $_GET['CUE'] ?? null;
$name = $_GET['nombreEscuela'] ?? '';
$location = $_GET['localidad'] ?? '';

if (!$cue) {
    $_SESSION['error'] = "No se recibió el CUE de la escuela.";
    header("Location: ../schools.php");
    exit;
}

// Función para sanear nombre de carpeta
function sanitizeFolderName($name) {
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
}

// Verificar si ya existe carpeta para ese CUE
$sqlCheck = "SELECT * FROM folders WHERE cue = :cue";
$stmtCheck = $pdo->prepare($sqlCheck);
$stmtCheck->execute([':cue' => $cue]);
$folderExists = $stmtCheck->fetch();

if ($folderExists) {
    $_SESSION['error'] = "Ya existe una carpeta para la escuela con CUE $cue.";
    header("Location: ../schools.php");
    exit;
}

// Crear carpeta física
$basePath = __DIR__ . '/../folders/escuelas/';
$folderName = sanitizeFolderName($cue);
$fullPath = $basePath . $folderName;

if (!is_dir($fullPath)) {
    if (!mkdir($fullPath, 0755, true)) {
        $_SESSION['error'] = "Error al crear la carpeta física.";
        header("Location: ../schools.php");
        exit;
    }
} else {
    $_SESSION['error'] = "La carpeta física ya existe.";
    header("Location: ../schools.php");
    exit;
}

// Insertar registro en BD
$sqlInsert = "INSERT INTO folders (name, cue, folder_path, location, created_on) VALUES (:name, :cue, :folder_path, :location, CURDATE())";
$stmtInsert = $pdo->prepare($sqlInsert);
$stmtInsert->execute([
    ':name' => $name,
    ':cue' => $cue,
    ':folder_path' => $fullPath,
    ':location' => $location
]);

$_SESSION['success'] = "Carpeta creada exitosamente para la escuela con CUE $cue.";
header("Location: ../schools.php");
exit;
