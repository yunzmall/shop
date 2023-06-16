<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/7/8
 * Time: 14:12
 */

namespace app\common\services;


class SmallQrCode
{
    public function getSmallQrCode($small_name,$postData)
    {
        //$small_url = "packageD/preferential_volume/preferential_volume";
        //$small_url = "pages/index/index";
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?";
        $token = $this->getToken();
        \Log::debug('===========access_token===========', $token);
        $url .= "access_token=" . $token;
//        $postdata = [
//            "scene" => 'vgid=' . $goods_id . ',vcpn='.$member_coupon. ',mid=' . $member_id,
//            "page"  => $small_url,
//        ];
        $path = storage_path('static/qrcode/coupon/' . \YunShop::app()->uniacid);
        if (!is_dir($path)) {
            Utils::mkdirs($path);
        }
        \Log::debug('=====地址信息=======', $postData);
        $res = $this->curl_post($url, json_encode($postData), $options = array());
        $erroe = json_decode($res);

        $data['message'] = "";
        $data['file_path'] = "";
        $data['code'] = 0;

        if (isset($erroe->errcode)) {
            $data['message'] = '错误码' . $erroe->errcode . ';错误信息' . $erroe->errmsg;
            $data['code'] = 1;
            return $data;
        }
        \Log::debug('===========生成小程序二维码===========', $res);
        $file = date('Ymd').'_code_qr_small_'.$small_name.'.png';
        file_put_contents($path . '/' . $file, $res);

        $imgPath = $path . '/' . $file;

        if (config('app.framework') == 'platform') {
            $urlPath = request()->getSchemeAndHttpHost() . '/storage/' . substr($imgPath, strpos($imgPath, 'static'));
        } else {
            $urlPath = request()->getSchemeAndHttpHost() . '/' . substr($imgPath, strpos($imgPath, 'addons'));
        }

        $data['file_path'] = $urlPath;
        return $data;
    }

    private function curl_post($url = '', $postdata = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    //发送获取token请求,获取token(2小时)
    private function getToken()
    {
        $url = $this->getTokenUrlStr();
        $res = $this->curl_post($url, $postdata = '', $options = array());

        $data = json_decode($res, JSON_FORCE_OBJECT);
        return $data['access_token'];
    }

    //获取token的url参数拼接
    private function getTokenUrlStr()
    {
        $set = \Setting::get('plugin.min_app');
        $getTokenUrl = "https://api.weixin.qq.com/cgi-bin/token?"; //获取token的url
        $WXappid = $set['key']; //APPID
        $WXsecret = $set['secret']; //secret
        $str = $getTokenUrl;
        $str .= "grant_type=client_credential&";
        $str .= "appid=" . $WXappid . "&";
        $str .= "secret=" . $WXsecret;
        return $str;
    }

     /*
        * 将图片转换为圆形
      */
     public static function drawCircle($imgpath)
      {
         $src_img = imagecreatefromstring($imgpath);
         $w   = imagesx($src_img);
         $h   = imagesy($src_img);
         $w   = min($w, $h);
         $h   = $w;
         $img = imagecreatetruecolor($w, $h);
         //必须
         imagesavealpha($img, true);
         //全透明
         $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
         imagefill($img, 0, 0, $bg);
         $r   = $w / 2; //圆半径
         for ($x = 0; $x < $w; $x++) {
             for ($y = 0; $y < $h; $y++) {
                 $rgbColor = imagecolorat($src_img, $x, $y);
//                 $rgbColor = imagecolorsforindex($src_img,$rgbColor);
                 if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                      imagesetpixel($img, $x, $y, $rgbColor);
                 }
             }
         }
          ob_start();
          imagepng($img);
          imagedestroy($img);
          $contents = ob_get_contents();
          ob_end_clean();
          return $contents;
      }

     //替换小程序二维码图片中间的logo
     public static function replaceMiddleLogo($target,$logo)
     {
         $target = imagecreatefromstring ($target);
         $logo = imagecreatefromstring ($logo);
         $target_width    = imagesx($target);//二维码图片宽度
         //$target_height   = imagesy($target);//二维码图片高度
         $logo_width  = imagesx($logo);//logo图片宽度
         $logo_height = imagesy($logo);//logo图片高度
         $logo_qr_width  = $target_width / 2.2;//组合之后logo的宽度(占二维码的1/2.2)
         $scale  = $logo_width / $logo_qr_width;//logo的宽度缩放比(本身宽度/组合后的宽度)
         $logo_qr_height = $logo_height / $scale;//组合之后logo的高度
         $from_width = ($target_width - $logo_qr_width) / 2;//组合之后logo左上角所在坐标点

          //重新组合图片并调整大小
          //imagecopyresampled() 将一幅图像(源图象)中的一块正方形区域拷贝到另一个图像中
         imagecopyresampled($target, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
         /**
          * 如果想要直接输出图片，应该先设header。header("Content-Type: image/png; charset=utf-8");
          * 并且去掉缓存区函数
          */
         //获取输出缓存，否则imagepng会把图片输出到浏览器
         ob_start();
         imagepng($target);
         imagedestroy($target);
         imagedestroy($logo);
         $contents = ob_get_contents();
         ob_end_clean();
         return $contents;
     }
}