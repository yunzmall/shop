<?php

namespace app\frontend\modules\member\controllers;

use app\common\components\ApiController;
use app\common\facades\Setting;
use app\common\helpers\ImageHelper;
use app\common\services\Utils;
use app\frontend\models\Member;
use app\frontend\modules\member\models\MemberModel;
use Yunshop\Poster\models\Poster;
use Yunshop\Poster\models\PosterRecord;
use Yunshop\Poster\services\CreatePosterService;

class PosterController extends ApiController
{
    protected $type;
    protected $uid;

    /**
     * 生成海报接口（新旧）
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $this->type = intval(request()->type);
        $this->uid = \YunShop::app()->getMemberId();
        $imageRes = $this->getPoster();
        if ($imageRes == false) {
            return $this->successJson('成功', [
                'image_url' => ''
            ]);
        }
        $this->exist_record($imageRes);
        $imageUrl = $imageRes['file_path'];
        return $this->successJson('成功', [
            'image_url' => $imageUrl
        ]);
    }
    //判断海报记录是否存在
    private function exist_record($imageRes)
    {
        $image_url = $imageRes['file_path'];
        if (!$image_url) {
            return false;
        }
        if (\YunShop::plugin()->get('new-poster') && $imageRes['type'] == 1) {
            //如果会员第一次生成海报就写入记录表
            $posterModel = \Yunshop\NewPoster\models\Poster::getCenterShowPoster($this->type);
            if (!$posterModel) {
                return false;
            }
            $poster_record = \Yunshop\NewPoster\models\PosterRecord::where([
                'poster_type' => $posterModel->poster_type,
                'url'  => $image_url,
            ])->orderBy('id', 'desc')->first();
            if ($poster_record) {
                $poster_record->url = $image_url;
                $poster_record->save();
            } else {
                $data = [
                    'url' => $image_url,
                    'poster_id' => $posterModel->id,
                    'member_id' => $this->uid,
                    'poster_type' => $posterModel->poster_type,
                    'created_at' => time(),
                ];
                \Yunshop\NewPoster\models\PosterRecord::create($data);
            }
            return true;
        }
        if (\YunShop::plugin()->get('poster') && $imageRes['type'] == 2) {
            //如果会员第一次生成海报就写入记录表
            $posterModel = Poster::uniacid()->select('id')->where('center_show', 1)->first();
            if (!$posterModel) {
                return false;
            }
            $poster_record = PosterRecord::where(['url'=>$image_url])->orderBy('id', 'desc')->first();
            if ($poster_record) {
                $poster_record->url = $image_url;
                $poster_record->save();
            } else {
                $data = [
                    'url' => $image_url,
                    'poster_id' => $posterModel->id,
                    'member_id' => $this->uid,
                    'created_at' => time(),
                ];
                PosterRecord::create($data);
            }
        }
        return true;
    }
    //会员中心推广二维码(包含会员是否有生成海报权限)
    private function getPoster()
    {
        $is_agent = Member::current()->yzMember->is_agent;
        if (\YunShop::plugin()->get('new-poster')) {
            $posterModel = \Yunshop\NewPoster\models\Poster::uniacid()->select(['id','is_open','poster_type'])
                ->whereRaw('FIND_IN_SET('.$this->type.',center_show)')
                ->first();
            if ($posterModel) {
                if ($posterModel->is_open || (!$posterModel->is_open && $is_agent)) {
                    $file_path = (new \Yunshop\NewPoster\services\CreatePosterService(
                        \YunShop::app()->getMemberId(),
                        $posterModel->id,
                        $posterModel->poster_type)
                    )->getMemberPosterPath();
                    if (!$file_path) {
                        return false;
                    }
                    return [
                        'type' => 1,
                        'file_path' => ImageHelper::getImageUrl($file_path),
                    ];
                }
            }
        }
        if (\YunShop::plugin()->get('poster')) {
            if (\Schema::hasColumn('yz_poster', 'center_show')) {
                $posterModel = Poster::uniacid()->select('id', 'is_open')->where('center_show', 1)->first();
                if ($posterModel) {
                    if ($posterModel->is_open || (!$posterModel->is_open && $is_agent)) {
                        $file_path = (new CreatePosterService(\YunShop::app()->getMemberId(), $posterModel->id, $this->type))->getMemberPosterPath();
                        if (!$file_path) {
                            return false;
                        }
                        return [
                            'type' => 2,
                            'file_path' => ImageHelper::getImageUrl($file_path),
                        ];
                    }
                }
            }
        }
        return [
            'type' => 3,
            'file_path' => $this->createPoster(),
        ];
    }
    //生成默认海报
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
        $str_length = $logo_width + $font_size_show * mb_strlen($shopName);
        $space = ($width - $str_length) / 2;
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
        $imgUrl = ImageHelper::getImageUrl($file).'?='.str_random(6);
        return $imgUrl;
    }
    //合并图片并指定图片大小
    private static function mergeImage($destinationImg, $sourceImg, $data)
    {
        $w = imagesx($sourceImg);
        $h = imagesy($sourceImg);
        imagecopyresized($destinationImg,$sourceImg,$data['dst_left'],$data['dst_top'],0,0,$data['dst_width'],$data['dst_height'],$w,$h);
        imagedestroy($sourceImg);
        return $destinationImg;
    }
    //合并字符串
    private static function mergeText($destinationImg, $text, $data)
    {
        $font = base_path() . DIRECTORY_SEPARATOR . "static" . DIRECTORY_SEPARATOR . "fonts" . DIRECTORY_SEPARATOR . "source_han_sans.ttf";
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
}