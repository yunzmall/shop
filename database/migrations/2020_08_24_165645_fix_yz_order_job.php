<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class FixYzOrderJob extends Migration
{
    use \Illuminate\Foundation\Bus\DispatchesJobs;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $order_pays = \app\common\models\OrderCreatedJob::where('status','waiting')->where('created_at','>',1596211200)->get();
        foreach ($order_pays as $order_pay) {
            $this->dispatch(new \app\Jobs\OrderCreatedEventQueueJob($order_pay->order_id));
        }

        $order_pays = \app\common\models\OrderPaidJob::where('status','waiting')->where('created_at','>',1596211200)->get();
        foreach ($order_pays as $order_pay) {
            $this->dispatch(new \app\Jobs\OrderPaidEventQueueJob($order_pay->order_id));
        }

        $order_pays = \app\common\models\OrderSentJob::where('status','waiting')->where('created_at','>',1596211200)->get();
        foreach ($order_pays as $order_pay) {
            $this->dispatch(new \app\Jobs\OrderSentEventQueueJob($order_pay->order_id));
        }

        $order_pays = \app\common\models\OrderReceivedJob::where('status','waiting')->where('created_at','>',1596211200)->get();
        foreach ($order_pays as $order_pay) {
            $this->dispatch(new \app\Jobs\OrderReceivedEventQueueJob($order_pay->order_id));
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
