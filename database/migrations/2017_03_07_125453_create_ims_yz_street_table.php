<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;


class CreateImsYzStreetTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_street')) {
            Schema::create('yz_street', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('areaname')->nullable();
                $table->integer('parentid')->nullable();
                $table->integer('level')->nullable();
            });
        }


        if (Schema::hasTable('yz_street')) {
            $street_file = base_path() . DIRECTORY_SEPARATOR . 'static'.DIRECTORY_SEPARATOR.'source'.DIRECTORY_SEPARATOR.'street.json';
            \Log::debug('--街道地址文件路径--'.$street_file);
            try {
                DB::beginTransaction();
                \app\common\models\Street::truncate();
                $street_list = array_chunk(json_decode(file_get_contents($street_file), true), 2000);
                foreach ($street_list as $street) {
                    \app\common\models\Street::insert($street);
                }
            } catch (\Exception $e) {
                \Log::debug('--街道地址生成错误--', $e->getMessage());
            }
        }
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('yz_street');
	}

}
