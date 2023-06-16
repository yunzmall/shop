<?php
/**
 * Created by PhpStorm.
 * Author:  
 * Date: 2017/4/17
 * Time: 下午4:09
 */

namespace app\frontend\modules\finance\models;

use \app\common\models\finance\Balance as BalanceModel;
use app\common\services\credit\ConstService;
use \app\frontend\modules\finance\models\BalanceTransfer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class Balance extends BalanceModel
{
    protected $appends = ['service_type_name','type_name','transfer_member_id','transfer_member_name'];


    /**
     * 设置全局作用域
     */
    public static function boot()
    {
        parent::boot();
        static::addGlobalScope(
            function(Builder $builder){
                return $builder->where('member_id',\YunShop::app()->getMemberId());
            }
        );
    }


    /**
     * 输出附加字段 transfer_member_id
     * @return string
     */
    public function getTransferMemberIdAttribute()
    {
        $transferModel = $this->getTransferModel();
        if ($this->attributes['type'] == ConstService::TYPE_INCOME) {
            return $transferModel->transferor ?: '';
        }
        return $transferModel->recipient ?: '';
    }

    /**
     * 输出附加字段 transfer_member_name
     * @return string
     */
    public function getTransferMemberNameAttribute()
    {
        $transferModel = $this->getTransferModel();
        if ($this->attributes['type'] == ConstService::TYPE_INCOME) {
            return $transferModel->transferInfo->realname ?: $transferModel->transferInfo->nickname ?: '';
        }
        return $transferModel->recipientInfo->realname ?: $transferModel->recipientInfo->nickname ?: '';
    }

    /**
     * 通过订单号获取
     * @return mixed
     */
    private function getTransferModel()
    {
        return BalanceTransfer::ofOrderSn($this->attributes['serial_number'])->withTransfer()->withRecipient()->first();
    }

    /**
     * 获取最新的三条记录
     * @return mixed
     */
    public static function getThreeData()
    {
        $log = self::orderBy('id', 'desc')
            ->limit(3)
            ->get(['type', 'created_at', 'new_money', 'change_money', 'service_type']);
        return $log;
    }

    /**
     * 获取余额明细列表
     * 按照月份分组
     * @return mixed
     */
    public static function getMemberRecordList()
    {
        $search = request()->search;
        $query = self::select(['id','created_at', 'type', 'change_money', 'new_money', 'service_type', 'serial_number'])
            ->with(
                [
                    'withdraw' => function ($query) {
                        $query->select('withdraw_sn', 'reject_reason');
                    },
                    'balanceRecharge' => function ($query) {
                        $query->select('ordersn', 'remark');
                    }
                ]
            );

        $query->when($search['date'], function ($table, $date){
            $date = date('Y-m-01', strtotime($date));
            $time = strtotime("{$date} +1 month -1 day") + 86399;
            $table->where('created_at', '<=', $time);
        });

        $query->searchRecord();

        $list = $query->orderBy('created_at','desc')->orderBy('id','desc')->paginate(15)->toArray();
        if($list['data']){
            foreach ($list['data'] as &$item){
                $item['group_date'] = date('Y-m', strtotime($item['created_at']));
                $item['action_type'] = '';
                if ($item['service_type'] == 3) {
                    switch ($item['type']) {
                        case 1:
                            $item['action_type'] = '转让人';
                            break;
                        case 2:
                            $item['action_type'] = '受让人';
                            break;
                        default;
                    }
                }
            }
            $list['data'] = collect($list['data'])->groupBy('group_date');
        }
        return $list;
    }



    /**
     * 统计每个月的支出与收入
     * @param $date_key
     * @return array
     */
    public static function getExpendAndIncome($date_key = []) : Collection
    {
        if(!$date_key){
            return collect();
        }
        $query = self::select(['type', 'change_money', 'created_at']);
        $query->searchRecord();
        $query->whereBetween('created_at', $date_key);
        $query->orderBy('created_at', 'desc');
        $record_list = $query->get()->toArray();

        //先让数据按照月份分组
        foreach ($record_list as &$record){
            $record['group_date'] = date('Y-m', strtotime($record['created_at']));;
        }

        $data = [];
        //根据每组（月份）的支出，收入进行统计。以月份作为键值
        collect($record_list)->groupBy('group_date')->each(function ($recordList, $date) use (&$data){
            $data[$date]['income'] = 0;//收入
            $data[$date]['expend'] = 0;//支出
            $recordList->each(function ($record) use (&$data, $date){
                if($record['type'] == 1){
                    $data[$date]['income'] = round($record['change_money'] + $data[$date]['income'], 2);
                }else if($record['type'] == 2){
                    $data[$date]['expend'] -= $record['change_money'];
                    $data[$date]['expend'] = round($data[$date]['expend'], 2);
                }
            });
        });
        return collect($data);
    }

    /**
     * 余额明细搜索记录
     * @param Builder $query
     * @return void
     */
    public function scopeSearchRecord(Builder $query)
    {
        $search = request()->search;
        $type_comment = [ConstService::TYPE_EXPENDITURE, ConstService::TYPE_INCOME];

        //如果条件是支出或者收入
        $query->when(in_array($search['record_type'], $type_comment), function ($table) use ($search) {
            $table->where('type', $search['record_type']);
        });

        //业务类型
        $query->when($search['service_type'], function ($table, $service_type){
            $table->where('service_type', $service_type);
        });

    }

    /**
     * 获取用户余额记录所拥有的服务类型
     * @return \app\framework\Database\Eloquent\Collection
     */
    public static function getServiceType()
    {
        $service_type = static::select(['service_type'])
            ->groupBy('service_type')
            ->pluck('service_type');
        return $service_type;
    }

}
