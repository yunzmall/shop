<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \Illuminate\Support\Facades\DB;

class CreateImsYzAddressTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        if (!Schema::hasTable('yz_address')) {
            Schema::create('yz_address', function (Blueprint $table) {
                $table->integer('id', true);
                $table->string('areaname')->nullable();
                $table->integer('parentid')->nullable();
                $table->integer('level')->nullable();
            });
        }

        if (Schema::hasTable('yz_address')) {

            $address_file =  base_path() .DIRECTORY_SEPARATOR.'static'.DIRECTORY_SEPARATOR.'source'.DIRECTORY_SEPARATOR.'address.json';
            \Log::debug('--地址文件路径--'.$address_file);
            try {
                DB::beginTransaction();
                \app\common\models\Address::truncate();
                $address_list = array_chunk(json_decode(file_get_contents($address_file), true), 2000);
                foreach ($address_list as $address) {
                    \app\common\models\Address::insert($address);
                }
                
                (new \app\common\services\address\GenerateAddressJs())->address();
            } catch (\Exception $e) {
                \Log::debug('--地址生成错误--', $e->getMessage());
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
		Schema::dropIfExists('yz_address');
	}

}
