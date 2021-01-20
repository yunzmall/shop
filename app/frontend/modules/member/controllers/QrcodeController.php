<?php

namespace app\frontend\modules\member\controllers;

use app\common\components\ApiController;
use app\common\facades\Setting;
use app\common\helpers\ImageHelper;
use app\common\services\Utils;
use app\frontend\models\Member;
use app\frontend\modules\member\models\MemberModel;
use Yunshop\NewPoster\services\CreateCode;
use Yunshop\NewPoster\services\MergePoster;
use Yunshop\Poster\models\Poster;
use Yunshop\Poster\models\PosterRecord;
use Yunshop\Poster\services\CreatePosterService;
use Yunshop\Poster\models\PosterQrcode;
use Yunshop\Poster\models\Qrcode;
use GuzzleHttp\Client;
use app\frontend\modules\member\controllers\PosterController;

class QrcodeController extends ApiController
{
    protected $type;
    protected $host;
    protected $posterModel;
    protected $memberModel;


    const WE_CHAT_SHOW_QR_CODE_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';

    /**
     * 会员中心推广二维码(包含会员是否有生成海报权限)
     *
     * @param $isAgent
     *
     * @return string
     */
    public function getPoster()
    {
        $this->type = request()->type == 2?2:1;
        $this->host = $host = request()->getSchemeAndHttpHost();

        $this->memberModel = $memberModel = Member::uniacid()
            ->select('uid', 'avatar', 'nickname')
            ->with('yzMember')
            ->ofUid(\YunShop::app()->getMemberId())
            ->first();

        if (\YunShop::plugin()->get('new-poster')) {
            $this->type = request()->type;
            $this->posterModel = $posterModel = \Yunshop\NewPoster\models\Poster::uniacid()
                ->where(['center_show' => 1, 'poster_type' => $this->type])
                ->first();

            if (!$this->posterModel) {
                //默认二维码
                if ($this->createPoster()) {
                    return $this->successJson('ok', ['image_url' => $this->createPoster(),'center_show'=> '0']);
                } else {
                    return $this->errorJson('生成二维码失败');
                }
            }

            $posterRecord = \Yunshop\NewPoster\models\PosterRecord::where([
                'member_id' => \YunShop::app()->getMemberId(),
                'poster_type' => $this->type,
                'poster_id' => $this->posterModel
            ])->first();

            if ($posterRecord) {
                return $this->successJson('ok', [
                    'image_url' => $posterRecord->url,
                    'center_show'=> '0',
                ]);
            }

            if ($posterModel->is_ago == 0) {
                return $this->successJson('ok', ['image_url' => '','center_show'=> '2']);
            }

            if ($posterModel->is_open || ($posterModel && !$posterModel->is_open && Member::current()->yzMember->is_agent)) {
                $poster_style = json_decode($posterModel['style_data'], true);
                foreach ($poster_style as &$item) {
                    $item = $this->getRealParams($item);
                    switch ($item['type']) {
                        case 'head' :
                            $item['src'] = ImageHelper::fix_wechatAvatar($memberModel->avatar_image);
                            break;
                        case 'nickname' :
                            $item['src'] = $memberModel->nickname;
                            break;
                        case 'qr' :
                            $item['src'] = $this->qr($item);
                            break;
                        case 'img' :
                            $item['src'] = yz_tomedia($item['src']);
                            break;
                    }
                }

                $posterModel['style_data'] = $poster_style;
                $posterModel['background'] = yz_tomedia($posterModel['background']);
                $posterModel['new'] = true;

                return $this->successJson('ok', $posterModel);
            }
        }

        if (\YunShop::plugin()->get('poster') && \Schema::hasColumn('yz_poster', 'center_show')) 
        {
            $posterModel = Poster::uniacid()
            ->where('center_show', 1)
            ->first();

            //判断是否由后台生成海报 0后台生成海报  1前端生成
            if ($posterModel->is_ago == 0) 
            {
                return $this->successJson('ok', ['image_url' => '','center_show'=> '2']);
            }

            if ($posterModel->is_open || ($posterModel && !$posterModel->is_open && Member::current()->yzMember->is_agent)) 
            {
                $poster_info = $posterModel->toArray();
                $params = json_decode($poster_info['style_data'], true);
                foreach ($params as $key=>$item) 
                {
                    $item = $this->getRealParams($item);
                    switch ($item['type']) 
                    {
                        case 'head':
                            $item['src'] = ImageHelper::fix_wechatAvatar($memberModel->avatar_image);
                            break;
                        case 'qr_shop':
                            $item['src'] = $host . yzAppUrl('home', ['mid' => $this->memberModel->uid]);
                            break;
                        case 'qr_app_share':
                            $item['src'] = $host . yzAppUrl('member/scaneditmobile', ['mid' => $this->memberModel->uid]);
                            break;
                        case 'nickname':
                            $item['src'] = $memberModel->nickname;
                            break;
                    }
                    $params[$key] = $item;
                }
                $poster_info['style_data'] = $params;
                $poster_info['new'] = false;
                if ($poster_info)
                {
                    return $this->successJson('ok', $poster_info);
                }
            }
        }

        //默认二维码
        if ($this->createPoster()) {
            return $this->successJson('ok', ['image_url' => $this->createPoster(),'center_show'=> '0']);
        } else {
            return $this->errorJson('生成二维码失败');
        }
    }

    /**
     * 海报记录接口
     */
    public function posterRecord()
    {
        $imageUrl  = \YunShop::request()->image;
        $poster_id = \YunShop::request()->poster_id;
        $type = \YunShop::request()->type;

        if (!$imageUrl || !$poster_id)
        {
            return $this->errorJson('缺少参数');
        }

        if (\YunShop::plugin()->get('new-poster')) {
            if (\Yunshop\NewPoster\models\PosterRecord::where('url',$imageUrl)->get()->isEmpty()) {
                $model = new \Yunshop\NewPoster\models\PosterRecord();
                $model->url = $imageUrl;
                $model->poster_type = $type;
                $model->poster_id =  $poster_id;
                $model->member_id =\YunShop::app()->getMemberId();
                $model->created_at = time();
                $model->save();
            }
        } else {
            if (!\YunShop::plugin()->get('poster')) {
                return $this->errorJson('海报插件未开启');
            }

            $posterRecord = new PosterRecord();
            if($posterRecord::where('url',$imageUrl)->get()->isEmpty())
            {
                $posterRecord->url = $imageUrl;
                $posterRecord->poster_id =  $poster_id;
                $posterRecord->member_id =\YunShop::app()->getMemberId();
                $posterRecord->created_at = time();
                $posterRecord->save();
            }
        }

        return $this->successJson('成功');
    }

    private function createPoster()
    {
        $width = 320;
        $height = 540;

        $logo_width = 40;
        $logo_height = 40;

        $font_size = 15;
        $font_size_show = 20;

        $member_id = \YunShop::app()->getMemberId();

        $shopInfo = Setting::get('shop.shop');
        $shopName = $shopInfo['name'] ?: '商城'; //todo 默认值需要更新
        $shopLogo = $shopInfo['logo'] ? replace_yunshop(yz_tomedia($shopInfo['logo'])) : base_path() . '/static/images/logo.png'; //todo 默认值需要更新
        $shopImg = $shopInfo['signimg'] ? replace_yunshop(yz_tomedia($shopInfo['signimg'])) : base_path() . '/static/images/photo-mr.jpg'; //todo 默认值需要更新

        $str_lenght = $logo_width + $font_size_show * mb_strlen($shopName);

        $space = ($width - $str_lenght) / 2;

        $uniacid = \YunShop::app()->uniacid;
        $path = storage_path('app/public/personalposter/' . $uniacid);

        Utils::mkdirs($path);

        $md5 = md5($member_id . $shopInfo['name'] . $shopInfo['logo'] . $shopInfo['signimg'] . $this->type . '2'); //用于标识组成元素是否有变化
        $extend = '.png';
        $file = $md5 . $extend;

        if (!file_exists($path . '/' . $file)) {
            $targetImg = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($targetImg, 255, 255, 255);
            imagefill($targetImg, 0, 0, $white);

            $imgSource = imagecreatefromstring(\Curl::to($shopImg)->get());
            $logoSource = imagecreatefromstring(\Curl::to($shopLogo)->get());

            if (2 == $this->type and request()->input('ingress') == 'weChatApplet') {
                $qrcode = MemberModel::getWxacode();
                $qrSource = imagecreatefromstring(\Curl::to($qrcode)->get());
            } else {
                $qrcode = MemberModel::getAgentQR();
                $qrSource = imagecreatefromstring(\Curl::to($qrcode)->get());
            }

            $fingerPrintImg = imagecreatefromstring(file_get_contents($this->getImgUrl('ewm.png')));
            $mergeData = [
                'dst_left' => $space,
                'dst_top' => 10,
                'dst_width' => $logo_width,
                'dst_height' => $logo_height,
            ];
            self::mergeImage($targetImg, $logoSource, $mergeData); //合并商城logo图片
            $mergeData = [
                'size' => $font_size,
                'left' => $space + $logo_width + 10,
                'top' => 37,
            ];
            self::mergeText($targetImg, $shopName, $mergeData);//合并商城名称(文字)
            $mergeData = [
                'dst_left' => 0,
                'dst_top' => 60,
                'dst_width' => 320,
                'dst_height' => 320,
            ];
            self::mergeImage($targetImg, $imgSource, $mergeData); //合并商城海报图片
            $mergeData = [
                'dst_left' => 0,
                'dst_top' => 380,
                'dst_width' => 160,
                'dst_height' => 160,
            ];
            self::mergeImage($targetImg, $fingerPrintImg, $mergeData); //合并指纹图片
            if ($this->type == 2) {
                $mergeData = [
                    'dst_left' => 180,
                    'dst_top' => 390,
                    'dst_width' => 120,
                    'dst_height' => 120,
                ];
            } else {
                $mergeData = [
                    'dst_left' => 160,
                    'dst_top' => 380,
                    'dst_width' => 160,
                    'dst_height' => 160,
                ];
            }
            self::mergeImage($targetImg, $qrSource, $mergeData); //合并二维码图片

            header("Content-Type: image/png");
            $imgPath = $path . "/" . $file;
            imagepng($targetImg, $imgPath);
        }

        $file = $path . '/' . $file;

        $imgUrl = ImageHelper::getImageUrl($file);
        return $imgUrl;
    }

    //合并图片并指定图片大小
    private static function mergeImage($destinationImg, $sourceImg, $data)
    {
        $w = imagesx($sourceImg);
        $h = imagesy($sourceImg);
        imagecopyresized($destinationImg, $sourceImg, $data['dst_left'], $data['dst_top'], 0, 0, $data['dst_width'],
            $data['dst_height'], $w, $h);
        imagedestroy($sourceImg);
        return $destinationImg;
    }

    //合并字符串
    private static function mergeText($destinationImg, $text, $data)
    {
        putenv('GDFONTPATH=' . base_path('static/fonts'));
        $font = "source_han_sans";

        $black = imagecolorallocate($destinationImg, 0, 0, 0);
        imagettftext($destinationImg, $data['size'], 0, $data['left'], $data['top'], $black, $font, $text);
        return $destinationImg;
    }

    private function getImgUrl($file){
        if (config('app.framework') == 'platform') {
            return request()->getSchemeAndHttpHost().'/addons/yun_shop/static/app/images/'.$file;
        } else {
            return base_path() . '/static/app/images/'.$file;
        }
    }

    private function getRealParams($params)
    {
        $params['left'] = intval(str_replace('px', '', $params['left'])) * 2;
        $params['top'] = intval(str_replace('px', '', $params['top'])) * 2;
        $params['width'] = intval(str_replace('px', '', $params['width'])) * 2;
        $params['height'] = intval(str_replace('px', '', $params['height'])) * 2;
        $params['size'] = intval(str_replace('px', '', $params['size'])) * 2;
        $params['src'] = yz_tomedia($params['src']);
        return $params;
    }

    //生成小程序二维码
    private function getWxacode()
    {
        $token = $this->getToken();

        if ($token === false) {return false;}

        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$token;
        $json_data = [
            "scene" => 'mid='.$this->memberModel->uid,
            "page"  => 'pages/index/index'
        ];
        $client = new Client;
        $res = $client->request('POST', $url, ['json'=>$json_data]);
        $data = json_decode($res->getBody()->getContents(), JSON_FORCE_OBJECT);

        //$path_file = $this->getPosterPath().'ceshi.png';
        //file_put_contents($path_file, $data);

        if (isset($data['errcode'])) {
            \Log::debug('===生成小程序二维码获取失败====='. self::class, $data);
            return false;
        }

        $php_base64 = 'data:image/png;base64,'.base64_encode($res->getBody());
        $localdir = $this->memberModel->uid."/". date('Y/m/d');
        if (config('app.framework') == 'platform') {
            $save_dir = '/static/app/images/'.$localdir;
        } else {
            $save_dir = '/addons/yun_shop/static/app/images/'.$localdir;
        }

        $php_base64 = $this->base64_image_content($php_base64,$save_dir);

        return $php_base64;
    }

    //发送获取token请求,获取token(有效期2小时)
    public function getToken()
    {
        $set = \Setting::get('plugin.min_app');

        $paramMap = [
            'grant_type' => 'client_credential',
            'appid' => $set['key'],
            'secret' => $set['secret'],
        ];
        //获取token的url参数拼接
        $strQuery="";
        foreach ($paramMap as $k=>$v){
            $strQuery .= strlen($strQuery) == 0 ? "" : "&";
            $strQuery.=$k."=".urlencode($v);
        }

        $getTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?". $strQuery; //获取token的url

        $client = new Client;
        $res = $client->request('GET', $getTokenUrl);
       // $res = $this->curl_post($getTokenUrl, '', $options = array());

        $data = json_decode($res->getBody()->getContents(), JSON_FORCE_OBJECT);

        if (isset($data['errcode'])) {
            \Log::debug('===生成小程序二维码获取token失败====='. self::class, $data);
            return false;
        }
        return $data['access_token'];

    }

    //获取微信二维码
    private function getAgentQR()
    {
        $posterService = new CreatePosterService($this->memberModel->uid,$this->posterModel->id,$this->type);
        return $posterService->getQrCodeUrl();
    }

    private function base64_image_content($base64_image_content,$path)
    {
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
            $type = $result[2];
            $new_file = $path."/".date('Ymd',time())."/";

            if(!file_exists($new_file)){
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700);
            }

            $new_file = $new_file.time().".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                return '/'.$new_file;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    //根据type获取各类二维码
    protected function qr($item)
    {
        switch ($this->type) {
            case 1 :
                $url = $this->getQrCodeUrl();
                break;
            case 2 :
                $url = $this->getMiniCode($item['mini_link']);
                break;
            case 5 :
                $url = $this->getQrShopImage($item['h5_link']);
                break;
            case 7 :
                $url = $this->getAppShareImage();
                break;
            default :
                $url = '';
                break;
        }

        return $url;
    }

    protected function getQrCodeUrl()
    {
        $client = new Client;
        $res = $client->request('GET', (new CreateCode($this->memberModel, $this->posterModel, $this->host))->getQrCodeUrl());

        $extend = 'png';
        $filename = 'fans_' . \YunShop::app()->uniacid . '_' . \YunShop::app()->getMemberId()  . '.' . $extend;
        $paths = \Storage::url('app/public/qr/');
        $paths_change = ltrim($paths, '/');

        file_put_contents(base_path($paths_change) . $filename, $res->getBody());

        return $this->host . config('app.webPath') . $paths . $filename;
    }

    protected function getMiniCode($link)
    {
        $res = (new CreateCode($this->memberModel, $this->posterModel, $this->host))->getMiniCode($link);
        if ($res == '') {
            return '';
        }

        $extend = 'png';
        $filename = 'mini_' . \YunShop::app()->uniacid . '_' . \YunShop::app()->getMemberId()  . '.' . $extend;
        $paths = \Storage::url('app/public/qr/');
        $paths_change = ltrim($paths, '/');

        file_put_contents(base_path($paths_change) . $filename, $res);

        return $this->host . config('app.webPath') . $paths . $filename;
    }

    protected function getQrShopImage($link)
    {
        $res = (new CreateCode($this->memberModel, $this->posterModel, $this->host))->getQrShopImage($link);

        $extend = 'png';
        $filename = 'shop_' . \YunShop::app()->uniacid . '_' . \YunShop::app()->getMemberId()  . '.' . $extend;
        $paths = \Storage::url('app/public/qr/');
        $paths_change = ltrim($paths, '/');

        file_put_contents(base_path($paths_change) . $filename, $res);

        return $this->host . config('app.webPath') . $paths . $filename;
    }

    protected function getAppShareImage()
    {
        $res = (new CreateCode($this->memberModel, $this->posterModel, $this->host))->getAppShareImage();

        $extend = 'png';
        $filename = 'share_' . \YunShop::app()->uniacid . '_' . \YunShop::app()->getMemberId()  . '.' . $extend;
        $paths = \Storage::url('app/public/qr/');
        $paths_change = ltrim($paths, '/');

        file_put_contents(base_path($paths_change) . $filename, $res);

        return $this->host . config('app.webPath') . $paths . $filename;
    }
}