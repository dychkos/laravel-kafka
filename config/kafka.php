<?php


return [
    'brokers' => env('KAFKA_BROKERS', 'localhost:9092'),
    'group_id' => env('KAFKA_GROUP_ID', 'laravel-consumer-group'),
    'client_id' => env('KAFKA_CLIENT_ID', 'laravel-client'),
    'auto_offset_reset' => env('KAFKA_AUTO_OFFSET_RESET', 'earliest'),
    'enable_auto_commit' => env('KAFKA_AUTO_COMMIT', true),
    'session_timeout_ms' => env('KAFKA_SESSION_TIMEOUT', 6000),
    'socket_timeout_ms' => env('KAFKA_SOCKET_TIMEOUT', 60000),
];
