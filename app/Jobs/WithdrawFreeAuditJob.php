<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/6/15 下午2:33
 * Email: livsyitian@163.com
 */

namespace app\Jobs;



use app\frontend\modules\withdraw\models\Withdraw;
use app\frontend\modules\withdraw\services\AutomateAuditService;
use app\host\HostManager;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class WithdrawFreeAuditJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Withdraw
     */
    private $withdrawModel;


    public function __construct(Withdraw $withdrawModel)
    {
        $hostCount = count((new HostManager())->hosts() ?: []) ? : 1;
        $this->queue = 'limit:'.($withdrawModel->member_id % (3 * $hostCount));
        $this->withdrawModel = $withdrawModel;
    }


    public function handle()
    {
        $automateAuditService = new AutomateAuditService($this->withdrawModel);
		try {
			$automateAuditService->freeAudit();
		} catch (\Exception $e) {
			\Log::debug('提现审核',[$e->getMessage()]);
		}
    }

}