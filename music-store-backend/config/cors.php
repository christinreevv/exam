<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // ВАЖНО: здесь должен быть только localhost:3000, а не '*'
    'allowed_origins' => ['http://localhost:3000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ВАЖНО: обязательно true, иначе куки не работают
    'supports_credentials' => true,

];
