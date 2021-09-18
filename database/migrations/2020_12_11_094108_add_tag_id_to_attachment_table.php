<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTagIdToAttachmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_core_attachment')) {
            Schema::table('yz_core_attachment', function (Blueprint $table) {

                if (!Schema::hasColumn('yz_core_attachment','tag_id')) {
                    $table->integer('tag_id')->nullable()->default(0);
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
