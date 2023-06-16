<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLngLatToOrderAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		if (Schema::hasTable('yz_order_address')) {
			Schema::table('yz_order_address', function (Blueprint $table) {

				if (!Schema::hasColumn('yz_order_address', 'longitude')) {
					$table->string('longitude', 15)->default('')->comment('经度');
				}
				if (!Schema::hasColumn('yz_order_address', 'latitude')) {
					$table->string('latitude', 15)->default('')->comment('纬度');
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
        Schema::table('order_address', function (Blueprint $table) {
            //
        });
    }
}
