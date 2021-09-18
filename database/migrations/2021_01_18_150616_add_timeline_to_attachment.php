<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimelineToAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (Schema::hasTable('yz_core_attachment')) {
            Schema::table('yz_core_attachment', function (Blueprint $table) {

                if (!Schema::hasColumn('yz_core_attachment','timeline')) {
                    $table->string('timeline')->nullable()->default('')->comment('时长');
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
