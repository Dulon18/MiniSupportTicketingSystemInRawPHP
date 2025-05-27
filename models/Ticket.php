<?php
class Ticket {
    public static function all() {
        $pdo = Database::connect();
        return $pdo->query("SELECT * FROM tickets")->fetchAll();
    }

    public static function create($title, $desc, $userId, $deptId, $creAt, $status = 'open') {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO tickets (title, description, user_id, department_id, created_at, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $desc, $userId, $deptId, $creAt, $status]);
    }
    

    public static function assign($ticketId, $agentId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE tickets SET user_id = ? WHERE id = ?");
        $stmt->execute([$agentId, $ticketId]);

    }

    public static function updateStatus($ticketId, $status) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        $stmt->execute([$status, $ticketId]);
    }

    public static function addNote($userId, $note,$ticketId) {
        $pdo = Database::connect();
        $stmt = $pdo->prepare("INSERT INTO ticket_notes (ticket_id, user_id, note) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $note,$ticketId]);
    }
}
