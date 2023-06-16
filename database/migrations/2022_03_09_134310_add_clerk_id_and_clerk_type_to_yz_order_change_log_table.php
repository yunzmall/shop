<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClerkIdAndClerkTypeToYzOrderChangeLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('yz_order_change_log', function (Blueprint $table) {
            if (!Schema::hasColumn('yz_order_change_log', 'clerk_type')) {
                $table->string('clerk_type')->default('')->comment('核销员来源');
            }
            if (!Schema::hasColumn('yz_order_change_log', 'clerk_id')) {
                $table->integer('clerk_id')->default(0)->comment('核销员ID');
            }
        });
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
