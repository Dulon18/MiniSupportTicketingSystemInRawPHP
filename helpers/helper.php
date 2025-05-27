<?php 
function userExists($userId) {
    $pdo = Database::connect();
    $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function departmentExists($departmentId) {
    $pdo = Database::connect();
    $stmt = $pdo->prepare("SELECT id FROM departments WHERE id = ?");
    $stmt->execute([$departmentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function ticketExists($departmentId) {
    $pdo = Database::connect();
    $stmt = $pdo->prepare("SELECT id FROM tickets WHERE id = ?");
    $stmt->execute([$departmentId]);
    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

