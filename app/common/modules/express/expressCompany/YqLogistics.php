<?php
namespace app\common\modules\express\expressCompany;

use app\common\exceptions\AppException;
use app\common\components\ApiController;
use app\common\models\Order;
use Yunshop\WorkWechat\common\service\WorkWechatService;
use Yunshop\YunSignApi\common\models\Apps;
use Yunshop\YunSignApi\common\models\AccessToken;
use Yunshop\YunqianApi\common\service\WechatOcrService;
use Yunshop\YunqianApi\common\service\ExpressService;
use app\common\modules\express\getTraces;


class YqLogistics implements Logistics
{
    private $app_id;
    private  $app_secret;
    //正式环境
    private $reqURL ='https://www.yunqiankeji.com/addons/yun_shop/api.php?i=1&uuid=0&type=5&route=plugin.yunqian-api.api.Express.getInfo';
//    private $reqURL ='https://dev1.yunzmall.com/addons/yun_shop/api.php?i=2&uuid=0&type=5&route=plugin.yunqian-api.api.Express.getInfo';   //测试地址


    public function __construct($data)
    {
	    $server_name = $_SERVER['SERVER_NAME'];
	    if (strexists($server_name, 'dev')) {
		    //dev环境用本地
		    $this->reqURL = 'https://dev1.yunzmall.com/addons/yun_shop/api.php?i=2&uuid=0&type=5&route=plugin.yunqian-api.api.Express.getInfo';
	    }
        $this->app_id = trim($data['YQ']['appId']);
        $this->app_secret = trim($data['YQ']['appSecret']);
    }

    public function getTraces($comCode, $expressSn, $orderSn,$order_id){
        $pa = json_encode([
            'number'=> $expressSn,
            'company_code'=> $comCode,
        ]);
        if ($comCode == 'SF'){
            $mobile = $this->getMobile($order_id);
            $pa = json_encode([
                'number'=> trim($expressSn).':'.trim($mobile),
                'company_code'=> $comCode,
            ]);
        }
        $array = $this->http_post_data($pa, $this->generateHeader(),$this->reqURL);
        $json_string = $array[1];
        $res = json_decode($json_string,true);
        $result = $this->format($res['data']);
        $result['data'] = array_reverse($result['data']);

        return $result;
    }



    private function generateHeader(){

        $millisecond = $this->getMillisecond();//为服务端时间前后5分钟内可以访问
        $nonceStr = $this->getNonceStr(16);//10到50位字符串
        $header=[
            'appId:'.$this->app_id,
            'signature:'.$this->getSignature($this->app_id,$this->app_secret,$nonceStr,$millisecond),
            'nonceStr:'.$nonceStr,
            'timestamp:'.$millisecond,
            'Content-Type:application/json',
        ];
        return $header;
    }
    //计算请求签名值
    private function getSignature($appId, $appSecret,$nonceStr,$millisecond)
    {
        $s = $appId.'_'.$appSecret.'_'.$nonceStr.'_'.$millisecond;
        $signature = strtoupper(md5($s));

        return $signature;
    }
    //模拟发送POST请求
    /**
     * 模拟发送POST 方式请求
     * @param $url
     * @param $data
     * @param $projectId
     * @param $signature
     * @return array
     */
    private function http_post_data( $data, $header,$url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        ob_start();
        curl_exec($ch);
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //echo curl_errno($ch);
        return array($return_code, $return_content);
    }
    public function curl_post($postdata = '', $header = '', $url,$options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    /**
     *
     * 产生随机字符串，不长于50位
     * @param int $length
     * @return 产生的随机字符串
     */
    private function getNonceStr($length = 50)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
    //获取当前时间戳（毫秒级）
    private function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());

        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    private function format($response)
    {
        $result = [];
        foreach ($response['list'] as $trace) {
            $result['data'][] = [
                'time' => $trace['time'],
                'ftime' => $trace['time'],
                'context' => $trace['status'],
                'location' => null,
            ];
        }
        $result['state'] = $response['deliverystatus'];
        return $result;
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
