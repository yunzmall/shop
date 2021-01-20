<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSettingDataToSynchronizedBinder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_synchronized_binder')) {
            Schema::table('yz_synchronized_binder', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_synchronized_binder', 'setting_data')) {
                    $table->text('setting_data')->nullable()->comment('当前会员设置');
                }
                if (!Schema::hasColumn('yz_synchronized_binder', 'save_type')) {
                    $table->string('save_type')->default('')->nullable()->comment('保存类型');
                }
            });
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
    }
}
