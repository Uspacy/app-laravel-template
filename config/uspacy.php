<?php

return [
    // Keyed by URL pathname: /{pathname}/...
    'integrations' => [
        'example' => [
            'integration_code' => env('USPACY_EXAMPLE_INTEGRATION_CODE', 'example'),
            'client_id' => env('USPACY_EXAMPLE_CLIENT_ID'),
            'client_secret' => env('USPACY_EXAMPLE_CLIENT_SECRET'),
        ],
    ],
];
