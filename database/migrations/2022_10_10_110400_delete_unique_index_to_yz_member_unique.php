<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeleteUniqueIndexToYzMemberUnique extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('yz_member_unique', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $preFix = Schema::getConnection()->getTablePrefix();
            $indexesFound = $sm->listTableIndexes($preFix . 'yz_member_unique');
            if ($indexesFound) {
                foreach ($indexesFound as $k=>$v) {
                    if ($v->getColumns() && count($v->getColumns()) == 1 && $v->getColumns()[0] === 'unionid') {
                        $table->dropIndex($k);
                    }
                }
            }
        });
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
