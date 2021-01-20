<?php
/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/24
 * Time: 下午2:29
 */

namespace app\common\services;


class System
{
    private $is_show;  //只支持linux
    private $loadAvg;  //系统负载
    private $cpu;      //CPU咯
    private $RAM;      //内存
    private $disk;     //磁盘

    public function __construct()
    {
        switch (PHP_OS) {
            case 'Linux' :

                $this->is_show = 1;

                break;

            default :

                $this->is_show = 0;

                break;
        }

    }

    public function index()
    {
        $this->getLoadAvg();
        $this->getCpu();
        $this->getRAM();
        $this->getDisk();

        return [
            'loadAvg' => $this->loadAvg,
            'cpu' => $this->cpu,
            'RAM' => $this->RAM,
            'disk' => $this->disk,
            'is_show' => $this->is_show,
        ];
    }

    /**
     * @return bool
     * @return 实例 : 1.63 0.61 0.22
     * 1.63（1分钟平均负载） 0.61（5分钟平均负载） 0.22（15分钟平均负载） 1/228（分子是当前正在运行的进程数，分母是总的进程数）
     */
    private function getLoadAvg()
    {
        if (false === ($str = @file("/proc/loadavg"))) return false;

        $str = explode(" ", implode("", $str));

        $str = array_chunk($str, 4);

        $percent = explode('/' ,$str[0][3]);

        $str[0][3] =  round($percent[0]/$percent[1]*100, 2);
        $this->loadAvg = $str[0];


    }


    private function getCpu()
    {

        if (false === ($str = @file("/proc/cpuinfo"))) return false;

        $str = implode("", $str);

        @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);

        if (false !== is_array($model[1])) {

            $res['cpu']['num'] = sizeof($model[1]);
            if ($res['cpu']['num'] == 1)
                $x1 = '';
            else
                $x1 = ' ×' . $res['cpu']['num'];
            $res['cpu']['model'][] = $model[1][0];

            if (false !== is_array($res['cpu']['model'])) $res['cpu']['model'] = implode("<br />", $res['cpu']['model']);

            $stat1 = self::GetCoreInformation();
            sleep(1);
            $stat2 = self::GetCoreInformation();
            $data = self::GetCpuPercentages($stat1, $stat2);
            $res['cpu']['using'] = $data['cpu0']['user']; //cpu使用率
            $this->cpu = $res['cpu'];

        }

    }

    private function getRAM()
    {
        if (false === ($str = @file("/proc/meminfo"))) return false;

        $str = implode("", $str);

        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
        preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);


        $res['memTotal'] = round($buf[1][0]/1024, 2);
        $res['memBuffers'] = round($buffers[1][0]/1024, 2);
        $res['memFree'] = round($buf[2][0]/1024, 2);
        $res['memCached'] = round($buf[3][0]/1024, 2);

        $res['memUsed'] = round($res['memTotal']-$res['memFree'] ,3);
        
        $res['memRealUsed'] = $res['memTotal'] - $res['memFree'] - $res['memCached'] - $res['memBuffers']; //真实内存使用
        $res['memRealFree'] = $res['memTotal'] - $res['memRealUsed']; //真实空闲
        $res['memPercent'] = (floatval($res['memTotal'])!=0)?round($res['memRealUsed']/$res['memTotal']*100,2):0;

        if($res['memTotal']<1024)
        {
            $res['memTotal'] = $res['memTotal']." M";
            $res['memUsed'] = $res['memRealUsed']." M";
            $res['memFree'] = $res['memRealFree']." M";
        }
        else
        {
            $res['memTotal']  = round($res['memTotal']/1024,3)." G";
            $res['memUsed']  = round($res['memRealUsed']/1024,3)." G";
            $res['memFree']  = round($res['memRealFree']/1024,3)." G";
        }

        $this->RAM = $res;
    }

    private function getDisk()
    {
        //硬盘
        $re['total'] = round(@disk_total_space(".")/(1024*1024*1024),3); //总
        $re['free'] = round(@disk_free_space(".")/(1024*1024*1024),3); //可用
        $re['used'] = round($re['total']-$re['free'], 3); //已用
        $re['percent'] = (floatval($re['total'])!=0)?round($re['used']/$re['total']*100,2):0;

        $this->disk = $re;
    }
    private function GetCoreInformation() {$data = file('/proc/stat');$cores = array();foreach( $data as $line ) {if( preg_match('/^cpu[0-9]/', $line) ){$info = explode(' ', $line);$cores[]=array('user'=>$info[1],'nice'=>$info[2],'sys' => $info[3],'idle'=>$info[4],'iowait'=>$info[5],'irq' => $info[6],'softirq' => $info[7]);}}return $cores;}
    private function GetCpuPercentages($stat1, $stat2) {if(count($stat1)!==count($stat2)){return;}$cpus=array();for( $i = 0, $l = count($stat1); $i < $l; $i++) {	$dif = array();	$dif['user'] = $stat2[$i]['user'] - $stat1[$i]['user'];$dif['nice'] = $stat2[$i]['nice'] - $stat1[$i]['nice'];	$dif['sys'] = $stat2[$i]['sys'] - $stat1[$i]['sys'];$dif['idle'] = $stat2[$i]['idle'] - $stat1[$i]['idle'];$dif['iowait'] = $stat2[$i]['iowait'] - $stat1[$i]['iowait'];$dif['irq'] = $stat2[$i]['irq'] - $stat1[$i]['irq'];$dif['softirq'] = $stat2[$i]['softirq'] - $stat1[$i]['softirq'];$total = array_sum($dif);$cpu = array();foreach($dif as $x=>$y) $cpu[$x] = round($y / $total * 100, 2);$cpus['cpu' . $i] = $cpu;}return $cpus;}

}