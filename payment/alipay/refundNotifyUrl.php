<?php
/**
 * Created by PhpStorm.
 * Author: 
 * Date: 2017/3/24
 * Time: 下午7:54
 */

define('IN_IA', true);
$boot_file = __DIR__ . '/../../../../framework/bootstrap.inc.php';

if (file_exists($boot_file)) {

    @include_once $boot_file;

}

include_once __DIR__ . '/../../app/laravel.php';

include_once __DIR__ . '/../../app/yunshop.php';