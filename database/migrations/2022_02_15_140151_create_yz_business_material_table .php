<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzBusinessMaterialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_business_material')) {
            Schema::create('yz_business_material', function (Blueprint $table) {
                $table->integer('id',true);
                $table->integer('uniacid');
                $table->integer('business_id');
                $table->string('type',10)->comment('附件类型');
                $table->string('file_name',50)->comment('附件名称');
                $table->string('file_url')->comment('附件完整地址');
                $table->string('media_id')->comment('企业微信回传的素材标识（三天有效）');
                $table->integer('updated_at');

                $table->index(['uniacid','business_id'],'ids_Uni_BniId');
                $table->index(['type','file_name'],'ids_Type_FName');
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix()
                . "yz_business_material` comment '企业微信素材标识管理表'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
