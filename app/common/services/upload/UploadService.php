<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021-04-26
 * Time: 15:59
 */

namespace app\common\services\upload;


use app\common\exceptions\ShopException;
use app\common\services\ImageZip;
use app\platform\modules\system\models\SystemSetting;

class UploadService
{
    private $setting;
    private $originalName;
    private $realPath;
    private $ext;
    private $mime_type;
    private $fileSize;
    private $diyFileName;
    private $is_remote;
    private $dir;
    private $fileNewName;
    private $file_type;
    private $relative_path;
    private $harm_type = array('asp', 'php', 'jsp', 'js', 'css', 'php3', 'php4', 'php5', 'ashx', 'aspx', 'exe', 'cgi');

    private $default_audio_types = array(
        'avi', 'asf', 'wmv', 'avs', 'flv', 'mkv', 'mov', '3gp', 'mp4', 'mpg', 'mpeg', 'dat', 'ogm', 'vob', 'rm', 'rmvb', 'ts', 'tp', 'ifo', 'nsv',
    );
    private $default_video_types = array(
        'mp3', 'aac', 'wav', 'wma', 'cda', 'flac', 'm4a', 'mid', 'mka', 'mp2', 'mpa', 'mpc', 'ape', 'ofr', 'ogg', 'ra', 'wv', 'tta', 'ac3', 'dts',
    );
    private $default_image_types = array(
        'jpg', 'bmp', 'eps', 'gif', 'mif', 'miff', 'png', 'tif', 'tiff', 'svg', 'wmf', 'jpe', 'jpeg', 'dib', 'ico', 'tga', 'cut', 'pic'
    );
    private $default_file_types = array(
        'pdf', 'xlsx', 'xls', 'doc', 'docx', 'txt', 'ppt', 'pptx', 'xml', 'wps', 'rtf', 'md', 'rar', 'zip', 'et', 'json',
    );
    private $default_file_mime_type = [
        'audio/aac', 'video/x-msvideo', 'image/bmp', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/gif',
        'image/vnd.microsoft.icon', 'image/jpeg', 'audio/midi', 'audio/x-midi', 'audio/mpeg', 'video/mpeg', 'image/png', 'application/pdf', 'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/x-rar-compressed', 'application/rtf', 'image/svg+xml', 'image/tiff',
        'text/plain', 'audio/wav', 'image/webp', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/xml', 'text/xml',
        'video/3gpp', 'audio/3gpp', 'video/x-ms-asf', 'video/x-ms-wmv', 'video/x-flv', 'video/quicktime', 'video/mp4', 'audio/x-wav', 'audio/x-m4a', 'audio/mid', 'audio/ogg',
        'audio/x-realaudio', 'application/postscript', 'application/x-msmetafile', 'image/x-icon', 'application/vnd.ms-works', 'application/rar', 'application/zip', 'application/x-rar',
        'application/octet-stream', 'application/x-font-gdos'
    ];

    public function __construct()
    {

    }
    public function upload($file, $file_type = 'image', $dir = '', $diy_file_name = '', $is_remote = true)
    {
        if (!$dir) {
            $this->dir = $this->getDirByType($file_type);
        } else {
            $this->dir = $dir;
        }
        $this->file_type = $file_type;
        $this->diyFileName = $diy_file_name;
        $this->is_remote = $is_remote;
        $this->setting = $this->getSetting();
        $this->initFile($file);
        $this->checkFile();
        $this->handleFile();
        $this->localUpload();
        $this->rotatePic();
        if ($this->setting['remote']['type'] != 0 && $is_remote) {
            $this->remoteUpload();
        }
        $url = $this->getUrl();
        $this->examine($url);
        return [
            'relative_path' => $this->getDiskUrl(),
            'absolute_path' => $url,
            'file_name' => $this->getFileName(),
        ];
    }
    private function getDirByType($upload_type)
    {
        switch ($upload_type) {
            case 'video' :
                $dir = 'videos';
                break;
            case 'audio' :
                $dir = 'audios';
                break;
            case 'file' :
                $dir = 'files';
                break;
            default :
                $dir = 'image';
                break;
        }
        return $dir;
    }
    private function getFileName()
    {
        if ($this->diyFileName) {
            return $this->diyFileName;
        }
        if (isset($this->fileNewName)) {
            return $this->fileNewName;
        } else {
            $this->fileNewName = md5($this->originalName.str_random(6)).'.'.$this->ext;
        }
        return $this->fileNewName;
    }
    private function getDiskUrl()
    {
        return $this->relative_path;
    }
    private function localUpload()
    {
        $uniacid = intval(\YunShop::app()->uniacid);
        $path = $this->file_type.'s/'.$uniacid.'/'.date('Y/m/');
        $dir = $this->basePath().'/'.$path;
        $this->mkDir($dir);
        $file_name = $this->getFileName();
        $save_path = $dir.$file_name;
        $relative_path = $path.$file_name;
        $this->relative_path = $relative_path;
        if (!$this->fileMove($this->realPath, $save_path)) {
            return false;
        }
        return true;
    }
    private function remoteUpload()
    {
        if (config('app.framework') == 'platform') {
            if ($this->setting['remote']['type'] != 0) {
                file_remote_upload($this->getDiskUrl(), true, $this->setting['remote']);
            }
        } else {
            if ($this->setting['remote']['type'] != 0) {
                file_remote_upload_wq($this->getDiskUrl(), true, $this->setting['remote']);
            }
        }
    }
    private function handleFile()
    {
        if ($this->file_type != 'image') {
            return;
        }
        if ($this->setting['upload']['thumb'] == 1 && $this->setting['upload']['width'] && $this->ext != 'gif') {
            ImageZip::makeThumb($this->realPath, $this->setting['upload']['width'], 2);
        }
        if ($this->setting['upload']['percent'] && $this->setting['upload']['percent'] != 100 && $this->ext != 'gif') {
            ImageZip::makeThumb($this->realPath, $this->setting['upload']['percent'], 1);
        }
    }
    private function getUrl()
    {
        if ($this->is_remote) {
            return yz_tomedia($this->getDiskUrl());
        } else {
            return change_to_local_url($this->getDiskUrl());
        }
    }
    public static function getSetting()
    {
        if (config('app.framework') == 'platform') {
            $global_setting = SystemSetting::settingLoad('global', 'system_global');
            $remote = SystemSetting::settingLoad('remote', 'system_remote');
            $upload['image_ext'] = $global_setting['image_extentions'];//图片文件拓展名
            $upload['image_limit'] = $global_setting['image_limit'];//图片文件限制大小
            $upload['percent'] = $global_setting['zip_percentage'];//图片压缩比例
            $upload['thumb'] = $global_setting['thumb'];//是否开启缩略
            $upload['width'] = $global_setting['thumb_width'];//缩略图最大宽度
            $upload['audio_ext'] = $global_setting['audio_extentions'];//音频文件拓展名
            $upload['audio_limit'] = $global_setting['audio_limit'];//音频文件限制大小
        } else {
            //全局配置
            global $_W;
            $global_upload = $_W['setting']['upload'];
            //公众号独立配置信息 优先使用公众号独立配置
            $uni_setting = app('WqUniSetting')->get()->toArray();
            if (!empty($uni_setting['remote']) && iunserializer($uni_setting['remote'])['type'] != 0) {
                $remote = iunserializer($uni_setting['remote']);
            } else {
                $remote = $_W['setting']['remote'];
            }
            $upload['image_ext'] = $global_upload['image']['extentions'];//图片文件拓展名
            $upload['image_limit'] = $global_upload['image']['limit'];//文件限制大小
            $upload['percent'] = $global_upload['image']['zip_percentage'];//压缩比例
            $upload['thumb'] = $global_upload['image']['thumb'];//是否开启缩略
            $upload['width'] = $global_upload['image']['width'];//缩略图最大宽度
            $upload['audio_ext'] = $global_upload['audio']['extentions'];//音频文件拓展名
            $upload['audio_limit'] = $global_upload['audio']['limit'];//音频文件限制大小
        }
        return array('upload' => $upload, 'remote' => $remote);
    }
    private function initFile($file)
    {
        $this->originalName = $file->getClientOriginalName(); // 文件原名
        $this->realPath = $file->getRealPath(); //临时文件的绝对路径
        $this->ext = strtolower($file->getClientOriginalExtension()); //文件后缀
        $this->handelMimeType($file);
        if ($this->file_type == 'image') {
            $this->ext = strtolower($file->getClientOriginalExtension()) ?: 'png';
        }
        if (!$this->ext && !empty($file->getMimeType())) {//兼容上传文件为前端转过格式的文件，获取不了后缀名情况
            $type = explode('/',$file->getMimeType());
            !empty($type[1]) || $type[1] = '';
            switch ($type[1]) {
                case 'x-wav':
                    $this->ext = 'wav';break;
            }
        }
        $this->fileSize = $file->getClientSize(); //文件大小
        $this->mime_type = $file->getMimeType();
    }
    private function handelMimeType($file)
    {
        $mime_type = $file->getClientMimeType(); //获取文件类型
        if (strexists($mime_type, 'image')) {
            $this->file_type = 'image';
        }
        if (strexists($mime_type, 'video')) {
            $this->file_type = 'video';
        }
        if (strexists($mime_type, 'audio')) {
            $this->file_type = 'audio';
        }
    }
    private function checkFile()
    {
        if (!in_array($this->mime_type, $this->default_file_mime_type)) {
            throw new ShopException('无法识别的文件mime类型：'.$this->mime_type);
        }
        if (in_array($this->ext, $this->harm_type)) {
            throw new ShopException('请上传正确的文件格式');
        }
        if (!in_array($this->ext, array_merge($this->default_image_types, $this->default_video_types, $this->default_audio_types, $this->default_file_types))) {
            throw new ShopException('非规定类型的文件默认格式.');
        }
        if ($this->file_type == 'image' && !in_array($this->ext, $this->setting['upload']['image_ext'])) {
            throw new ShopException('非规定类型的图片文件格式.');
        }
        if (($this->file_type == 'video' || $this->file_type == 'audio') && !in_array($this->ext, $this->setting['upload']['audio_ext'])) {
            throw new ShopException('非规定类型的音频文件格式.');
        }
        $default_img_size = $this->setting['upload']['image_limit'] ? $this->setting['upload']['image_limit'] * 1024 : 1024 * 1024 * 5;
        if ($this->file_type == 'image' && $this->fileSize > $default_img_size) {
            throw new ShopException('图片文件大小超出规定值.');
        }
        $default_audio_size = $this->setting['upload']['audio_limit'] ? $this->setting['upload']['audio_limit'] * 1024 : 1024 * 1024 * 25;
        if (($this->file_type == 'video' || $this->file_type == 'audio') && $this->fileSize > $default_audio_size) {
            throw new ShopException('音频文件大小超出规定值.');
        }
        return true;
    }
    private function examine($url)
    {
        if (app('plugins')->isEnabled('upload-verification')) {
            if (in_array($this->ext, ['png','jpg','jpeg','bmp','gif','webp','tiff'])) {
                $uploadResult = do_upload_verificaton($url, 'img');
                if (0 === $uploadResult[0]['status']) {
                    throw new ShopException($uploadResult[0]['msg']);
                }
            }
            if ($this->file_type == 'audio') {
                $uploadResult = do_upload_verificaton($url, 'audio');
                if (0 === $uploadResult[0]['status']) {
                    throw new ShopException($uploadResult[0]['msg']);
                }
            }
            if ($this->file_type == 'video') {
                $uploadResult = do_upload_verificaton($url, 'video');
                if (0 === $uploadResult[0]['status']) {
                    throw new ShopException($uploadResult[0]['msg']);
                }
            }
        }
    }
    private function rotatePic()
    {
        $url = change_to_local_url($this->getDiskUrl());
        if (!in_array($this->ext, ['png','jpg','jpeg','bmp','gif','webp','tiff'])) {
            return false;
        }
        $img_size = getimagesize($url);
        list($src_width, $src_height) = $img_size;
        $memory_limit = trim(ini_get('memory_limit'), 'M');
        $img_memory = $src_width * $src_height * 3 * 1.7;
        if ($img_memory > $memory_limit * 1024 * 1024) { //imagecreatetruecolor方法生成图片资源时会占用大量的服务器内存，所以在上传大图、长图时不能使用
            return false;
        }
        if (function_exists('exif_read_data')) {
            $exif = exif_read_data($url);
            if (!$exif) {
                return false;
            }
            $image = imagecreatefromstring(file_get_contents($url));
            if (!empty($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 8:
                        $image = imagerotate($image, 90, 0);
                        break;
                    case 3:
                        $image = imagerotate($image, 180, 0);
                        break;
                    case 6:
                        $image = imagerotate($image, -90, 0);
                        break;
                }
                if ($exif['Orientation'] != 1) {
                    if ($exif['MimeType'] == 'image/gif') {
                        imagegif($image, $url);
                    } else if($exif['MimeType'] == 'image/png') {
                        imagepng($image, $url);
                    } else {
                        imagejpeg($image, $url);
                    }
                }
            }
        }
        return true;
    }
    private function basePath()
    {
        if (config('app.framework') == 'platform') {
            $path = base_path('static/upload');
        } else  {
            $path = dirname(dirname(base_path())).'/attachment';
        }
        return $path;
    }
    private function fileMove($filename, $dest)
    {
        $this->mkDir(dirname($dest));
        if (is_uploaded_file($filename)) {
            move_uploaded_file($filename, $dest);
        } else {
            rename($filename, $dest);
        }
        @chmod($filename, 0777);
        return is_file($dest);
    }
    private function mkDir($dir)
    {
        return is_dir($dir) or self::mkDir(dirname($dir)) and mkdir($dir, 0777);
    }
}
