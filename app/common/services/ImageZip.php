<?php

namespace app\common\services;


class ImageZip {
    /**
     * 压缩/缩略
     * @param $srcFile
     * @param $number
     * @param $type
     * @return bool
     */
    public static function makeThumb($srcFile, $number, $type)
    {
        //type 1 压缩 2 缩略
        $img = getimagesize($srcFile);
        if (!is_file($srcFile) || $img == false) {
            return false;
        }
        list($src_width, $src_height, $src_type) = $img; //原图宽度、高度
        $memory_limit = trim(ini_get('memory_limit'), 'M');
        $img_memory = $src_width * $src_height * 3 * 1.7;
        if ($img_memory > $memory_limit * 1024 * 1024) { //imagecreatetruecolor方法生成图片资源时会占用大量的服务器内存，所以在上传大图、长图时不能使用
            return false;
        }
        if ($type == 2) {
            if ($src_width < $number) {
                $width = $src_width;
                $number = $src_width;
            } else {
                $width = $number;
            }
            $height = $src_height * $number / $src_width;
        } else {
            $width = $src_width;
            $height = $src_height;
        }
        switch ($src_type) {
            case 1 :
                $image_type = 'gif';
                break;
            case 2 :
                $image_type = 'jpeg';
                break;
            case 3 :
                $image_type = 'png';
                break;
            case 15 :
                $image_type = 'wbmp';
                break;
            default :
                return false;
        }
        $image_canvas = imagecreatetruecolor($width, $height); //创建画布
        $bg = imagecolorallocatealpha($image_canvas, 255, 255, 255, 127);
        imagefill($image_canvas, 0, 0, $bg);
        $imagecreatefromfunc = 'imagecreatefrom'.$image_type;
        $image_resources = $imagecreatefromfunc($srcFile);
        imagecopyresampled($image_canvas, $image_resources, 0, 0, 0, 0, $width, $height, $src_width, $src_height); //缩放图片（高精度）
        if ($type == 2) {
            $imagefunc = 'image'.$image_type;
            $imagefunc($image_canvas, $srcFile);
        } else {
            imagejpeg($image_canvas, $srcFile, $number);
        }
        imagedestroy($image_canvas);
        imagedestroy($image_resources);
        return true;
    }
}