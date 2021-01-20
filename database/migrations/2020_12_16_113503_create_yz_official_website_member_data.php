<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzOfficialWebsiteMemberData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('yz_official_website_member_data')) {
            Schema::create('yz_official_website_member_data',function (Blueprint $table){
                $table->increments('id')->comment('主键id');
                $table->string('nickname')->default(0)->comment('姓名');
                $table->string('mobile')->nullable()->comment('手机');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
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
        Schema::dropIfExists('yz_official_website_member_data');
    }
}
