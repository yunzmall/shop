<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLevelSetToYzComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_comment')) {
            Schema::table('yz_comment', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_comment', 'level_set')) {
                    $table->integer('level_set')->default(0)->comment('手动设置等级');
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
        Schema::table('yz_comment', function (Blueprint $table) {
            $table->dropColumn('level_set');
        });
    }
}
