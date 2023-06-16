<?php

namespace app\frontend\modules\finance\services;

use app\frontend\modules\finance\models\Balance;
use Illuminate\Support\Collection;

class BalanceRecordService
{

    /**
     * 余额记录列表，支出与收入统计
     * @return array
     */
    public function getRecordData()
    {
        $record_list = Balance::getMemberRecordList();
        if(!$record_list['data']){
            return false;
        }
        $date_key = $this->getDateKey($record_list['data']);
        $money_arr = Balance::getExpendAndIncome($date_key);
        $this->fillDate($record_list['data'], $date_key, "record");
        $this->fillDate($money_arr, $date_key, "price");
        $data = [
            'money_arr' => $money_arr,
            'record_list' => $record_list,
        ];
        return $data;
    }

    /**
     * 补全没有数据的月份，让他们显示为空数组
     * @param Collection $data
     * @param $date_key
     * @param $type price = 月收入数据的补全； record = 详细数据的补全
     * @return void
     */
    private function fillDate(Collection &$data, $date_key, $type)
    {
        $all_date = $this->monthRange($date_key[1], $date_key[0]);
        //月份差，获得缺少的月份
        $diff_date = array_diff($all_date, $data->keys()->all());
        foreach ($diff_date as $date){
            if($type == 'price'){
                $data[$date] = [
                    "income" => 0,
                    "expend" => 0,
                ];
            }else{
                $data[$date] = [];
            }
        }
        //根据时间倒序
        $data = $data->sortByDesc(function ($record, $date){
            return strtotime($date);
        });
    }

    /**
     * 根据开始到结束得出全部月份
     * @param $start
     * @param $end
     * @return array
     */
    private function monthRange($start, $end)
    {
        $start = date('Y-m',$start); // 转换为月
        $range = [];
        $i = 0;
        do {
            $month = date('Y-m', strtotime(date('Y-m',$end) . ' + ' . $i . ' month'));

            //echo $i . ':' . $month . '<br>';
            $range[] = $month;
            $i++;
        } while ($month < $start);

        return $range;
    }

    /**
     * 获取分组的第一个月和最后一个月的日期
     * @param $record_list
     * @return array|void
     */
    private function getDateKey($record_list)
    {
        $date = $record_list->keys();

        if($record_list->isEmpty()){
            return;
        }
        if($date->count() > 1){
            return [
                strtotime($date->last()),
                strtotime("$date[0] +1 month -1 day") + 86399,
            ];
        }else{
            return [
                strtotime("$date[0]"),
                strtotime("$date[0] +1 month -1 day") + 86399,
            ];
        }
    }


}
