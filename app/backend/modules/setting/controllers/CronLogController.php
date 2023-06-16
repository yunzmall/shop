<?php


namespace app\backend\modules\setting\controllers;


use app\common\components\BaseController;
use app\framework\Http\Request;
use Illuminate\Support\Facades\DB;

class CronLogController extends BaseController
{
    public function index()
	{
		if (request()->ajax()) {
			$list = DB::table(config('queue.failed.table'))->orderBy('id','desc')->paginate(10)->toArray();
			foreach ($list['data'] as $key=>&$queue) {
				$queue['queue_id'] = json_decode($queue['payload'],true)['id'];
			}

			return $this->successJson('',$list);
		}
        return view('setting.shop.cron_log',[])->render();
    }

    public function submit(Request $request)
	{
		$type = $request->input('type');
		$id = $request->input('id');
		if ($type == 2) {
			DB::table(config('queue.failed.table'))->delete($id);
			return $this->successJson('操作成功');
		}
		if ($id === 0) {
			\Artisan::call("queue:retry all");
		} else {
			\Artisan::call("queue:retry " . $id);
		}
		return $this->successJson('操作成功');
	}


}