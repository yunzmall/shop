<?php 

namespace app\platform\modules\Application\controllers;

use app\platform\controllers\BaseController;
use app\platform\modules\system\models\SystemSetting;
use app\platform\modules\application\models\CoreAttach;
use app\common\services\qcloud\Api;
use app\common\services\aliyunoss\OssClient;
use app\common\services\aliyunoss\OSS\Core\OssException;
use app\common\services\ImageZip;

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
        if (count($file) > 6) {
            return $this->errorJson('文件数量过多, 请选择低于6个文件');
        }

        $success = [];
        if (count($file) > 1 && count($file) < 7) {
            //多文件上传
            foreach ($file as $k => $v) {
                if ($v) {
                    $url = $this->doUpload($v);
                    $success[] = $url;
                }
            }
        } else {
            $success = $this->doUpload($file);
        }

        return $this->successJson('ok', ['success' => $success, 'fail' => '']);
    }
    
    public function doUpload($file)
    {
    	if (!$file->isValid()) {
            return false;
        }

        if ($file->getClientSize() > 30*1024*1024) {
            return '上传资源过大';
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

        $ext = $file->getClientOriginalExtension();

        $originalName = $file->getClientOriginalName();

        $realPath = $file->getRealPath();

        $merge_ext = array_merge($defaultImgType, $defaultAudioType, $defaultVideoType);
        if (!in_array($ext, $merge_ext)) {
            return '非规定类型的文件格式';
        }

        if (in_array($ext, $defaultImgType)) {
            $file_type = 'images';
        } elseif (in_array($ext, $defaultAudioType)) {
            $file_type = 'audios';
        } elseif (in_array($ext, $defaultVideoType)) {
            $file_type = 'videos'; 
        }

        $newFileName = $this->getNewFileName($originalName, $ext, $file_type);

        $setting = SystemSetting::settingLoad('global', 'system_global');
        $remote = SystemSetting::settingLoad('remote', 'system_remote');

        if (in_array($ext, $defaultImgType)) {
            if ($setting['image_extentions'] && !in_array($ext, array_filter($setting['image_extentions'])) ) {
                return '非规定类型的文件格式';
            }

            $defaultImgSize = $setting['img_size'] ? $setting['img_size'] * 1024 : 1024*1024*5; //默认大小为5M

            if ($file->getClientSize() > $defaultImgSize) {
                return '文件大小超出规定值';
            }
        }

        if (in_array($ext, $defaultAudioType) || in_array($ext, $defaultVideoType)) {
            if ($setting['audio_extentions'] && !in_array($ext, array_filter($setting['audio_extentions'])) ) {
                return '非规定类型的文件格式';
            }
            $defaultAudioSize = $setting['audio_limit'] ? $setting['audio_limit'] * 1024 : 1024*1024*30; //音视频最大 30M

            if ($file->getClientSize() > $defaultAudioSize) {
                    \Log::info('local_audio_video_file_size_is_not_set_size');
                return '文件大小超出规定值';
            }
        }
        $file_type = $file_type == 'images' ? 'syst_images' : $file_type;

        //执行本地上传
        $local_res = \Storage::disk($file_type)->put($newFileName, file_get_contents($realPath));
        if (!$local_res) {
            return '本地上传失败';
        }

        $url = \Storage::disk($file_type)->url($newFileName);

        if ($setting['image']['zip_percentage']) {
            //执行图片压缩
            $imagezip = new ImageZip();
            $imagezip->makeThumb(
                yz_tomedia($url),
                yz_tomedia($url),
                $setting['image']['zip_percentage']
            );
        }
        
        if ($setting['thumb_width'] == 1 && $setting['thumb_width']) {
        	$imagezip = new ImageZip();
        	$imagezip->makeThumb(
        		yz_tomedia($url),
        		yz_tomedia($url),
        		$setting['thumb_width']
        	);
        }

        if ($remote['type'] != 0) { //远程上传
       		$res = file_remote_upload($url, true, $remote);
        }

        if (!$res || $local_res) {
        	//数据添加
        	$this->getData($originalName, $file_type, $url, $remote['type']);
       		return yz_tomedia($url);
        }
        return $res;
    }

    /**
     * 获取新文件名
     * @param  string $originalName 原文件名
     * @param  string $ext          文件扩展名
     * @return string               新文件名
     */
    public function getNewFileName($originalName, $ext, $file_type)
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
        $core = new CoreAttach();

        if (request()->year != '不限') {
            $search['year'] = request()->year;
        }

        if(request()->month != '不限') {
            $search['month'] = request()->month;
        }

        $core = $core->where('uniacid', 0)->where('type', 1)->orderBy('id', 'desc');

        if ($search) {
            $core = $core->search($search);
        }

        $list = $core->paginate()->toArray();

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

        if ($core['upload_type']== 2) { //oss
            try {
                $oss = new OssClient($setting['alioss']['key'], $setting['alioss']['secret'], $setting['alioss']['ossurl']);
            } catch (OssException $e) {
                return $this->errorJson($e->getErrorMessage());
            }

            $ossbucket = rtrim(substr($setting['alioss']['bucket'], 0, strrpos($setting['alioss']['bucket'],'@')), '@');
            $res = $oss->deleteObject($ossbucket, $core['attachment']); //info['url'] 

            if (!$res['info']['url']) {
                return $res;
            }

        } elseif ($core['upload_type'] == 4) { //cos
            try {

	            $cos = new Api([
	                'app_id' => $setting['cos']['appid'],
	                'secret_id' => $setting['cos']['secretid'],
	                'secret_key' => $setting['cos']['secretkey'],
	                'region' => $setting['cos']['url']
	            ]);
            	
            	$res = $cos->delFile($setting['cos']['bucket'], $core['attachment']); //[code =0  'message'='SUCCESS']
            } catch (\Exception $e) {
            	return $this->errorJson('腾讯云配置错误');
            }

            if ($res['code'] != 0 || $res['message'] != 'SUCCESS') {
                //删除失败
                return $res;
            }

        } else {
            //删除文件
            $res = \app\common\services\Storage::remove(yz_tomedia($core['attachment']));
            if ($res !== true) {
                \Log::info('本地图片删除失败', $core['attachment']);
            }
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