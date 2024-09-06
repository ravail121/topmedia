<?php

return [
    'empty_object' => new stdClass(),
    'google_map_key' => '',
    'asset_url' => env('APP_URL'),
    'upload_type' => 'local',
    'default' => [
        'image' => 'uploads/user/user.png',
        'user_image' => 'uploads/user/user.png',
        'no_image_available' => 'assets/general/image/no_image.jpg',
    ],
    'upload_paths' => [
        'exception_upload' => 'uploads/exception',
        'user_profile_image' => 'uploads/user',
        'posts' => 'uploads/posts',
        'admin_upload' => 'uploads/admin',
        'user_audio_file' => 'uploads/user/audio'
    ],
    'push_log' => true,
    'firebase_server_key' => '',
    
    // Agora settings
    'agora' => [
        'app_id' => env('AGORA_APP_ID'),
        'app_certificate' => env('AGORA_APP_CERTIFICATE'),
    ],
];
