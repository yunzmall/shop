<?php 

namespace app\platform\modules\Application\controllers;

use app\platform\controllers\BaseController;
use app\platform\modules\system\models\SystemSetting;
use app\platform\modules\application\models\CoreAttach;
use app\common\services\qcloud\Api;
use app\common\services\aliyunoss\OssClient;
use app\common\services\aliyunoss\OSS\Core\OssException;
use Illuminate\Support\Carbon;

class AllUploadController extends BaseController
{
    protected $path;
    protected $pattern;

    public function __construct()
    {
        $this->path = config('filesystems.disks.syst')['url'].'/'; //本地图片实际存放路径
    }

	public function upload()
    {
        $file = request()->file('file');
        if (!$file->isValid()) {
            return $this->errorJson('上传失败');
        }
        if ($file->getClientSize() > 30*1024*1024) {
            return $this->errorJson('上传图片资源过大');
        }
        //默认支持的文件格式类型
        $defaultImgType = [
            'jpg', 'bmp', 'eps', 'gif', 'mif', 'miff', 'png', 'tif',
            'tiff', 'svg', 'wmf', 'jpe', 'jpeg', 'dib', 'ico', 'tga', 'cut', 'pic'
        ];
        $defaultAudioType = ['avi', 'asf', 'wmv', 'avs', 'flv', 'mkv', 'mov', '3gp', 'mp4',
            'mpg', 'mpeg', 'dat', 'ogm', 'vob', 'rm', 'rmvb', 'ts', 'tp', 'ifo', 'nsv'
        ];
        $defaultVideoType = [
            'mp3', 'aac', 'wav', 'wma', 'cda', 'flac', 'm4a', 'mid', 'mka', 'mp2',
            'mpa', 'mpc', 'ape', 'ofr', 'ogg', 'ra', 'wv', 'tta', 'ac3', 'dts'
        ];
        $default_file_mime_type = [
            'audio/aac', 'video/x-msvideo', 'image/bmp', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/gif',
            'image/vnd.microsoft.icon', 'image/jpeg', 'audio/midi', 'audio/x-midi', 'audio/mpeg', 'video/mpeg', 'image/png', 'application/pdf', 'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/x-rar-compressed', 'application/rtf', 'image/svg+xml', 'image/tiff',
            'text/plain', 'audio/wav', 'image/webp', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/xml', 'text/xml',
            'video/3gpp', 'audio/3gpp', 'video/x-ms-asf', 'video/x-ms-wmv', 'video/x-flv', 'video/quicktime', 'video/mp4', 'audio/x-wav', 'audio/x-m4a', 'audio/mid', 'audio/ogg',
            'audio/x-realaudio', 'application/postscript', 'application/x-msmetafile', 'image/x-icon', 'application/vnd.ms-works', 'application/rar', 'application/zip',
        ];
        $ext = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $realPath = $file->getRealPath();
        $mime_type = $file->getMimeType();
        if (!in_array($mime_type, $default_file_mime_type)) {
            return $this->errorJson('文件类型错误上传失败');
        }
        $merge_ext = array_merge($defaultImgType, $defaultAudioType, $defaultVideoType);
        if (!in_array($ext, $merge_ext)) {
            return $this->errorJson('非规定类型的文件格式');
        }
        if (in_array($ext, $defaultImgType)) {
            $file_type = 'images';
        } elseif (in_array($ext, $defaultAudioType)) {
            $file_type = 'audios';
        } elseif (in_array($ext, $defaultVideoType)) {
            $file_type = 'videos';
        }
        $newFileName = $this->getNewFileName($originalName, $ext);
        $setting = SystemSetting::settingLoad('global', 'system_global');
        $remote = SystemSetting::settingLoad('remote', 'system_remote');
        if (in_array($ext, $defaultImgType)) {
            if ($setting['image_extentions'] && !in_array($ext, array_filter($setting['image_extentions'])) ) {
                return $this->errorJson('非规定类型的图片格式');
            }
            $defaultImgSize = $setting['img_size'] ? $setting['img_size'] * 1024 : 1024*1024*5; //默认大小为5M
            if ($file->getClientSize() > $defaultImgSize) {
                return $this->errorJson('图片文件大小超出规定值');
            }
        }
        if (in_array($ext, $defaultAudioType) || in_array($ext, $defaultVideoType)) {
            if ($setting['audio_extentions'] && !in_array($ext, array_filter($setting['audio_extentions'])) ) {
                return $this->errorJson('非规定类型的文件格式');
            }
            $defaultAudioSize = $setting['audio_limit'] ? $setting['audio_limit'] * 1024 : 1024*1024*30; //音视频最大 30M
            if ($file->getClientSize() > $defaultAudioSize) {
                return $this->errorJson('文件大小超出规定值');
            }
        }
        $file_type = $file_type == 'images' ? 'syst_images' : $file_type;
        if (!\Storage::disk($file_type)->put($newFileName, file_get_contents($realPath))) {
            return $this->errorJson('本地上传失败');
        }
        $url = \Storage::disk($file_type)->url($newFileName);
        if ($remote['type'] != 0) {
            file_remote_upload($url, true, $remote);
        }
        $this->getData($originalName, $file_type, $url, $remote['type']);
        return $this->successJson('ok', ['success' => yz_tomedia($url), 'fail' => '']);
    }

    /**
     * 获取新文件名
     * @param  string $originalName 原文件名
     * @param  string $ext          文件扩展名
     * @return string               新文件名
     */
    public function getNewFileName($originalName, $ext)
    {
        return md5($originalName . str_random(6)) . '.' . $ext;
    }

    public function getUniacid()
    {
        return \YunShop::app()->uniacid ? : 0;
    }

	//获取本地已上传图片的列表
    public function getLocalList()
    {
        if (request()->year != '不限') {
            $search['year'] = request()->year;
        }
        if (request()->month != '不限') {
            $search['month'] = request()->month;
        }
        $uid = \YunShop::app()->uid;
        $query = CoreAttach::where(['uniacid'=>0,'type'=>1])->orderby('id', 'desc');
        if ($uid && $uid != 1) {
            $query->where('uid', $uid);
        }
        if ($search['year'] || $search['month']) {
            $start_time = Carbon::createFromDate($search['year'], $search['month'])->startOfMonth()->timestamp;
            $end_time = Carbon::createFromDate($search['year'], $search['month'])->endOfMonth()->timestamp;
            $query->whereBetween('created_at', [$start_time, $end_time]);
        }
        $list = $query->paginate()->toArray();
        foreach ($list['data'] as $k => $v) {
            if ($v['attachment'] && $v['id']) {
                $data['data'][$k]['id'] = $v['id'];
                $data['data'][$k]['url'] = yz_tomedia($v['attachment']);
            }
        }
        $data['total'] = $list['total'];
        $data['per_page'] = $list['per_page'];
        $data['last_page'] = $list['last_page'];
        $data['prev_page_url'] = $list['prev_page_url'];
        $data['next_page_url'] = $list['next_page_url'];
        $data['current_page'] = $list['current_page'];
        $data['from'] = $list['from'];
        $data['to'] = $list['to'];
        if (!$data['data']) {
            $data['data'] = [];
        }
        return $this->successJson('获取成功', $data);
    }

    public function delLocalImg()
    {
        $id = request()->id;
        $core = CoreAttach::find($id);
        if (!$core) {
            return $this->errorJson('请重新选择');
        }
        $setting = SystemSetting::settingLoad('remote', 'system_remote');

        if ($core['upload_type']) {
            $remote_url = '';
            if ($setting['type'] == 2) {
                $remote_url = $setting['alioss']['url'];
            }
            if ($setting['type'] == 4) {
                $remote_url = $setting['cos']['url'];
            }
            if ($remote_url && strexists($core['attachment'], $remote_url)) {
                $str_len = strlen($remote_url);
                $core['attachment'] = substr($core['attachment'], $str_len+1);
            }
            $status = file_remote_delete($core['attachment'], $core['upload_type'], $setting);
        } else {
            $status = file_delete($core['attachment']);
        }
        if ($core->delete()) {
            return $this->successJson('删除成功');
        }
        return $this->errorJson('删除失败');
    }

    //上传记录表
    public function getData($originalName, $file_type, $newFileName, $save_type)
    {
        //存储至数据表中
        $core = new CoreAttach;
        switch ($file_type) {
        	case 'syst_images':
        		$type = 1;
        		break;
        	case 'audios':
        		$type = 2;
        		break;
        	default:
        		$type = 3;
        		break;
        }
        $d = [
            'uniacid' => $this->getUniacid(),
            'uid' => \Auth::guard('admin')->user()->uid,
            'filename' => $originalName,
            'type' => $type, //类型1.图片; 2.音乐
            'attachment' => $newFileName,
            'upload_type' => $save_type
        ];
        $core->fill($d);
        $validate = $core->validator();
        if (!$validate->fails()) {
            if ($core->save()) {
                return 1;
            }
        }
        return $validate->messages();
    }
}