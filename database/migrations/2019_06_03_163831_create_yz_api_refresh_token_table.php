<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzApiRefreshTokenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('yz_api_refresh_token')) {
            Schema::create('yz_api_refresh_token', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('uniacid')->default(0)->nullable();
                $table->string('refresh_token')->nullable()->comment('刷新令牌');
                $table->integer('expires_at')->nullable()->comment('有效期至');
                $table->boolean('revoked')->comment('是否撤回');
                $table->timestamps();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE " . app('db')->getTablePrefix() . "yz_api_refresh_token comment 'api--刷新令牌'");//表注释
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_api_refresh_token');
    }
}
