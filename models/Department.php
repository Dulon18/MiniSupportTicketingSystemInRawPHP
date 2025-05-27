<?php
class Department {
    public static function all() {
        $pdo = Database::connect();
        return $pdo->query("SELECT * FROM departments")->fetchAll();
    }

    public static function create($name) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO departments (name) VALUES (?)");
        $stmt->execute([$name]);
    }

    public static function update($id, $name) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE departments SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
    }
    public static function exists($name) {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT id FROM departments WHERE name = :name");
        $stmt->execute(['name' => $name]);
        return $stmt->fetch() !== false;
    }
    
    public static function delete($id) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->execute([$id]);
    }
}
