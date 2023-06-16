<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/12
 * Time: 下午2:39
 */

namespace app\frontend\modules\finance\models;


use Illuminate\Support\Collection;
use Intervention\Image\Point;
use app\common\helpers\Cache;

class PointLog extends \app\common\models\finance\PointLog
{
    /*public static function getPointTotal($member_id, $type = null)
    {
        $builder = PointLog::select()->byMemberId($member_id)->type($type)->limit(5)->orderBy('id', 'desc');
        return $builder;
    }

    public static function getLastTime($member_id, $type)
    {
        $time = PointLog::select('created_at')->byMemberId($member_id)->type($type)->first();
        return $time;
    }*/

    /**
     * 新的获取积分明细方法
     * @param $member_id
     * @param null $type
     * @param null $mode
     * @param null $month
     * @return array
     */
    public  function getPointRecord($member_id, $type = null, $mode = null, $month = null){
        $original=PointLog::select(['relation_id','id','created_at','member_id','point_mode','point','before_point','after_point','point_income_type','point_mode'])
            ->byMemberId($member_id)->type($type)->mode($mode)->month($month)
            ->orderBy('created_at', 'desc')->orderBy('id','desc')->paginate(15)->toArray();
        if(empty($original['data'])&&!$month){
            return ['record_list'=>['data'=>[date('Y-m')=>[]]],'point_arr'=>[date('Y-m')=>['income'=>0,'expand'=>0]],'source_comment'=>$this->getSource($member_id),];
        }
        $month=date('Y-m',strtotime($month));
        $list=$this->getFilterData($original,$month);
        $monthData=$this->getMonthData($list['month_list']?:[$month],$member_id);
        $source=(new \app\common\models\finance\PointLog())->sourceComment();
        return ['record_list'=>$list['record_list'],'source_comment'=>$this->getSource($member_id),'point_arr'=>$monthData];
    }

    /**
     * 1.1.138前取积分明细方法
     * @param $member_id
     * @param null $type
     * @param null $mode
     * @param null $month
     * @return mixed
     */
    public static function getPointLogList($member_id, $type = null)
    {
        $builder = PointLog::select()->byMemberId($member_id)->type($type)->orderBy('id', 'desc')->paginate(15)->toArray();
        return $builder;
    }

    /**
     * 获取用户最新的三条记录
     * @param $member_id
     * @return mixed
     */
    public function getLatestRecord($member_id){
        return PointLog::select(['id','created_at','member_id','point_mode','point','before_point','after_point','point_income_type'])->byMemberId($member_id)->orderBy('created_at','desc')->limit(3)->get()->toArray();
    }


    public function scopeByMemberId($query, $member_id)
    {
        return $query->where('member_id', $member_id)->uniacid();
    }

    public function scopeType($query, $type)
    {
        if (!isset($type) || $type == 0) {
            return $query;
        }
        return $query->where('point_income_type', $type);
    }

    public function scopeMode($query, $mode)
    {
        if (!isset($mode) || $mode == 0) {
            return $query;
        }
        return $query->where('point_mode', $mode);
    }

    public function scopeMonth($query, $month)
    {
        if (!isset($month) || $month == '') {
            return $query;
        }
        return $query->where('created_at', '<', strtotime('last day of ' . $month)+86399);
    }


    /**
     * 获取按月份分类后的积分明细数据
     * @param $data
     * @return array
     */
    public function getFilterData($data,$month)
    {

        $list = [];
        foreach ($data['data'] as $val) {
            $list[date('Y-m', strtotime($val['created_at']))][] = $val;
        }
        if(empty($list)){
            $list[$month]=[];
        }
        $month=array_keys($list);
        $data['data']=$list;

        return ['record_list'=>$data,'month_list'=>$month];
    }

    /**
     * 获取月份区间中，每个月的收入和支出
     * @param $month
     * @param $member_id
     * @return array
     */
    private function getMonthData($month,$member_id){

        $startMonth=strtotime($month[count($month)-1]);
        $endMonth=strtotime('last day of '.$month[0])+86399;
        $original=PointLog::selectRaw("YEAR(FROM_UNIXTIME(created_at,'%Y-%m-%d')) as years ,Right(100 + MONTH(FROM_UNIXTIME(created_at,'%Y-%m-%d')),2) as months ,sum(if(point_income_type=1,point,0)) as income,sum(if(point_income_type=-1,point,0))as expand")
            ->byMemberId($member_id)
            ->whereBetween('created_at',[$startMonth,$endMonth])
            ->groupByRaw("years,months")
            ->get()->toArray();
        $result=[];
        if(count($original)){
            foreach($original as $val){
                $result[$val['years'].'-'.$val['months']]=['income'=>$val['income'],'expand'=>$val['expand']];
            }
        }else{
            $result[$month[0]]=['income'=>0,'expand'=>0];
        }

        return $result;
    }

    /**
     * 获取积分来源
     * @param $member_id
     * @return mixed
     */
    private function getSource($member_id){
        $redis_key = "ServiceTypeList:".$member_id;
        $result=[];
        if(Cache::has($redis_key)){
            $result = Cache::get($redis_key);
        }else{
            $service_type_arr = (new \app\common\models\finance\PointLog())->sourceComment();
            $service_type_key = PointLog::where('member_id',$member_id)
                ->select(['point_mode'])
                ->groupBy('point_mode')
                ->pluck('point_mode');
            $service_type_arr = $service_type_key->map(function ($serviceKey) use ($service_type_arr){
                return [
                    'id' => $serviceKey,
                    'name' => $service_type_arr[$serviceKey],
                ];
            })->values();
            foreach($service_type_arr as $key=>$value){
                $result[$value['id']]=$value['name'];
            }
            //根据当前会员的积分明细查出所拥有的服务类型，存进缓存
            Cache::put($redis_key, $result, 1440);
        }
        return  $result;
    }
}
