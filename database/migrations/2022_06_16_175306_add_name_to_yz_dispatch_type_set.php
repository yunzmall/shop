<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNameToYzDispatchTypeSet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_dispatch_type_set')) {
            Schema::table('yz_dispatch_type_set', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_dispatch_type_set', 'name')) {
                    $table->string('name')->nullable()->comment('配送名称');
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
        Schema::table('yz_dispatch_type_set', function (Blueprint $table) {
            //
        });
    }
}
