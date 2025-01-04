<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
      'http://localhost:8000',   // Laravel backend
      'http://127.0.0.1:8000',  // Same as localhost, but added to avoid CORS issues
      'http://localhost:8080',   // Vue.js frontend
      'http://127.0.0.1:8080',  // Same as localhost, but added to avoid CORS issues
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'   ],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

    // 'allowed_origins' => ['*'],

];
