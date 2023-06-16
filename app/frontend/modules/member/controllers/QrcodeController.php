<?php

namespace app\frontend\modules\member\controllers;

use app\common\components\ApiController;
use app\common\facades\Setting;
use app\common\helpers\ImageHelper;
use app\common\services\MiniFileLimitService;
use app\common\services\Utils;
use app\frontend\models\Member;
use app\frontend\modules\member\models\MemberModel;
use Yunshop\NewPoster\services\CreateCode;
use Yunshop\Poster\models\Poster;
use Yunshop\Poster\models\PosterRecord;
use GuzzleHttp\Client;

class QrcodeController extends ApiController
{
    protected $type;
    protected $host;
    protected $uid;
    protected $posterModel;
    protected $memberModel;
    protected $poster_id;

    const WE_CHAT_SHOW_QR_CODE_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';

    /**
     * 会员中心推广二维码(包含会员是否有生成海报权限)
     * @return \Illuminate\Http\JsonResponse
     * @throws \app\common\exceptions\AppException
     */
    public function getPoster()
    {
        $this->type = intval(request()->type) == 2?2:1;
        $this->host = $host = request()->getSchemeAndHttpHost();
        $this->uid = \YunShop::app()->getMemberId();
        $is_agent = Member::current()->yzMember->is_agent;
        $this->memberModel = $memberModel = Member::uniacid()
            ->select('uid', 'avatar', 'nickname')
            ->with('yzMember')
            ->ofUid($this->uid)
            ->first();
        //新海报
        if (\YunShop::plugin()->get('new-poster')) {
            $this->type = intval(request()->type);
            $this->poster_id = intval(request()->poster_id);

            if ($this->poster_id) {
                $this->posterModel = $posterModel = \Yunshop\NewPoster\models\Poster::uniacid()
                    ->where('id',$this->poster_id)
                    ->first();
            } else {
                $this->posterModel = $posterModel = \Yunshop\NewPoster\models\Poster::uniacid()
                    ->whereRaw('FIND_IN_SET('.$this->type.',center_show)')
                    ->first();
            }

            if (!$this->posterModel) {
                //默认二维码
                if ($this->createPoster()) {
                    return $this->successJson('ok', [
                        'image_url' => $this->createPoster(),
                        'center_show'=> '0'
                    ]);
                } else {
                    return $this->errorJson('生成海报失败');
                }
            }
            $this->type = $this->posterModel->poster_type; //取海报的类型
            $file_name = $this->getFileName($this->posterModel, $this->type, true);
            $posterRecord = \Yunshop\NewPoster\models\PosterRecord::where([
                'member_id' => $this->uid,
                'poster_type' => $this->type,
                'poster_id' => $this->posterModel->id
            ])->orderby('id', 'desc')->first();
            if ($posterRecord) {
                $file_res = strstr($posterRecord->url, $file_name);
                if ($file_res) {
                    return $this->successJson('ok', [
                        'image_url' => $posterRecord->url.'?='.str_random(6),
                        'center_show'=> '0',
                    ]);
                }
            }
            if ($posterModel->is_ago == 0) {
                return $this->successJson('ok', [
                    'image_url' => '',
                    'center_show'=> '2'
                ]);
            }
            if ($posterModel->is_open || (!$posterModel->is_open && $is_agent)) {
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
                            $item['src'] = $this->qrByType($item, $this->type); //$type 海报类型
                            break;
                        case 'img' :
                            $item['src'] = yz_tomedia($item['src']);
                            break;
                        case 'invite' :
                            $item['src'] = $this->memberModel->yzMember->invite_code;
                            break;
                        case 'mid' :
                            $item['src'] = $this->memberModel->uid;
                            break;
                        case 'shopqr' :
                            $item['src'] = $this->qrByType($item, 5);
                            break;
                    }
                }
                $posterModel['style_data'] = $poster_style;
                $posterModel['background'] = yz_tomedia($posterModel['background']);
                $posterModel['new'] = true;
                $posterModel['center_show'] = 1;
                return $this->successJson('ok', $posterModel);
            }
        }
        //旧海报
        if (\YunShop::plugin()->get('poster') && \Schema::hasColumn('yz_poster', 'center_show')) {
            $posterModel = Poster::uniacid()->where('center_show', 1)->first();
            if (!$posterModel) {
                //默认二维码
                if ($this->createPoster()) {
                    return $this->successJson('ok', [
                        'image_url' => $this->createPoster(),
                        'center_show'=> '0'
                    ]);
                } else {
                    return $this->errorJson('生成海报失败');
                }
            }
            $file_name = $this->getFileName($posterModel, $this->type, false);
            $posterRecord = PosterRecord::where([
                'member_id'=>$this->uid,
                'poster_id'=>$posterModel->id,
            ])->orderby('id', 'desc')->first();
            if ($posterRecord) {
                $file_res = strstr($posterRecord->url, $file_name);
                if ($file_res) {
                    return $this->successJson('ok', [
                        'image_url' => $posterRecord->url.'?='.str_random(6),
                        'center_show'=> '0',
                    ]);
                }
            }
            //判断是否由后台生成海报 0后台生成海报  1前端生成
            if ($posterModel->is_ago == 0) {
                return $this->successJson('ok', [
                    'image_url' => '',
                    'center_show'=> '2'
                ]);
            }
            if ($posterModel->is_open || (!$posterModel->is_open && $is_agent)) {
                $poster_info = $posterModel->toArray();
                $params = json_decode($poster_info['style_data'], true);
                foreach ($params as $key => $item) {
                    $item = $this->getRealParams($item);
                    switch ($item['type']) {
                        case 'head':
                            $item['src'] = ImageHelper::fix_wechatAvatar($memberModel->avatar_image);
                            break;
                        case 'qr_shop':
                            $item['src'] = $host . yzAppUrl('home', ['mid' => $this->memberModel->uid]);
                            break;
                        case 'qr_app_share':
                            $item['src'] = $host . yzAppUrl('member/scaneditmobile', ['mid' => $this->memberModel->uid , 'app_type' => 7]);
                            break;
                        case 'nickname':
                            $item['src'] = $memberModel->nickname;
                            break;
                        case 'img':
                            $item['src'] = yz_tomedia($item['src']);
                            break;
                    }
                    $params[$key] = $item;
                }
                $poster_info['style_data'] = $params;
                $poster_info['new'] = false;
                if ($poster_info) {
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
        $image_url = request()->image;
        $poster_id = request()->poster_id;
        $is_new = request()->is_new;
        $uid = \YunShop::app()->getMemberId();
        if (!$image_url || !$poster_id) {
            return $this->errorJson('缺少参数');
        }
        if (app('plugins')->isEnabled('new-poster') && $is_new) {
            $poster = \Yunshop\NewPoster\models\Poster::find($poster_id);
            $poster_record = \Yunshop\NewPoster\models\PosterRecord::where([
                'poster_type' => $poster->poster_type,
                'url' => $image_url,
            ])->orderBy('id', 'desc')->first();
            if ($poster_record) {
                $poster_record->url = $image_url;
                $poster_record->save();
            } else {
                $data = [
                    'url' => $image_url,
                    'poster_id' => $poster_id,
                    'member_id' => $uid,
                    'poster_type' => $poster->poster_type,
                    'created_at' => time(),
                ];
                \Yunshop\NewPoster\models\PosterRecord::create($data);
            }
        }
        if (app('plugins')->isEnabled('poster') && !$is_new) {
            $poster_record = PosterRecord::where(['url'=>$image_url])->orderBy('id', 'desc')->first();
            if ($poster_record) {
                $poster_record->url = $image_url;
                $poster_record->save();
            } else {
                $data = [
                    'url' => $image_url,
                    'poster_id' => $poster_id,
                    'member_id' => $uid,
                    'created_at' => time(),
                ];
                PosterRecord::create($data);
            }
        }
        return $this->successJson('成功');
    }
    /**
     * 生成默认海报
     * @return string
     */
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
            $fingerPrintImg = imagecreatefromstring(\Curl::to($this->getImgUrl('ewm.png'))->get());
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
        imagecopyresampled($destinationImg,$sourceImg,$data['dst_left'],$data['dst_top'],0,0,$data['dst_width'],$data['dst_height'],$w,$h);
        imagedestroy($sourceImg);
        return $destinationImg;
    }
    //合并字符串
    private static function mergeText($destinationImg, $text, $data)
    {
        putenv('GDFONTPATH=' . base_path('static/fonts'));
        $font = base_path() . DIRECTORY_SEPARATOR . "static" . DIRECTORY_SEPARATOR . "fonts" . DIRECTORY_SEPARATOR . "source_han_sans.ttf";
        $black = imagecolorallocate($destinationImg, 0, 0, 0);
        imagettftext($destinationImg, $data['size'], 0, $data['left'], $data['top'], $black, $font, $text);
        return $destinationImg;
    }
    //获取图片url
    private function getImgUrl($file)
    {
        if (config('app.framework') == 'platform') {
            return request()->getSchemeAndHttpHost().'/addons/yun_shop/static/app/images/'.$file;
        } else {
            return base_path() . '/static/app/images/'.$file;
        }
    }
    //处理坐标
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
    //根据type获取各类二维码
    protected function qrByType($item, $type)
    {
        switch ($type) {
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
    //关注二维码
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
    //小程序二维码
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
        return $this->host . config('app.webPath') . $paths . $filename.'?v='.str_random(6);
    }
    //商城二维码
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
    //app二维码
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

    /**
     * 海报上传本地
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadLocal()
    {
        $file = request()->file('file');
        $ingress = request()->ingress;
        $poster_id = request()->poster_id;
        if (!$file) {
            return $this->errorJson('请传入正确参数.');
        }
        if (!$file->isValid()) {
            return $this->errorJson('上传失败.');
        }
        if ($ingress) {
            if ($file->getClientSize() > 1024*1024) {
                return $this->errorJson('小程序图片安全验证图片不能大于1M');
            }
            $check_result = (new MiniFileLimitService())->checkImg($file);
            if ($check_result['errcode'] == 87014) {
                return $this->errorJson('内容含有违法违规信息');
            }
        }
        $realPath = $file->getRealPath(); //临时文件的绝对路径
        $is_new = request()->is_new;
        if ($is_new) {
            $posterModel = \Yunshop\NewPoster\models\Poster::find($poster_id);
            $type = $posterModel->poster_type;
        } else {
            $posterModel = Poster::find($poster_id);
            $type = request()->type == 2 ? 2 : 1;
        }
        if (!$posterModel) {
            return $this->errorJson('海报已删除');
        }
        $file_name = $this->getFileName($posterModel, $type, $is_new);
        $path = storage_path('app/public/poster/' . \YunShop::app()->uniacid);
        if (!file_exists($path)) {
            Utils::mkdirs($path);
        }
        $full_path = $path . DIRECTORY_SEPARATOR . $file_name;
        file_put_contents($full_path, file_get_contents($realPath));
        return $this->successJson('ok', [
            'img_url' => $this->getPosterUrl($file_name),
        ]);
    }
    private function getFileName($posterModel, $type, $is_new_poster)
    {
        $file = md5(json_encode([
            'memberId' => \YunShop::app()->getMemberId(),
            'posterId' => $posterModel->id,
            'uniacid' => \YunShop::app()->uniacid,
            'background' => $posterModel->background,
            'style_data' => $posterModel->style_data,
        ]));
        if ($is_new_poster) {
            return $file.'_new_'.$type.'.png';
        } else {
            return $file.'_'.$type.'.png';
        }
    }
    private function getPosterUrl($file_name)
    {
        if (config('app.framework') == 'platform') {
            return request()->getSchemeAndHttpHost().DIRECTORY_SEPARATOR.'storage/app/public/poster/'.\YunShop::app()->uniacid.DIRECTORY_SEPARATOR.$file_name;
        } else {
            return request()->getSchemeAndHttpHost().DIRECTORY_SEPARATOR.'addons/yun_shop/storage/app/public/poster/'.\YunShop::app()->uniacid.DIRECTORY_SEPARATOR.$file_name;
        }
    }
}