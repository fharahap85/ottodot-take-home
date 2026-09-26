<?php

return [

    'version' => env('ASSET_VERSION', null),
    'encryption_key' => env('INERTIA_ENCRYPTION_KEY'),
    'shared' => [],
    'testing' => [
        'ensure_pages_exist' => true,
    ],

];