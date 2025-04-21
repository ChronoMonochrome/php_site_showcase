<?php
session_start();
require 'db.php';
require 'User.php';
require 'File.php';

$connection = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'], $_POST['password'])) {
    $user = new User($connection);
    if ($user->authenticate($_POST['login'], $_POST['password'])) {
        $_SESSION['user'] = $user;
    } else {
        $error = "Invalid credentials.";
    }
}

if (isset($_GET['logout'])) {
    $user = new User($connection);
    $user->logout();
    header("Location: index.php");
    exit;
}

$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
$uploadedFiles = [];
$groupedFiles = [];

if ($currentUser) {
    // Here we pass the full path to the File class
    $fileHandler = new File($currentUser->getFullDumpFilePath());
    $uploadedFiles = $fileHandler->getUploadedFiles();
    $groupedFiles = $fileHandler->groupFilesByYear($uploadedFiles);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Files</title>
</head>
<body>
    <?php if (!$currentUser): ?>
        <form method="post" action="">
            <input type="text" name="login" placeholder="Login" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <?php if (isset($error)): ?>
                <p style="color:red;"><?php echo $error; ?></p>
            <?php endif; ?>
        </form>
    <?php else: ?>
        <h1>Welcome, <?php echo htmlspecialchars($currentUser->name); ?>!</h1>
        <a href="?logout=1">Logout</a>
        <h2>Your Files</h2>

        <?php if (!empty($groupedFiles)): ?>
            <?php foreach ($groupedFiles as $year => $files): ?>
                <h3><?php echo $year; ?></h3>
                <ul>
                    <?php foreach ($files as $file): ?>
                        <li>
                            <a href="<?php echo htmlspecialchars($file['full_path']); ?>" download><?php echo htmlspecialchars($file['file_title']); ?></a>
                            (Uploaded on: <?php echo htmlspecialchars($file['date']); ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No files found.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>

