<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateImsYzOrderSentJobTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_order_sent_job')) {

            Schema::create('yz_order_sent_job', function (Blueprint $table) {
                $table->increments('id');
                $table->enum('status', ['waiting','finished','failed']);
                $table->integer('order_id')->nullable();
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `".app('db')->getTablePrefix()."yz_order_sent_job` comment'订单--发货队列情况'");//表注释

        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('yz_order_sent_job')) {

            Schema::drop('yz_order_sent_job');
        }
    }

}
