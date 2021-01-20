<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateMiniTempUniacid extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_mini_template_corresponding')) {
            Schema::table('yz_mini_template_corresponding', function (Blueprint $table) {
                if (Schema::hasColumn('yz_mini_template_corresponding', 'uniacid')) {
                    $table->integer("uniacid")->change();
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
