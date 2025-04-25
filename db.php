<?php
$config = get_config();
function get_config() {
   $root = $_SERVER['DOCUMENT_ROOT'];

   return parse_ini_file($root . "/../config.ini");
}
function getDBConnection() {
    $config = get_config();
    $host = 'localhost'; // your database host
    $dbname = 'survey_db'; // your database name
    $username = 'root'; // your database username
    $password = $config["root_passwd"];

    try {
        return new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
}
