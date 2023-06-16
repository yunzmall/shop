<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUniqueIndexToYzMemberUnique extends Migration
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
                        if (!array_key_exists("uniacid_unionid_unique", $indexesFound)) {
                            $table->unique(['uniacid', 'unionid'], 'uniacid_unionid_unique');
                        }
                    }
                }
            }
        });
//        Schema::table('yz_member_unique', function (Blueprint $table) {
//            $sm = Schema::getConnection()->getDoctrineSchemaManager();
//            $preFix = Schema::getConnection()->getTablePrefix();
//            $indexesFound = $sm->listTableIndexes($preFix . 'yz_member_unique');
//
//            if (array_key_exists("idx_unionid", $indexesFound)) {
//                $table->dropIndex('idx_unionid');
//            }
//            if (!array_key_exists("uniacid_unionid_unique", $indexesFound)) {
//                $table->unique(['uniacid', 'unionid'], 'uniacid_unionid_unique');
//            }
//        });
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
