<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzMemberChangeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_member_change_log')) {
            Schema::create('yz_member_change_log', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('公众号');
                $table->integer('member_id')->comment('会员id');
                $table->integer('member_id_after')->comment('变更后会员id');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
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
        Schema::dropIfExists('yz_member_merge_log');
    }
}
