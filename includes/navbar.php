<?php
// Extraer datos del usuario desde la sesión
if (isset($_SESSION['user_data'])) {
  $admin = $_SESSION['user_data']; // o $user
}

include 'includes/profile_modal.php';
?>


<header class="main-header">
  <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    <div class="container-fluid">
      <div class="navbar-header">
        <a href="index.php" class="navbar-brand"><b>Gestor</b> Escolar</a>
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#mainNavbar">
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
      </div>

      <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="nav navbar-nav">
          <li><a href="index.php">Inicio</a></li>
          <li><a href="about.php">Nosotros</a></li>
          <li><a href="contact.php">Contacto</a></li>
        </ul>

        <ul class="nav navbar-nav navbar-right">
          <?php
if (isset($_SESSION['user_data']) && is_array($_SESSION['user_data'])) {
  $user = $_SESSION['user_data'];
  $image = (!empty($user['photo'])) ? 'images/'.$user['photo'] : 'images/profile.jpg';
  echo '
    <li class="dropdown">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <img src="'.$image.'" class="img-circle" style="width:25px; height:25px; margin-right:5px;"> 
        '.$user['first_name'].' '.$user['last_name'].' <b class="caret"></b>
      </a>
      <ul class="dropdown-menu">
        <li><a href="#" data-toggle="modal" data-target="#profile">Mi Perfil</a></li>
        <li><a href="logout.php">Cerrar sesión</a></li>
      </ul>
    </li>
  ';
} else {
  echo '
    <li><a href="login.php"><span class="glyphicon glyphicon-log-in"></span> Iniciar sesión</a></li>
  ';
}
?>

        </ul>
      </div>
    </div>
  </nav>
</header>
