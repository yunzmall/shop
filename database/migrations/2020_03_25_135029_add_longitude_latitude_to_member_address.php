<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLongitudeLatitudeToMemberAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_member_address')) {
            Schema::table('yz_member_address', function (Blueprint $table) {

                if (!Schema::hasColumn('yz_member_address', 'longitude')) {
                    $table->string('longitude', 15)->default('');
                }
                if (!Schema::hasColumn('yz_member_address', 'latitude')) {
                    $table->string('latitude', 15)->default('');
                }
            });
        }
        if (Schema::hasTable('mc_member_address')) {
            Schema::table('mc_member_address', function (Blueprint $table) {
                if (!Schema::hasColumn('mc_member_address', 'longitude')) {
                    $table->string('longitude', 15)->default('');
                }
                if (!Schema::hasColumn('mc_member_address', 'latitude')) {
                    $table->string('latitude', 15)->default('');
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
