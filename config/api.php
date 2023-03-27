<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Api params
    |--------------------------------------------------------------------------
    |
    | This values are forming the base path of your application api endpoints. 
    | E. g. {code}/{version}/{endpoint_path}
    | 
    */

    'code' => env('API_CODE', 'app_laravel_template'),
    
    'version' => env('API_VERSION', 'v1'),
];
