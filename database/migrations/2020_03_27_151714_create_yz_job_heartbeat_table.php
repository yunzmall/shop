<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzJobHeartbeatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_job_heartbeat')) {
            Schema::create('yz_job_heartbeat', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('execution_time')->nullable()->comment('执行时间');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_job_heartbeat comment '队列执行记录表'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_job_heartbeat');
    }
}
