<?php

/*
 * This file is part of the jiannei/laravel-crawler.
 *
 * (c) jiannei <longjian.huang@foxmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

 $suffix = now()->format('Y-m-d');

return [
    'debug' => false, // http client debug

    'log' => [
        'driver' => 'daily',
        'path' => storage_path('logs/http.log'),
        'level' => env('CRAWLER_LOG_LEVEL', 'debug'),
        'days' => 14,
    ],

    'guzzle' => [
        // https://docs.guzzlephp.org/en/stable/request-options.html
        'options' => [
            'debug' => false, // fopen(storage_path("logs/guzzle-{$suffix}.log"), 'a+')
            'connect_timeout' => 10,
            'http_errors' => false,
            'timeout' => 30,

            'headers' => [
                'Accept-Encoding' => 'gzip',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
            ],
        ],
    ],
];
