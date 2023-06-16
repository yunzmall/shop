<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDataToYzLogisticsSetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('yz_logistics_set')) {
            Schema::table('yz_logistics_set', function (Blueprint $table) {
                if (Schema::hasColumn('yz_logistics_set', 'data')) {
                    $table->text('data')->nullable()->change();
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
