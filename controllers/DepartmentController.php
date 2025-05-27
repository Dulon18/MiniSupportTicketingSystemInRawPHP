<?php
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../config/database.php';

class DepartmentController {
    public function all() {
        return Department::all();
    }
    public function create($data, $user) {

        if (!$user) {
            http_response_code(401);
            return ['status' => 'failed', 'error' => 'Unauthorized'];
        }
    
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            return ['status' => 'failed', 'error' => 'Only admins can create departments'];
        }
        //validation
        if (!isset($data['name']) || empty($data['name'])) {
            http_response_code(422);
            return ['status' => 'failed', 'error' => 'Department name is required'];
        }
        $name = trim($data['name']);

        if (Department::exists($name)) {
            http_response_code(409);
            return ['status' => 'failed', 'error' => 'Department already exists'];
        }
        Department::create($data['name']);
    
        return [
            'status' => 'success',
            'message' => 'Department is Created successfully',
            'data' => ['name' => $data['name']]
        ];
    }
    public function update($id, $data, $user) {
        if (!$user) {
            http_response_code(401);
            echo json_encode(['status'=>'failed','error' => 'Unauthorized']);
            exit;
        }        
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            return ['status'=>'failed','error' => 'Only admins can update departments'];
        }

        Department::update($id, $data['name']);
        return ['status'=>'success','message' => 'Department updated'];
    }

    public function delete($id, $user) {
        if (!$user) {
            http_response_code(401);
            echo json_encode(['status'=>'failed','error' => 'Unauthorized']);
            exit;
        }        
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            return ['status'=>'failed','error' => 'Only admins can delete departments'];
        }

        Department::delete($id);
        return ['status'=>'success','message' => 'Department deleted'];
    }
}