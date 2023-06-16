<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRightTypeToYzBusinessStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_business_staff')) {
            Schema::table('yz_business_staff', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_business_staff', 'right_type')) {
                    $table->tinyInteger('right_type')->default(0)->comment('权限类型 0部门权限 1成员独立权限');
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

    }
}
