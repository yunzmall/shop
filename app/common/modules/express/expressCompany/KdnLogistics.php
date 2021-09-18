<?php
/**
 * Created by PhpStorm.
 * User: shenyang
 * Date: 2018/10/23
 * Time: 下午2:34
 */

namespace app\common\modules\express\expressCompany;

use app\common\exceptions\ShopException;
use app\common\models\Order;
use app\common\modules\express\getTraces;
use Ixudra\Curl\Facades\Curl;

class KdnLogistics implements Logistics
{
    private $eBusinessID;
    private $appKey;
    private $reqURL;
    private $set_data;

    
    public function __construct($data)
    {
        $this->eBusinessID = $data['KDN']['eBusinessID'];
        $this->appKey = $data['KDN']['appKey'];
        $this->reqURL = config('app.express.KDN.reqURL');
        $this->set_data = $data;
    }

    public function getTraces($comCode, $expressSn, $orderSn = '',$order_id = '')
    {
       //快递鸟1002状态为免费，8001状态为收费
        $express_api = $this->set_data;//\Setting::get('shop.express_info');
        if($comCode == 'JD'){
            $requestData = json_encode(
                [
                    'OrderCode' => $orderSn,
                    'CustomerName' => $express_api['KDN']['CustomerName'],
                    'ShipperCode' => $comCode,
                    'LogisticCode' => $expressSn,
                ]
            );
        }elseif ($comCode == 'SF'){
            $mobile = $this->getMobile($order_id);

            $requestData = json_encode(
                [
                    'OrderCode' => $orderSn,
                    'CustomerName' => $mobile,
                    'ShipperCode' => $comCode,
                    'LogisticCode' => $expressSn,
                ]
            );
        }else{
            $requestData = json_encode(
                [
                    'OrderCode' => $orderSn,
                    'ShipperCode' => $comCode,
                    'LogisticCode' => $expressSn,
                ]
            );
        }

        if(empty($express_api['KDN']['express_api'])){//判断如果快递鸟状态为空，默认赋值为1002免费状态
            $express_api['KDN']['express_api'] = 1002;
        }
        if ($express_api['KDN']['express_api'] == 1002 || $express_api['KDN']['express_api'] == 8001 ){//判断如果快递鸟状态为1002或者8001则赋值，不为
            $datas = array(
                'EBusinessID' => $this->eBusinessID,
                'RequestType' => $express_api['KDN']['express_api'],//'1002',//快递鸟1002状态为免费，8001状态为收费
                'RequestData' => urlencode($requestData),
                'DataType' => '2',
            );
        }else{  //不为1002或者8001返回错误
            throw new ShopException("快递鸟状态错误");
        }

        $datas['DataSign'] = $this->encrypt($requestData);

        $response = Curl::to($this->reqURL)->withData($datas)
            ->asJsonResponse(true)->get();
        return $this->format($response);
    }

    private function format($response)
    {
        $result = [];
        foreach ($response['Traces'] as $trace) {
            $result['data'][] = [
                'time' => $trace['AcceptTime'],
                'ftime' => $trace['AcceptTime'],
                'context' => $trace['AcceptStation'],
                'location' => null,
            ];
        }
        $result['state'] = $response['State'];
        return $result;
    }

    private function encrypt($data)
    {
        return urlencode(base64_encode(md5($data . $this->appKey)));
    }

    public function getMobile($order_id)
    {
        if (empty($order_id)){
            throw new ShopException("订单id为空");
        }
        $address = Order::uniacid()->with(['address'])->where('id',$order_id)->first();
        if (empty($address['address']['mobile'])){
            throw new ShopException("订单收货人号码为空");
        }
        $mobile = substr($address['address']['mobile'],-4);
        return $mobile;
    }

}