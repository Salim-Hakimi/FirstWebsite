<?php

return [
    'password' => [
        'min_length' => (int) env('PASSWORD_MIN_LENGTH', 12),
        'check_compromised' => (bool) env('PASSWORD_CHECK_COMPROMISED', false),
    ],

    'requests' => [
        'max_content_length' => (int) env('SECURITY_MAX_CONTENT_LENGTH', 25 * 1024 * 1024),
        'blocked_patterns' => [
            '/\x00/',
            '/(?:^|[\/\\\\])\.\.(?:[\/\\\\]|$)/',
            '/<\s*script\b/i',
            '/javascript\s*:/i',
            '/data\s*:\s*text\/html/i',
            '/(?:php|file|phar|zip):\/\//i',
            '/\b(?:union\s+select|select\s+.+\s+from|insert\s+into|drop\s+table|sleep\s*\(|benchmark\s*\()\b/i',
            '/(?:\bor\b|\band\b)\s+[\w\'"]+\s*=\s*[\w\'"]+/i',
        ],
    ],
];
