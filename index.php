<?php
require 'db.php';
require 'User.php';
require 'File.php';

session_start();
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$connection = getDBConnection();
$currentUser = null; // Initialize currentUser

// Check if a user is already logged in
if (isset($_SESSION['user'])) {
    $currentUser = $_SESSION['user'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'], $_POST['password'])) {
    $user = new User();
    if ($user->authenticate($connection, $_POST['login'], $_POST['password'])) {
        $_SESSION['user'] = $user;
        $currentUser = $user; // Update currentUser after successful login
    } else {
        $error = "Invalid credentials.";
    }
}

if (isset($_GET['logout'])) {
    $user = new User($connection); // You might not need to instantiate a new User object here
    session_destroy(); // It's cleaner to just destroy the session
    header("Location: index.php");
    exit;
}

$uploadedFiles = [];
$groupedFiles = [];

if ($currentUser) {
    $fileHandler = new File($currentUser->getFullDumpFilePath());
    $uploadedFiles = $fileHandler->getUploadedFiles();
    $groupedFiles = $fileHandler->groupFilesByYear($uploadedFiles);
}

$title = "Главная";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Files</title>
<link rel="stylesheet" href="/site-templates/site.css" type="text/css" />
       <link href="/site-templates/allrounder-3/css/template.css" rel="stylesheet" type="text/css" media="all" />
<!--
       <link href="/site-templates/allrounder-3/css/bootstrap.css" rel="stylesheet" type="text/css" media="all" />
-->
       <link href="/site-templates/allrounder-3/css/joomla.css" rel="stylesheet" type="text/css" media="all" />
       <!--<link href="/site-templates/allrounder-3/css/colors.css" rel="stylesheet" type="text/css" media="all" />-->
       <link href="/site-templates/allrounder-3/css/lvdropdown.css" rel="stylesheet" type="text/css" media="all" />
       <link href="/site-templates/allrounder-3/css/typo.css" rel="stylesheet" type="text/css" media="all" />
       <link href="/site-templates/allrounder-3/css/modules.css" rel="stylesheet" type="text/css" media="all" />
<style>
.spoiler-trigger{
	color: #0b70db;
	text-decoration: none;
	padding-left: 15px;
	background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAANUlEQVQoU2PkLrj9n4EAYAQp+jpBlRGXOpA8hiJ0TaQrwuY2kDNINwnmcKLchO5LuHWEwgkAlO5FBwhFaI8AAAAASUVORK5CYII=) no-repeat 0 50%;
}
.spoiler-trigger.active{
	background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAkAAAAJCAYAAADgkQYQAAAAKklEQVQoU2PkLrj9n4EAYAQp+jpBlRGXOpA8DRRhcxvIGTSyjqDvCIUTAEcINQcERZkIAAAAAElFTkSuQmCC);
}
.spoiler-trigger>span{
	border-bottom: 1px dashed #0b70db;
	padding:0 3px;
}
.spoiler-trigger:hover>span{
	border-bottom-style: solid;
}
.spoiler-block{
	display: none;
}
</style>
</head>
<body BGCOLOR="#ffffff" text="#000000">
   <div id="wrapper" style="max-width:1180px;">


     <div id="header_container">

       <div id="header">


          <div id="logo">

              <a class="imglogo" href="/index.php"><img alt="Logo" src="/site-templates/img/institut_2015_1x2_print2_2.jpg"/></a>
		<a style="color:#0066B3;" class="logo" href="/index.php"> Государственное бюджетное  учреждение дополнительного профессионального образования  «Ставропольский краевой институт развития образования, повышения квалификации и переподготовки работников образования»</a>
                                  </div>

          <span class="heckl">&nbsp;</span>
          <span class="heckr">&nbsp;</span>
        </div>
       </div> <!-- header -->
     </div> <!-- header_container -->
   </div> <!-- wrapper -->
<div id="container">
    <?php if (!$currentUser): ?>
		<br>
		<br>
		Пожалуйста, войдите в систему, введя логин и пароль в форму ниже:<br>
		﻿<form method="post" action="">
			Логин: <input type="text" name="login"
			value="" />
			<br/>
			Пароль: <input type="password" name="password" value="" /><br/>
			<input type="submit" value="Войти" />
		</form>
    <?php else: ?>
        <span>Здравствуйте, Вы зашли как <?php echo htmlspecialchars($currentUser->name); ?>!</span>
        <a href="?logout=1">Выйти</a>
		<br>
		<?php if (!empty($groupedFiles)): ?>
			<?php krsort($groupedFiles); // Sort years in reverse order ?>
			<?php foreach ($groupedFiles as $year => $files): ?>
				<a href="#" id="a<?php echo htmlspecialchars($year); ?>" class="spoiler-trigger active">
					<span><?php echo htmlspecialchars($year); ?> год</span>
				</a>
				<div id="spoiler_id<?php echo htmlspecialchars($year); ?>" class="spoiler-block" style="display: block;">
					<ul>
					<?php foreach ($files as $index => $file): // Use index for file ID ?>
						<li>
							<a href="download.php?id=<?php echo htmlspecialchars($index); ?>" download><?php echo htmlspecialchars($file['file_title']); ?></a>
							(<?php echo htmlspecialchars($file['date']); ?>)
						</li>
					<?php endforeach; ?>
					</ul>
				</div>
				<br>
			<?php endforeach; ?>
		<?php else: ?>
			<p>No files found.</p>
		<?php endif; ?>
    <?php endif; ?>
</div> <!-- container -->
</body>
</html>
