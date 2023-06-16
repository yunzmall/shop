<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRootUsernameToYzWebsiteInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_website_info')) {
            Schema::table('yz_website_info', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_website_info', 'root_username')) {
                    $table->string('root_username')->default('')->comment('服务器账号');
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
    }
}
