<?php
/**
 * Author:  
 * Date: 2017/11/21
 * Time: 下午4:01
 */

namespace app\backend\modules\order\controllers;


use app\backend\modules\member\models\Member;
use app\backend\modules\order\models\Order;
use app\common\components\BaseController;
use app\common\exceptions\ShopException;
use app\common\helpers\Url;
use app\common\models\order\Express;
use app\common\repositories\ExpressCompany;
use app\frontend\models\OrderGoods;

class BatchSendController extends BaseController
{
    private $originalName;
    private $reader;
    private $success_num = 0;
    private $err_array = [];
    private $error_msg;
    private $uid =array();

    public function preAction()
    {
        parent::preAction();
        // 生成目录
        if (!is_dir(storage_path('app/public/orderexcel'))) {
            mkdir(storage_path('app/public/orderexcel'), 0777);
        }
    }

    public function index()
    {
//        $send_data = request()->send;
        $send_data = [];
        $express = json_decode(request()->body);

        $send_data['express_code'] = $express->value;
        $send_data['express_company_name'] = $express->name;
        $send_data['excelfile'] = request()->file;
        if (\Request::isMethod('post')) {
            if ($send_data['express_company_name'] == "顺丰" && $send_data['express_code'] != "SF") {
                return $this->errorJson('上传失败，请重新上传');
            }

            if (!$send_data['excelfile']) {
                return $this->errorJson('请上传文件');
            }

            if ($send_data['excelfile']->isValid()) {
                try {
                    $this->uploadExcel($send_data['excelfile']);
                } catch (ShopException $exception) {
                    return $this->errorJson($exception->getMessage());
                }
//                $this->readExcel();
                $this->handleOrders($this->getRow(), $send_data);
                $this->sendMessage($this->uid);
                $msg = $this->success_num . '个订单发货成功。';
                return $this->successJson('ok',$msg . $this->error_msg);
//                return $this->message($msg . $this->error_msg, Url::absoluteWeb('order.batch-send.index'));
            }
        }

        return view('order.batch_send_vue', [])->render();
    }

    protected $importData;

    /**
     * @name 保存excel文件
     * @param $file
     * @throws ShopException
     * @author
     */
    private function uploadExcel($file)
    {
        $originalName = $file->getClientOriginalName(); // 文件原名
        $ext = $file->getClientOriginalExtension();     // 扩展名
        $realPath = $file->getRealPath();   //临时文件的绝对路径
        if (!in_array($ext, ['xls', 'xlsx','csv'])) {
            throw new ShopException('不是xls、xlsx文件格式！');
        }


        $this->importData = \app\exports\ExcelService::importToArray($file);

//        $newOriginalName = md5($originalName . str_random(6)) .'.'. $ext;
//        \Storage::disk('orderexcel')->put($newOriginalName, file_get_contents($realPath));
//        $this->originalName = $newOriginalName;
    }


    /**
     * 读取文件
     * @author
     */
    private function readExcel()
    {
        //$this->reader = \Excel::load(storage_path('app/public/orderexcel') . '/' . $this->originalName);
    }

    /**
     * @name 获取表格内容
     * @return array
     * @author
     */
    private function getRow()
    {

        $values = $this->importData[0];
        array_shift($values); // 删除标题

        return $values?:[];


        $values = [];
        $sheet = $this->reader->getActiveSheet();
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnCount = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $row = 2;
        while ($row <= $highestRow) {
            $rowValue = array();
            $col = 0;
            while ($col < $highestColumnCount) {
                $rowValue[] = (string)$sheet->getCellByColumnAndRow($col, $row)->getValue();
                ++$col;
            }
            $values[] = $rowValue;
            ++$row;
        }
        return $values;
    }

    /**
     * @name 订单发货
     * @param $values
     * @param $send_data
     * @author
     */
    private function handleOrders($values, $send_data)
    {
        foreach ($values as $rownum => $col) {
            $order_sn = trim($col[0]);
            $express_sn = trim($col[1]);
            if (empty($order_sn)) {
                continue;
            }
            if (empty($express_sn)) {
                $this->err_array[] = $order_sn;
                continue;
            }
            if ($order_sn == $express_sn) {
                $this->err_array[] = '发货失败,订单号为' . $order_sn . '快递单号不能与订单编号一致';
//                $this->err_array[] = $order_sn;
                continue;
            }
//            $order = Order::select('id', 'order_sn', 'status', 'refund_id','uid')->whereStatus(1)->whereOrderSn($order_sn)->first();
            $order = Order::select('id', 'order_sn', 'status', 'refund_id','uid','is_all_send_goods')->whereIn('status',[1,2])->whereOrderSn($order_sn)->first();


            //判断如果没有查询到  或者 查询到了 订单状态为已发货 订单状态不为部分发货
            if (!$order || ($order['status'] == 2 && $order->is_all_send_goods != 1)) {
                $this->err_array[] = $order_sn;
                continue;
            }
            //如果是部分发货则走新逻辑 -- 如果是未发货状态则走以前的不变
            if($order['status'] == 2){
                //存储新的物流信息
                $db_express_model = new Express();
                $db_express_model->order_id = $order->id;
                $db_express_model->express_code = $send_data['express_code'];
                $db_express_model->express_company_name = $send_data['express_company_name'];
                $db_express_model->express_sn = $express_sn;
                $db_express_model->save();
                //修改订单商品状态
                $where[] = ['order_id','=',$order->id];
                $where[] = ['order_express_id','=',null];
                $param['order_goods_ids'] = OrderGoods::where($where)->update(['order_express_id'=>$db_express_model->id]);
                //修改订单表是否全部发货 为全部发货
                $order->is_all_send_goods = 2;
            }else {
                $express_model = Express::where('order_id', $order->id)->first();
                !$express_model && $express_model = new Express();
                $express_model->order_id = $order->id;
                $express_model->express_company_name = $send_data['express_company_name'];
                $express_model->express_code = $send_data['express_code'];
                $express_model->express_sn = $express_sn;
                $express_model->save();
            }
            $order->send_time = time();
            $order->status = 2;
            $this->uid[] = $order->uid;
            $order->save();
            $order->fireSentEvent();
            $this->success_num += 1;
        }
        $this->setErrorMsg();
    }

    /**
     * @name 设置错误信息
     * @author
     */
    private function setErrorMsg()
    {
        if (count($this->err_array) > 0) {
            $num = 1;
            $this->error_msg = '<br>' . count($this->err_array) . '个订单发货失败,失败的订单编号: <br>';
            foreach ($this->err_array as $k => $v) {
                $this->error_msg .= $v . ' ';
                if (($num % 2) == 0) {
                    $this->error_msg .= '<br>';
                }
                ++$num;
            }
        }
    }

    /**
     * @name 获取示例excel
     * @author
     */
    public function getExample()
    {
        $export_data[0] = ["订单编号", "快递单号"];


        $file_name = date('Y-m-d-h-i-s', time()) . "批量发货数据模板.xls";


        return  \app\exports\ExcelService::fromArrayExport($export_data, $file_name);

        // 商城更新，无法使用
//        \Excel::create('批量发货数据模板', function ($excel) use ($export_data) {
//            $excel->setTitle('Office 2005 XLSX Document');
//            $excel->setCreator('芸众商城')
//                ->setLastModifiedBy("芸众商城")
//                ->setSubject("Office 2005 XLSX Test Document")
//                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
//                ->setKeywords("office 2005 openxml php")
//                ->setCategory("report file");
//            $excel->sheet('info', function ($sheet) use ($export_data) {
//                $sheet->rows($export_data);
//            });
//        })->download('csv');//->export('xls');
    }

    private function sendMessage($uid)
    {
        try {
            \Log::debug('批量发货短信');

            //sms_send 是否开启
            $smsSet = \Setting::get('shop.sms');
            //是否设置
            if ($smsSet['type'] != 3 || empty($smsSet['aly_templateBalanceCode'])) {
                return false;
            }
            //查询余额,获取余额超过该值的用户，并把没有手机号的筛选掉
            $mobile = Member::uniacid()
                ->WhereIn('uid',$uid)
                ->select('uid', 'mobile')
                ->whereNotNull('mobile')
                ->get();

            if (empty($mobile)) {
                \Log::debug('未找到满足条件会员');
                return false;
            } else {
                $mobile = $mobile->toArray();
            }

            $name = \Setting::get('shop.shop')['name'];

            foreach ($mobile as $key => $value) {
                if (!$value['mobile']) {
                    continue;
                }
                $data =  Array(  // 短信模板中字段的值
                    "shop" => $name,
                );
                //todo 发送短信
                app('sms')->sendGoods($value['mobile'], $data);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    public function getExpress()
    {
        $data = \app\common\repositories\ExpressCompany::create()->all();

        return $this->successJson('ok',$data);
    }
}