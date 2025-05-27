<?php
class User {
    public static function create($name, $email, $password, $role) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_BCRYPT),
            $role
        ]);
        return true; // Return true if successfully created
    }
    public static function find($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function findByEmail($email) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
}
