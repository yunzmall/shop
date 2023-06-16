<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzBusinessStaffTicketTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_business_staff_ticket')) {
            Schema::create('yz_business_staff_ticket',
                function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('uniacid')->comment('公众号')->index('uniacid_idx');
                    $table->integer('business_id')->comment('企业ID');
                    $table->string('crop_id')->comment('微信企业ID');
                    $table->string('agent_id')->comment('自建应用ID');
                    $table->string('user_id')->comment('企业微信userid');
                    $table->string('ticket')->comment('隐私授权ticket');
                    $table->integer('created_at')->nullable();
                    $table->integer('updated_at')->nullable();
                    $table->integer('deleted_at')->nullable();
                });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_business_staff_ticket` comment'企业管理--员工隐私信息ticket表'");//表注释

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_business_staff_ticket');
    }
}
