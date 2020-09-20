<?php

return [
    'secret' => env('CENTRIFUGE_SECRET'),
    'api_key' => env('CENTRIFUGE_API_KEY'),
    'connection_url' => env('CENTRIFUGE_CONNECTION_URL', 'ws://localhost:8000/connection/websocket'),
    'url' => env('CENTRIFUGE_URL', 'http://localhost:8000'),
    'verify' => env('CENTRIFUGE_VERIFY', false),
    'ssl_key' => env('CENTRIFUGE_SSL_KEY', null),
    'broadcast_error' => env('CENTRIFUGE_BROADCAST_ERROR', true),
];
