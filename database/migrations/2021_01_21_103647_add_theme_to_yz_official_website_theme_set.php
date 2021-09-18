<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThemeToYzOfficialWebsiteThemeSet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_official_website_theme_set')) {
            $data = [
                "id"=>15,
                "title" => "商城PC端",
                "is_default" => 0,
                "basic" => '{"cus_url":"","cus_link":"","cus_mobile":"","cus_uniacid":1}',
                "top" => '',
                "tail" => '',
                "identification"=>"uniacid_theme",
                "created_at" => time(),
                "updated_at" => time()
            ];
            \Illuminate\Support\Facades\DB::table('yz_official_website_theme_set')->insert($data);

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
