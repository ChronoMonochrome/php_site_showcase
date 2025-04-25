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
    <title><?php echo $title; ?></title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <link href="/theme/favicon.ico" rel="shortcut icon" type="image/x-icon" />
    <link href="/static/css/bootstrap-3.3.7.min.css" rel="stylesheet">
    <link href="/static/css/bootstrap-slider-10.6.1.min.css" rel="stylesheet">
    <link href="/static/css/bootstrap-datepicker-1.8.0.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/static/css/site.css" type="text/css" />
    <script type="text/javascript" src="/static/js/jquery-1.x-git.js"></script>
    <script type="text/javascript" src="/static/js/jquery-ui-1.12.1.min.js"></script>
    <script type="text/javascript" src="/static/js/bootstrap-3.3.7.min.js"></script>
</style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <ul class="menu">
                    <li><a href="/">На главную</a></li>
                    <li><a href="/signin">Войти</a></li>
                </ul>
            </nav>
        </div>
    </header>

   <div id="wrapper" style="max-width:1180px;">


     <div id="header_container">
       <div id="header">
          <div id="logo">
              <a class="imglogo" href="/"><img alt="Logo" src="/static/img/institut_2015_1x2_print2_2.jpg"/></a>
              <a style="color:#0066B3;" class="logo" href="/"> Государственное бюджетное  учреждение дополнительного профессионального образования  «Ставропольский краевой институт развития образования, повышения квалификации и переподготовки работников образования»</a>
          </div>
          <span class="heckl">&nbsp;</span>
          <span class="heckr">&nbsp;</span>
        </div>
       </div> <!-- header -->
     </div> <!-- header_container -->



   </div> <!-- wrapper -->

        <div id="container">
            <?php if (!$currentUser): ?>
                <div class="login-form">
                    <br>
                    <p>Пожалуйста, войдите в систему, введя логин и пароль в форму ниже:</p>
                    <form method="post" action="">
                        <label>Логин: <input type="text" name="login" value="" /></label><br />
                        <label>Пароль: <input type="password" name="password" value="" /></label><br />
                        <input type="submit" value="Войти" />
                    </form>
                </div>
            <?php else: ?>
                <span>Здравствуйте, Вы зашли как <?php echo htmlspecialchars($currentUser->name); ?>!</span>
                <a href="?logout=1">Выйти</a>
                <br>
				<?php if (!empty($groupedFiles)): ?>
					<?php krsort($groupedFiles); // Sort years in reverse order ?>
					<?php $first = true; ?>
					<?php foreach ($groupedFiles as $year => $files): ?>
						<a href="#" id="a<?php echo htmlspecialchars($year); ?>" class="spoiler-trigger <?php if ($first) echo 'active'; ?>">
							<span><?php echo htmlspecialchars($year); ?> год</span>
						</a>
						<div id="spoiler_id<?php echo htmlspecialchars($year); ?>" class="spoiler-block <?php if ($first) echo 'active'; ?>" style="<?php if ($first) echo 'display: block;'; ?>">
							<ul>
								<?php foreach ($files as $index => $file): ?>
									<li>
										<a href="download.php?id=<?php echo htmlspecialchars($index); ?>" download><?php echo htmlspecialchars($file['file_title']); ?></a>
										(<?php echo htmlspecialchars($file['date']); ?>)
									</li>
								<?php endforeach; ?>
							</ul>
						</div>
						<br>
						<?php $first = false; ?>
					<?php endforeach; ?>
				<?php else: ?>
					<p>No files found.</p>
				<?php endif; ?>
			<?php endif; ?>
		</div> <!-- container -->
    </div> <!-- wrapper -->
<script>
  document.querySelectorAll('.spoiler-trigger').forEach(function(trigger) {
    trigger.addEventListener('click', function(event) {
      event.preventDefault();
      const isActive = this.classList.contains('active');
      const spoilerId = '#spoiler_id' + this.id.substring(1);
      const spoilerBlock = document.querySelector(spoilerId);

      if (spoilerBlock) {
        this.classList.toggle('active');
        spoilerBlock.classList.toggle('active');
        if (spoilerBlock.classList.contains('active')) {
          spoilerBlock.style.display = 'block';
        } else {
          spoilerBlock.style.display = 'none';
        }
      }
    });
  });
</script>
</body>
</html>
