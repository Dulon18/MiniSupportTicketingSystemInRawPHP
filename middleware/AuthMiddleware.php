<?php
require_once __DIR__ . '/../config/database.php';

class AuthMiddleware {
    public static function authenticate($headers) {
        if (!isset($headers['Authorization'])) return null;

        $token = trim(str_replace('Bearer', '', $headers['Authorization']));
        $tokens = json_decode(file_get_contents('storage/tokens.json'), true);
    
        if (!isset($tokens[$token])) return null;
    
        $tokenData = $tokens[$token];
        $expiresIn = 60 * 60 * 24; // 24 hours
        if (time() - $tokenData['created_at'] > $expiresIn) {
            // Expired — remove the token
            unset($tokens[$token]);
            file_put_contents('storage/tokens.json', json_encode($tokens));
            return null;
        }
        // Fetch full user info
        $user = User::find($tokenData['user_id']);

        if (!$user) return null;

        return [
            'id' => $user['id'],
            'role' => $user['role'],
            'name' => $user['name'],
            'email' => $user['email']
        ];
    }
}
