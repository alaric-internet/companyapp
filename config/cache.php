<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:17
 */

return [
    'adapter'  => 'File',
    'cacheDir' => cache_path('data'),
    'prefix' => env('CACHE_PREFIX', 'alaric'),
    'lifetime' => env('CACHE_LIFETIME', 86400),
];