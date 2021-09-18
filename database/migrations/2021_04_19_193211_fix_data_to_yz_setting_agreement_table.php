<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixDataToYzSettingAgreementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		$data = \Illuminate\Support\Facades\DB::table('yz_setting')->where(['group'=>'shop','key'=>'shop'])->get();
   		foreach ($data as $key => $value) {
   		    $set_arr = unserialize($value['value']);
   		    if (!isset($set_arr['agreement']) || empty($set_arr['agreement'])) {
   		        continue;
            }
   		    $data = [
   		        'uniacid' => $value['uniacid'],
   		        'group' => 'shop',
   		        'key' => 'agreement',
   		        'value' => $set_arr['agreement'],
                'created_at' => time(),
            ];
            \Illuminate\Support\Facades\DB::table('yz_rich_text')->insert($data);
            unset($set_arr['agreement']);
            \Illuminate\Support\Facades\DB::table('yz_setting')->where(['uniacid'=>$value['uniacid'],'group'=>'shop','key'=>'shop'])->update(['value'=>serialize($set_arr)]);
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
