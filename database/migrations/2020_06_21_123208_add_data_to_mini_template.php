<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDataToMiniTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_mini_app_template_message')) {

            Schema::table('yz_mini_app_template_message', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_mini_app_template_message', 'small_type')) {
                    $table->tinyInteger('small_type')->default(0)->comment('新模板标识');
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
