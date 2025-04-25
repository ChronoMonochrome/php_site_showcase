<?php
require 'db.php';
require 'User.php';
require 'File.php';
session_start();

if (!isset($_SESSION['user'])) {
    die("Unauthorized access."); // User must be authenticated
}

$currentUser = $_SESSION['user']; // Retrieve the authenticated user

$fileHandler = new File($currentUser->getFullDumpFilePath());
$uploadedFiles = $fileHandler->getUploadedFiles();

if (!isset($_GET['id']) || !is_numeric($_GET['id']) || $_GET['id'] < 0 || $_GET['id'] >= count($uploadedFiles)) {
    die("Invalid file ID.");
}

$file = $uploadedFiles[$_GET['id']];

// Validate the file path
$file_path = str_replace('/home/chrono/public_html', '/media/system/public_html/public_html/', $file['filename']);

if (!file_exists($file_path)) {
    die("File not found.");
}

// Set headers to download the file
header('Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
readfile($file_path);
