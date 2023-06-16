<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAfterParentIdAndStatusToYzMemberRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('yz_member_record', function (Blueprint $table) {
            Schema::table('yz_member_record', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_member_record', 'after_parent_id')) {
                    $table->integer('after_parent_id')->nullable()->default(0)->comment('修改后父级id');
                }
                if (!Schema::hasColumn('yz_member_record', 'status')) {
                    $table->integer('status')->nullable()->default(1)->comment('0修改中，1修改成功，2修改失败');
                }
                if (!Schema::hasColumn('yz_member_record', 'time')) {
                    $table->integer('time')->nullable()->default(0)->comment('耗时');
                }
            });
        });
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
