<?php

$env=@file_get_contents('.env');

if (empty($env)) {
    $env=@file_get_contents('../../.env');
}

$is_yunshop=strpos($env,"APP_Framework");

$extend = '';
$boot_file = __DIR__ . '/../../framework/bootstrap.inc.php';

if (!$is_yunshop) {
    include_once $boot_file;
} else {
    $extend = '/../..';
}

include_once __DIR__ . $extend . '/app/laravel.php';

include_once __DIR__ . $extend . '/app/yunshop.php';