<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLastlogintimeDevicetypeToYzMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (Schema::hasTable('yz_member')) {
            Schema::table('yz_member', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_member','last_login_time')){
                    $table->integer('last_login_time')->nullable()->default(0);
                }
                if (!Schema::hasColumn('yz_member','system_type')) {
                    $table->string('system_type', 255)->nullable()->default('');
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
