<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | The default broadcaster used when an event needs to be broadcast.
    | If PUSHER_APP_KEY is not set, we fall back to 'log' to prevent errors.
    |
    */

    'default' => env('BROADCAST_DRIVER', env('PUSHER_APP_KEY') ? 'pusher' : 'log'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Define all broadcast connections here.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY', ''),
            'secret' => env('PUSHER_APP_SECRET', ''),
            'app_id' => env('PUSHER_APP_ID', ''),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER', 'eu'),
                'useTLS' => true,
            ],
            'client_options' => [
                // Optional Guzzle client options
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];