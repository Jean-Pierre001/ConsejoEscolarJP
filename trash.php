<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Papelera de Carpetas</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" />
  <style>
    body {
      padding-top: 50px;
      background-color: #e8f0fe;
    }

    .content-wrapper {
      margin-left: 230px;
      padding: 30px;
    }

    .folder-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 25px;
    }

    .folder-card {
      background: #ffffff;
      width: 160px;
      height: 160px;
      border-radius: 15px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      text-align: center;
      padding: 20px 10px;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      text-decoration: none;
    }

    .folder-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 12px 24px rgba(0,0,0,0.15);
      background: linear-gradient(to bottom, #fff7e6, #ffe0b2);
    }

    .folder-icon {
      font-size: 55px;
      color: #f1c40f;
      margin-bottom: 10px;
    }

    .folder-name {
      font-size: 16px;
      font-weight: 600;
      color: #2c3e50;
      word-break: break-word;
    }
  </style>
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="content-wrapper">
  <h2>Papelera de Carpetas</h2>
  <p>Carpetas dentro del directorio <code>/trash</code>:</p>

  <div class="folder-grid">
    <?php
    require_once 'includes/conn.php';

    // Obtener todas las carpetas en tabla trash
    $stmt = $pdo->prepare("SELECT * FROM trash ORDER BY deleted_on DESC");
    $stmt->execute();
    $folders = $stmt->fetchAll();

    if (!$folders) {
      echo "<p>No hay carpetas en la papelera.</p>";
    } else {
      foreach ($folders as $folder) {
        $folder_name = htmlspecialchars($folder['name']);
        $encoded_name = urlencode($folder_name);
        $folder_path = 'trash/' . $folder['name'];

        if (!is_dir($folder_path)) {
          // Si la carpeta física no existe, mostrar mensaje o ignorar
          continue;
        }

        echo '
          <div style="position: relative; display: inline-block; margin: 10px;">
            <a href="folders_back/detailsfolders.php?folder=' . $encoded_name . '" class="folder-card" style="display: block;">
              <div class="folder-icon">
                <span class="glyphicon glyphicon-folder-open"></span>
              </div>
              <div class="folder-name">' . $folder_name . '</div>
            </a>
            <form method="POST" action="restore_folder.php" onsubmit="return confirm(\'¿Restaurar carpeta ' . $folder_name . '?\');" style="position: absolute; top: 5px; right: 10px;">
              <input type="hidden" name="folder_id" value="' . intval($folder['id']) . '">
              <button type="submit" style="background: none; border: none; color: green; font-size: 20px; cursor: pointer;" title="Restaurar carpeta">
                &#8634;
              </button>
            </form>
          </div>
        ';
      }
    }
    ?>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
<?php include 'includes/scripts.php' ?>

</body>
</html>
