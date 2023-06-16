<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/6/15
 * Time: 15:00
 */

namespace app\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class deleteUniacidColumnsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $table_name;
    protected $uniacid;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($table_name, $uniacid)
    {
        $this->table_name = $table_name;
        $this->uniacid = $uniacid;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::select("DELETE FROM `{$this->table_name}` WHERE `uniacid` = {$this->uniacid}");
    }
}
