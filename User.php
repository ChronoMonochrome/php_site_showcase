<?php
class User {
    public $login;
    public $hash;
    public $name;
    public $type;
    public $capabilities;
    public $dump_folder;

    public function authenticate($connection, $login, $password) {
        $stmt = $connection->prepare("SELECT * FROM users WHERE login = :login LIMIT 1");
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $this->hashPassword($password) === $user['hash']) {
            $this->login = $user['login'];
            $this->hash = $user['hash'];
            $this->name = $user['name'];
            $this->type = $user['type'];
            $this->capabilities = $user['capabilities'];
            $this->dump_folder = $user['dump_folder'];

            $_SESSION['user'] = $this; // Store the current User object in the session

            return true;
        }
        return false;
    }

    private function hashPassword($password) {
        // Use sha512 hashing algorithm
        return hash('sha512', $password);
    }

    public function getFullDumpFilePath() {
        $root = $_SERVER['DOCUMENT_ROOT'];
        // Construct full path to serialized data file
        return $root . "/files_db/" . $this->dump_folder . ".txt";
    }

    public function logout() {
        session_start();
        session_destroy();
    }
}
