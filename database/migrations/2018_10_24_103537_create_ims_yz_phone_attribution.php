<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImsYzPhoneAttribution extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (!Schema::hasTable('yz_phone_attribution')) {
            Schema::create('yz_phone_attribution', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uid')->default(0);
                $table->integer('uniacid')->default(0);
                $table->string('province', 200)->default('')->nullable()->comment('省');
                $table->string('city', 255)->default('')->nullable()->comment('市');
                $table->string('sp', 255)->default('')->nullable()->comment('运营商');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE `" . app('db')->getTablePrefix() . "yz_phone_attribution` comment '商城--电话号码归属地'"
            );
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
        Schema::dropIfExists('yz_phone_attribution');
    }
}
