<?php


namespace app\backend\modules\setting\controllers;


use app\common\components\BaseController;

class CronLogController extends BaseController
{
    public function index(){

        set_time_limit ( 0 ); // 脚本执行没有时间限
        ini_set("memory_limit","-1"); //不限制内存
        $cron_job_log=__DIR__."/../../../../../storage/logs/cron/cron-".date('Y-m-d',time()).".log";

        $file_exit=file_exists($cron_job_log);
        if(!$file_exit){
            $text = '队列日志不存在';
        }else{
            $file=fopen($cron_job_log,"r");
            if(!$file){
                $text = '队列日志不存在';
            }else{
                $text="";
                while(! feof($file))
                {
                    $text.=fgets($file)."<br />";
                }
                fclose($file);

            }
        }
        return view('setting.shop.cron_log',['data'=>$text]);

    }


}