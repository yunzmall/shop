<?php

namespace app\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CorrectionSupplierData extends Command
{
    protected $signature = 'migrate:correction_supplier_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '纠正供应商商品表和配送表member_id不同步问题';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //纠正供应商商品表和配送表member_id不同步
        if(Schema::hasTable('yz_supplier_goods') && Schema::hasTable('yz_supplier_dispatch')){
            $table_arr = ['yz_supplier_goods','yz_supplier_dispatch'];

            foreach ($table_arr as $table){
                \Illuminate\Support\Facades\DB::beginTransaction();
                //两表同字段分组过后使用全查询，再次分组统计数量后为1的是 yz_supplier 或 yz_supplier_goods表只有一条数据
                $sql = "SELECT member_id,supplier_id FROM (SELECT id as supplier_id,member_id FROM " . app('db')->getTablePrefix() . "yz_supplier GROUP BY supplier_id,member_id UNION ALL SELECT supplier_id,member_id FROM " . app('db')->getTablePrefix() . $table . " GROUP BY supplier_id,member_id) tb1 GROUP BY member_id,supplier_id HAVING count(*) = 1";
                $data = \Illuminate\Support\Facades\DB::select($sql);

                foreach ($data as $item){
                    $id = \Illuminate\Support\Facades\DB::select("select id from " . app('db')->getTablePrefix() . $table . " WHERE supplier_id='".$item['supplier_id']."' AND member_id='" . $item['member_id'] . "'");

                    //yz_supplier_goods 数据为空的不需要理会，说明供应商没有绑定商品，不需要更改。  反则有数据的说明之前进行过更换供应商，绑定商品的member_id没有进行更换的
                    if(!empty($id)){
                        $ids = implode(',',array_column($id,'id'));
                        $later_member_id = \Illuminate\Support\Facades\DB::select("select member_id from ".app('db')->getTablePrefix()."yz_supplier WHERE id='".$item['supplier_id']."'")[0]['member_id'];

                        file_put_contents(storage_path('logs/EditSupplierGoods.txt'), print_r(date('Ymd His').'修改的' . $table . '表ID(' . $ids . ')' . '，修改的supplier_id:' . $item['supplier_id'] . "。修改前member_id:" . $item['member_id'] . ",修改后member_id:" . $later_member_id . PHP_EOL,1), FILE_APPEND);

                        //批量更新
                        if(!isset($u_sql)){
                            $u_sql = "UPDATE " . app('db')->getTablePrefix() . $table . " SET member_id = CASE WHEN id IN (" . $ids . ") THEN " . $later_member_id;
                        }else{
                            $u_sql .= " WHEN id IN(" . $ids . ") THEN " . $later_member_id;
                        }
                    }
                }

                if(isset($u_sql) && !empty($u_sql)){
                    $u_sql .= " ELSE member_id END";
                    $res = \Illuminate\Support\Facades\DB::update($u_sql);
                    if(!$res) {
                        \Illuminate\Support\Facades\DB::rollBack();
                        \Log::error($table . '迁移更新数据失败',"SQL语句：" . $u_sql);
                    }
                    unset($u_sql);
                }
                \Illuminate\Support\Facades\DB::commit();
            }
            \Log::info('供应商迁移数据更新成功');
        }

    }
}
