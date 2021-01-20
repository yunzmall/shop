<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzProtocolTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_protocol')) {
            Schema::create('yz_protocol', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->comment('公众号');
                $table->boolean('status')->default(0)->comment('0关闭1开启');
                $table->longText('content')->nullable()->comment('内容');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
            });
        }

        $member_protocol = \app\common\models\Setting::where(['key' => 'protocol', 'group' => 'shop'])->select('uniacid', 'value')->get()->toArray();
        foreach ($member_protocol as $k => $value){
            $data[$k]['status'] = unserialize($value['value'])['protocol'] ?: 0;
            $data[$k]['content'] = unserialize($value['value'])['content'] ?: '';
            $data[$k]['uniacid'] = $value['uniacid'];
            $data[$k]['created_at'] = time();
            $data[$k]['updated_at'] = time();
        }
        if(!empty($data)){
            \Illuminate\Support\Facades\DB::table('yz_protocol')->insert($data);
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
