<?php
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/RateLimiter.php';

class TicketController {

    public function all() {
        return Ticket::all();
    }
    public function submit($data, $user) {
        
        if (!isset($user) || !is_array($user) || !isset($user['id'])) {
            http_response_code(401);
            return ['status' => 'failed', 'error' => 'Unauthorized'];
        }
        // Rate limit check
        if (!RateLimiter::allow($user['id'])) {
            http_response_code(429); // Too Many Requests
            return [
                'status' => 'failed',
                'error' => 'Rate limit exceeded. Try again later.'
            ];
        }
        if (!userExists($user['id'])) {
            http_response_code(404);
            return ['status' => 'failed', 'error' => 'User not found'];
        }        
        // Validate required fields
        if (empty($data['title']) || empty($data['description']) || empty($data['department_id'])) {
            http_response_code(400);
            return ['status' => 'failed', 'error' => 'Title, description, and department_id are required'];
        }

        // Check if user exists
        if (!userExists($user['id'])) {
            http_response_code(404);
            return ['status' => 'failed', 'error' => 'User not found'];
        }
        

        // Check if department exists
        if (!departmentExists($data['department_id'])) {
            http_response_code(404);
            return ['status' => 'failed', 'error' => 'Department not found'];
        }
        
        //create ticket
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $status = $data['status'] ?? 'open';

        Ticket::create(
            $data['title'], 
            $data['description'], 
            $user['id'], 
            $data['department_id'],
            $status,
            $data['created_at']
        );
        return [
            'status' => 'success',
            'message' => 'Ticket submitted',
            'data'=>[
                'title'=>$data['title'],
                'description'=>$data['description'],
                'status'=>$status
                ]
        ];
    }

    public function assignToSelf($id, $user) {
        
        if (!isset($user) || !is_array($user) || !isset($user['id'])) {
            http_response_code(401);
            return ['status' => 'failed', 'error' => 'Unauthorized'];
        }
        if (!userExists($user['id'])) {
            http_response_code(404);
            return ['status' => 'failed', 'error' => 'User not found'];
        }
        if ($user['role'] !== 'agent') {
            http_response_code(403);
            return ['status' => 'failed','error' => 'Only agents can assign themself in tickets'];
        }

        Ticket::assign($id, $user['id']);
        return ['status' => 'success','message' => 'Ticket assigned to agent'];
    }

    public function updateStatus($id, $data, $user) 
    {
        if (!isset($user) || !is_array($user) || !isset($user['id'])) {
            http_response_code(401);
            return ['status' => 'failed', 'error' => 'Unauthorized'];
        }
        if (!userExists($user['id'])) {
            http_response_code(404);
            return ['status' => 'failed', 'error' => 'User not found'];
        }
        // Check if ticket exists
        if (!ticketExists($id)) {
            http_response_code(404);
            return ['status' => 'failed', 'error' => 'Ticket not found'];
        }
        Ticket::updateStatus($id, $data['status']);
        return [
            'status' => 'success','message' => 'Status updated',
            'data' =>['status'=>$data['status']]
        ];
    }

    public function addNote($data, $user) {
        if (!isset($user) || !is_array($user) || !isset($user['id'])) {
            http_response_code(401);
            return ['status' => 'failed', 'error' => 'Unauthorized'];
        }
        if (!userExists($user['id'])) {
            http_response_code(404);
            return ['status' => 'failed', 'error' => 'User not found'];
        }
        // Check if ticket exists
        if (!ticketExists($data['ticket_id'])) {
            http_response_code(404);
            return ['status' => 'failed', 'error' => 'Ticket ID not found'];
        }
        //validation
        if (!isset($data['note'])) {
            http_response_code(400);
            return ['status' => 'failed', 'error' => 'Note is required'];
        }
        Ticket::addNote( 
            $user['id'], 
            $data['ticket_id'], 
            $data['note']
        );
        return ['status' => 'success','message' => 'Note added'];
    }
}
