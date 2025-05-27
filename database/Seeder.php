<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../models/Ticket.php';

$pdo = Database::connect();

// Clear tables (optional)
$pdo->exec("DELETE FROM tickets");
$pdo->exec("DELETE FROM departments");
$pdo->exec("DELETE FROM users");

// Seed users
$password = password_hash('password', PASSWORD_DEFAULT);

$users = [
    ['Rakib Hasan', 'rakib5050@example.com', $password, 'admin'],
    ['Samia Khan', 'samia2025@example.com', $password, 'agent'],
    ['Bilal Hasan', 'bilal@example.com', $password, 'user'],
];

foreach ($users as $u) {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute($u);
}

// Seed departments
$departments = ['IT Support', 'Customer Service', 'Billing'];

foreach ($departments as $d) {
    $stmt = $pdo->prepare("INSERT INTO departments (name) VALUES (?)");
    $stmt->execute([$d]);
}

// Seed tickets
$stmt = $pdo->query("SELECT id FROM users WHERE role = 'user' LIMIT 1");
$userId = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT id FROM departments LIMIT 1");
$deptId = $stmt->fetchColumn();

$tickets = [
    ['Cannot login', 'I am unable to login to my account.', $userId, $deptId, time(), 'open'],
    ['Billing issue', 'I was overcharged last month.', $userId, $deptId, time(), 'open'],
];

foreach ($tickets as $t) {
    $stmt = $pdo->prepare("INSERT INTO tickets (title, description, user_id, department_id, created_at, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute($t);
}

// Seed ticket_notes
$stmt = $pdo->query("SELECT id FROM tickets");
$ticketIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->query("SELECT id FROM users WHERE role = 'agent' LIMIT 1");
$agentId = $stmt->fetchColumn();

foreach ($ticketIds as $ticketId) {
    $notes = [
        'Looking into the issue.',
        'Requested more details from the user.',
        'Escalated to the technical team.',
    ];

    foreach ($notes as $note) {
        $stmt = $pdo->prepare("INSERT INTO ticket_notes (ticket_id, user_id, note, created_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ticketId, $agentId, $note, time()]);
    }
}

echo "Database seeded successfully.\n";
