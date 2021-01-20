<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/11/20
 * Time: 3:41 PM
 */

namespace app\framework\Support\Facades;

use app\framework\Log\SimpleLog;

class Log extends \Illuminate\Support\Facades\Log
{
    /**
     * @return SimpleLog
     */
    static public function order(){
        return app('OrderManager')->log;
    }
    static public function debug($message,$content = []){
        if(!is_array($content)){
            $content = [$content];
        }
        app('Log.debug')->add($message,$content);
    }
    static public function error($message,$content = []){
        if(!is_array($content)){
            $content = [$content];
        }
        app('Log.error')->add($message,$content);
    }
    static public function info($message,$content = []){
        if(!is_array($content)){
            $content = [$content];
        }
        app('log')->info($message,$content);
    }
}