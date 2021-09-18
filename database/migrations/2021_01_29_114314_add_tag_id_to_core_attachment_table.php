<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTagIdToCoreAttachmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('core_attachment')) {
            Schema::table('core_attachment', function (Blueprint $table) {

                if (!Schema::hasColumn('core_attachment','tag_id')) {
                    $table->integer('tag_id')->nullable()->default(0);
                }
                if (!Schema::hasColumn('core_attachment','timeline')) {
                    $table->string('timeline')->nullable()->default('');
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
