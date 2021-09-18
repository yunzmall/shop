<?php

use Illuminate\Database\Seeder;

class YzMaterialCenterSeeder extends Seeder
{
    protected $table = 'yz_goods_material';
    protected $category_table = 'yz_material_category';
    protected $goods_category_table = 'yz_material_goods_category';
    protected $uniTable = 'uni_account';//微擎

    public function __construct()
    {
        if (config('app.framework') == 'platform') {
            $this->uniTable = 'yz_uniacid_app';
        }
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //获取全部平台 id
        $uniAccount = \Illuminate\Support\Facades\DB::table($this->uniTable)->get();

        //获取全部素材id，批量填充分类到表中
        $material_ids = \Illuminate\Support\Facades\DB::table($this->table)->select('id','uniacid')->get();

        $has_category_table = \Illuminate\Support\Facades\DB::table($this->category_table)->select('id')->first();
        $goods_category_table = \Illuminate\Support\Facades\DB::table($this->goods_category_table)->select('id')->first();

        $category_data = $goods_category_data = [];
        $i = 1;

        if(!empty($material_ids) && !$has_category_table && !$goods_category_table){
            //每个平台生成默认分类
            foreach ($uniAccount as $u) {
                $category_data[] = [
                    'uniacid' => $u['uniacid'],
                    'name' => '默认分类(一级)',
                    'parent_id' => 0,
                    'level' => 1,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
                $category_data[] = [
                    'uniacid' => $u['uniacid'],
                    'name' => '默认分类',
                    'parent_id' => $i,
                    'level' => 2,
                    'created_at' => time(),
                    'updated_at' => time(),
                ];
                foreach ($material_ids as $v){
                    $ids = $i+1;

                    if($v['uniacid'] == $u['uniacid']){
                        $goods_category_data[] = [
                            'material_id' => $v['id'],
                            'category_id' => $ids,
                            'category_ids' => $i.",".$ids,
                            'created_at' => time(),
                            'updated_at' => time(),
                        ];
                    }
                }
                $i += 2;
            }

            //添加数据
            \Illuminate\Support\Facades\DB::table($this->category_table)->insert($category_data);
            \Illuminate\Support\Facades\DB::table($this->goods_category_table)->insert($goods_category_data);

        }

    }
}
