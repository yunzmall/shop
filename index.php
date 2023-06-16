<?php

if (preg_match('/^\/$/', $_SERVER['REQUEST_URI']) && strlen($_SERVER['REQUEST_URI'] ) == 1) {
    include_once "./official/fun.php";
    $url = returnUrl();

    header('HTTP/2.0 301 Moved Permanently');
    header('Location:' . $url);
    exit();
}

include_once __DIR__ . '/app/laravel.php';

include_once __DIR__ . '/app/yunshop.php';
