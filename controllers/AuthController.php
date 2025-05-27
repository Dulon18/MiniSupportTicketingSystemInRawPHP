<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class AuthController {
    public function register($data) {
        $userCreated = User::create($data['name'], $data['email'], $data['password'], $data['role']);
        
        if ($userCreated) {
            return [
                'status' => "success",
                'message' => 'User registered successfully'
            ];
        }
        return [
            'status' => "failed",
            'error' => 'Registration failed'
        ];
    }

    public function login($data) {
        $user = User::findByEmail($data['email']);
    
        if ($user && password_verify($data['password'], $user['password'])) {
            if (!is_dir('storage')) {
                mkdir('storage', 0755, true);
            }
    
            if (!file_exists('storage/tokens.json')) {
                file_put_contents('storage/tokens.json', json_encode([]));
            }
    
            $token = bin2hex(random_bytes(32));
            $tokens = json_decode(file_get_contents('storage/tokens.json'), true);
            $tokens[$token] = [
                'user_id' => $user['id'],
                'role' => $user['role'],
                'name' => $user['name'],
                'email' => $user['email'],
                'created_at' => time()
            ];
            file_put_contents('storage/tokens.json', json_encode($tokens));
            
            return [
                'status' => "success",
                'message' => 'User Logged in successfully',
                'token' => $token,
                'data' => [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ]
            ];
        }
    
        return [
            'status' => "failed",
            'error' => 'Invalid credentials'
        ];
    }
    

    public function logout($headers) {
        if (!isset($headers['Authorization'])) {
            return ['status' => 'failed', 'error' => 'Authorization header missing'];
        }
    
        $token = trim(str_replace('Bearer', '', $headers['Authorization']));
        $tokens = json_decode(file_get_contents('storage/tokens.json'), true);
    
        if (isset($tokens[$token])) {
            unset($tokens[$token]);
            file_put_contents('storage/tokens.json', json_encode($tokens));
        }
    
        return ['status' => 'success', 'message' => 'Logged out successfully'];
    }
    

    public function me($headers) {
        $userId = AuthMiddleware::authenticate($headers);
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                return [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
            }
        }

        return ['status'=>'error','message' => 'Unauthorized'];
    }

    public function getUserFromToken($headers) {
        if (!isset($headers['Authorization'])) return null;

        $token = trim(str_replace('Bearer', '', $headers['Authorization']));
        $tokens = json_decode(file_get_contents('storage/tokens.json'), true);
        $userId = $tokens[$token]['user_id'] ?? null;

        if ($userId) {
            return User::find($userId);
        }

        return null;
    }
}
