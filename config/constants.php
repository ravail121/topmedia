<?php

return [
    'empty_object' => new stdClass(),
    'google_map_key' => 'AIzaSyBRR40Ie35qkoC1F5-v3YsZ1eWt51F3Qqg',
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
    'firebase_server_key' => 'AAAAoaCu6q8:APA91bG1C2lzjruLaEPejwaX0YWN_1TgQ1cIfO9BQDPSsS1HAZMVDCtarqRHuKfPWrOaDKjQ24vx9ez2-yh35FykQ1sxfO0UlTepDXwMzOTUGC5Ucr4sEYrA3Pbq_DaWk7XY3yB2nyY2',
];
