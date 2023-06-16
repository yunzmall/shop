<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateYzCategoryDiscountTable20211229 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_category_discount')) {
            Schema::table('yz_category_discount', function (Blueprint $table) {
                if (Schema::hasColumn('yz_category_discount', 'category_ids')) {
                    $table->text('category_ids')->change();
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
        //
    }
}
