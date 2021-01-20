
<?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

class AddRelationIdToPointLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_point_log')) {
            Schema::table('yz_point_log', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_point_log', 'relation_id')) {
                    $table->integer('relation_id')->nullable();
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
