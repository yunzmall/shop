<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021-12-24
 * Time: 16:08
 */

namespace app\common\helpers;


use app\common\exceptions\ShopException;
use app\common\facades\Setting;
use EasyWeChat\Factory;

class MiniCodeHelper
{
    private $mini_set;
    private $scene;
    private $dir;
    private $page;
    private $file_name;
    private $width;

    /**
     * MiniCodeHelper constructor.
     * @param string $dir 文件目录 商城根目录开始
     * @param string $file_name 文件名
     * @param string $page 二维码页面路由
     * @param string $scene 二维码链接参数
     * @param int $width 二维码宽度
     */
    public function __construct(string $dir, string $file_name, string $page, string $scene, int $width = 300)
    {
        $this->mini_set = $this->getMiniSet();
        $this->dir = $dir;
        $this->file_name = $file_name;
        $this->page = $page;
        $this->scene = $scene;
        $this->width = $width;
    }

    public function url()
    {
        $config = [
            'app_id' => $this->mini_set['key'],
            'secret' => $this->mini_set['secret'],
        ];
        if (!$config['app_id'] || !$config['secret']) {
            throw new ShopException('小程序未配置');
        }
        $app = Factory::miniProgram($config);
        $parameter = [
            'page' => $this->page,
            'scene' => $this->scene,
            'width' => $this->width,
        ];
        $res = $app->app_code->getUnlimit('scene-value', $parameter);
        if (is_array($res) && isset($res['errcode'])) {
            \Log::debug('-------小程序二维码生成失败-------', [$res['errcode'], $res['errmsg']]);
            throw new ShopException($res['errmsg']);
        }
        $absolute_dir = base_path($this->dir);
        $this->recursionDir($absolute_dir);
        $filename = $res->saveAs($absolute_dir, $this->file_name);
        $mini_code_url = request()->getSchemeAndHttpHost().config('app.webPath').'/'.$this->dir.'/'.$filename;
        return $mini_code_url;
    }

    private function getMiniSet()
    {
        return Setting::get('plugin.min_app');
    }

    private function recursionDir($dir)
    {
        return is_dir($dir) or self::recursionDir(dirname($dir)) and mkdir($dir, 0777);
    }

    public function drawCircle($target)
    {
        $src_img = imagecreatefromstring($target);
        $w = imagesx($src_img);
        $h = imagesy($src_img);
        $w = min($w, $h);
        $h = $w;
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $w / 2; //圆半径
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($src_img, $x, $y);
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

    public function replaceMiddleLogo($target, $logo)
    {
        $target = imagecreatefromstring($target);
        $logo = imagecreatefromstring($logo);
        $target_width = imagesx($target);
        $logo_width  = imagesx($logo);
        $logo_height = imagesy($logo);
        $logo_qr_width = $target_width / 2.2;
        $scale = $logo_width / $logo_qr_width;
        $logo_qr_height = $logo_height / $scale;
        $from_width = ($target_width - $logo_qr_width) / 2;
        imagecopyresampled($target, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
        ob_start();
        imagepng($target);
        imagedestroy($target);
        imagedestroy($logo);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

//    private function handleTarget($file_name)
//    {
//        $type = exif_imagetype($file_name);
//        switch ($type) {
//            case 1 :
//                $target = imagecreatefromgif($file_name);
//                break;
//            case 2 :
//                $target = imagecreatefromjpeg($file_name);
//                break;
//            case 3 :
//                $target = imagecreatefrompng($file_name);
//                break;
//            case 4 :
//                $target = imagecreatefromswf($file_name);
//                break;
//            case 5 :
//                $target = imagecreatefromgpsd($file_name);
//                break;
//            case 6 :
//                $target = imagecreatefrombmp($file_name);
//                break;
//            case 7 :
//                $target = imagecreatefromtiffii($file_name);
//                break;
//            case 8 :
//                $target = imagecreatefromtiffmm($file_name);
//                break;
//            case 9 :
//                $target = imagecreatefromjpc($file_name);
//                break;
//            case 10 :
//                $target = imagecreatefromjp2($file_name);
//                break;
//            case 11 :
//                $target = imagecreatefromjpx($file_name);
//                break;
//            case 12 :
//                $target = imagecreatefromjb2($file_name);
//                break;
//            case 13 :
//                $target = imagecreatefromswc($file_name);
//                break;
//            case 14 :
//                $target = imagecreatefromiff($file_name);
//                break;
//            case 15 :
//                $target = imagecreatefromwbmp($file_name);
//                break;
//            case 16 :
//                $target = imagecreatefromxbm($file_name);
//                break;
//            case 17 :
//                $target = imagecreatefromico($file_name);
//                break;
//            case 18 :
//                $target = imagecreatefromwebp($file_name);
//                break;
//            default :
//                $target = false;
//                break;
//        }
//        return $target;
//    }
}