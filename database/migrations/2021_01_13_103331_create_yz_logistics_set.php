<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzLogisticsSet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (!Schema::hasTable('yz_logistics_set')) {
            Schema::create('yz_logistics_set', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0);
                $table->integer('type')->default(0)->comment('类型');
                $table->text('data')->nullable()->comment('设置数据');
                $table->integer('status')->default(0)->nullable()->comment('状态');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });

            $list = \Illuminate\Support\Facades\DB::table('yz_setting')->where('group','shop')->where('key','express_info')->get();

            $data = [];
            foreach ($list as $item) {
                \Illuminate\Support\Facades\DB::table('yz_logistics_set')->insert([
                    'uniacid' => $item['uniacid'],
                    'data'    => $item['value'],
                    'type'    => 1
                ]);
            }
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_logistics_set` comment'物流查询设置表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('dispatch_classify');
    }
}
