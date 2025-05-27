<?php
class RateLimiter {
    private static $limit = 5;
    private static $window = 600; // 10 minutes in seconds
    private static $file = __DIR__ . '/../storage/rate_limits.json';

    public static function allow(string $userId): bool {
        if (!file_exists(self::$file)) {
            file_put_contents(self::$file, json_encode([]));
        }

        $limits = json_decode(file_get_contents(self::$file), true);

        $now = time();
        $userKey = (string)$userId;

        if (!isset($limits[$userKey])) {
            $limits[$userKey] = [];
        }

        // Remove old timestamps
        $limits[$userKey] = array_filter($limits[$userKey], fn($timestamp) => $timestamp > $now - self::$window);

        if (count($limits[$userKey]) >= self::$limit) {
            return false; // rate limit hit
        }

        // Allow and record this attempt
        $limits[$userKey][] = $now;
        file_put_contents(self::$file, json_encode($limits));

        return true;
    }
}
