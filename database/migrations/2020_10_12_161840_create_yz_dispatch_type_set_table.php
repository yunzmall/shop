<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzDispatchTypeSetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_dispatch_type_set')) {
            Schema::create('yz_dispatch_type_set', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0)->nullable();
                $table->integer('dispatch_type_id')->default(0)->index('dispatch_type_idx')->comment('配送方式ID');
                $table->tinyInteger('sort')->default(0)->nullable()->comment('排序');
                $table->tinyInteger('enable')->default(0)->nullable()->comment('是否开启');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();

            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_dispatch_type_set` comment '配送方式设置'");//表注释

        }

        \app\common\models\DispatchType::whereIn('id', [1,8])->update([
            'sort' => 0,
            'enable' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_dispatch_type_set');
    }
}
