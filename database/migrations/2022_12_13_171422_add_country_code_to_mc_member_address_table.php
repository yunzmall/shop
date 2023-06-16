<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryCodeToMcMemberAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('mc_member_address')) {
            Schema::table('mc_member_address', function (Blueprint $table) {
                if (!Schema::hasColumn('mc_member_address', 'country_code')) {
                    $table->string('country_code')->default('')->nullable()->comment('国家区号');
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
