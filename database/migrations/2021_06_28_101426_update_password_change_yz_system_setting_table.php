<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePasswordChangeYzSystemSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		if (config('app.framework') == 'platform') {
			$login_set = \Illuminate\Support\Facades\DB::table('yz_system_setting')->where('key','loginset')->first();
			if (empty($login_set)) {
				// 添加
				$data['password_verify'] = 1;
				$data = serialize($data);
				\Illuminate\Support\Facades\DB::table('yz_system_setting')->insert([
					'key'       	 => 'loginset',
					'value'     	 =>  $data,
					'created_at'     =>  time(),
					'updated_at'     =>  time(),
				]);
			} else {
				$data = unserialize($login_set['value']);
				$data['password_verify'] = "1";
				$data = serialize($data);
				// 修改
				\Illuminate\Support\Facades\DB::table('yz_system_setting')->where('key', 'loginset')->update(['value' => $data]);
			}
			\app\common\helpers\Cache::forget('system_loginset');
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
