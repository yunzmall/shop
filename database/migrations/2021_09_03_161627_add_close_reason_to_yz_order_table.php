<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCloseReasonToYzOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if(Schema::hasTable('yz_order')){
            Schema::table('yz_order', function (Blueprint $table) {
                if(!Schema::hasColumn('yz_order','close_reason')){
                    $table->text('close_reason')->nullable()->comment('关闭原因');
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
        Schema::table('yz_order', function (Blueprint $table) {

        });
    }
}
