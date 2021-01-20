<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateYzAddressDelAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_address')) {


            $del_area = ['city_id' => 330200, 'city_name' => '宁波市', 'area_name' => '江东区'];

            //要删除的区
            $del = \app\common\models\Address::where(['areaname' => $del_area['area_name'], 'parentid' =>  $del_area['city_id'], 'level' => 3])->first();
            if ($del) {
                $del->delete();
                $memberAddressModel = app(\app\frontend\repositories\MemberAddressRepository::class)->model();
                $memberAddressModel::where('city', '宁波市')->where('district', '江东区')->delete();
                (new \app\common\services\address\GenerateAddressJs())->address();
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
        //
    }
}
