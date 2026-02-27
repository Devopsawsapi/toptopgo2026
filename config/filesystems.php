<?php

// config/filesystems.php
// Ajoutez 'backblaze' dans le tableau 'disks'

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
            'throw'  => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],

        // ✅ Backblaze B2
        'backblaze' => [
            'driver'                  => 's3',
            'key'                     => env('BACKBLAZE_KEY_ID'),
            'secret'                  => env('BACKBLAZE_APPLICATION_KEY'),
            'region'                  => env('BACKBLAZE_REGION', 'us-west-004'),
            'bucket'                  => env('BACKBLAZE_BUCKET'),
            'endpoint'                => env('BACKBLAZE_ENDPOINT'),
            'url'                     => env('BACKBLAZE_ENDPOINT') . '/file/' . env('BACKBLAZE_BUCKET'),
            'visibility'              => 'public',
            'throw'                   => false,
            'use_path_style_endpoint' => true,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];