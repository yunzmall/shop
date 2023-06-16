<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateYzBusinessStaffTicketCodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_business_staff_ticket_code')) {
            Schema::create('yz_business_staff_ticket_code',
                function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('uniacid')->comment('公众号')->index('uniacid_idx');
                    $table->integer('business_id')->comment('企业ID');
                    $table->string('ticket_url')->comment('ticket链接');
                    $table->string('code_path')->comment('二维码路径');
                    $table->string('code_url')->comment('二维码链接');
                    $table->integer('created_at')->nullable();
                    $table->integer('updated_at')->nullable();
                    $table->integer('deleted_at')->nullable();
                });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `" . app('db')->getTablePrefix() . "yz_business_staff_ticket_code` comment'企业管理--员工隐私授权二维码表'");//表注释

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_business_staff_ticket_code');
    }
}
