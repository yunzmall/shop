<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisabledToYzBusinessStaffTable extends Migration
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
                if (!Schema::hasColumn('yz_business_staff', 'disabled')) {
                    $table->tinyInteger('disabled')->default(0)->comment('是否禁用 0正常 1禁用');
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
