<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../helper.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/DepartmentController.php';
require_once __DIR__ . '/../controllers/TicketController.php';

$basePath = '/PHPSupportTicketingSystem';
$uri = str_replace($basePath, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();
$input = json_decode(file_get_contents('php://input'), true);
$user = AuthMiddleware::authenticate($headers);

function route($reqMethod, $pattern, $callback) {
    global $uri, $method, $input, $headers, $user;

    if ($reqMethod !== $method) return;

    $regex = '#^/api' . preg_replace('#\{(\w+)\}#', '(\d+)', $pattern) . '$#';
    if (preg_match($regex, $uri, $matches)) {
        array_shift($matches); // remove full match
        $params = [...$matches, $input, $user, $headers];
        echo json_encode(call_user_func_array($callback, $params));
        exit;
    }
}
// Routes
$auth = new AuthController();
route('POST', '/register', fn($input) => $auth->register($input));
route('POST', '/login', fn($input) => $auth->login($input));
route('POST', '/logout', fn($headers) => $auth->logout($headers));
route('GET', '/me', fn($headers) => $auth->me($headers));

// Department routes
$dept = new DepartmentController();
route('GET', '/departments', fn() => $dept->all());
route('POST', '/departments/create', fn($input, $user) => $dept->create($input, $user));
route('PUT', '/departments/{id}', fn($id, $input, $user) => $dept->update($id, $input, $user));
route('DELETE', '/departments/{id}', fn($id,$user) => $dept->delete($id, $user));

// Ticket routes
$ticket = new TicketController();
route('GET', '/tickets', fn() => $ticket->all());
route('POST', '/tickets/create', fn($input, $user) => $ticket->submit($input, $user));
route('PUT', '/tickets/{id}/assign', fn($id, $input, $user) => $ticket->assignToSelf($id, $user));
route('PUT', '/tickets/{id}/status', fn($id, $input, $user) => $ticket->updateStatus($id, $input, $user));
route('POST', '/tickets/notes', fn($input, $user) => $ticket->addNote($input, $user));

// Fallback
http_response_code(404);
echo json_encode(['error' => 'Route not found']);
