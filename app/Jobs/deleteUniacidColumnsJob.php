<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
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
