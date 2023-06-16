<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-02-04
 * Time: 10:10
 *
 *    .--,       .--,
 *   ( (  \.---./  ) )
 *    '.__/o   o\__.'
 *       {=  ^  =}
 *        >  -  <
 *       /       \
 *      //       \\
 *     //|   .   |\\
 *     "'\       /'"_.-~^`'-.
 *        \  _  /--'         `
 *      ___)( )(___
 *     (((__) (__)))     梦之所想,心之所向.
 */

namespace app\backend\modules\upload\controllers;


use app\backend\modules\upload\models\CoreAttach;
use app\common\components\BaseController;
use app\common\exceptions\ShopException;
use app\common\services\ImageZip;
use app\common\services\upload\UploadService;
use app\platform\modules\system\models\SystemSetting;
use getID3;
class UploadController extends BaseController
{
    protected $isPublic = true;
    protected $uniacid;

    public function __construct()
    {
        $this->uniacid = \YunShop::app()->uniacid ?: 0;
    }

    public function upload()
    {
        $file = request()->file('file');
        $type = request()->upload_type;
        if (!$file) {
            return $this->errorJson('文件上传失败.');
        }
        if (!$file->isValid()) {
            return $this->errorJson('文件上传失败.');
        }
        $auth_uid = \Auth::guard('admin')->user()->uid?:1;
        global $_W;
        // 获取文件相关信息
        $originalName = $file->getClientOriginalName(); // 文件原名
        $realPath = $file->getRealPath();   //临时文件的绝对路径
        $ext = $file->getClientOriginalExtension(); //文件后缀
        $uploadService = new UploadService();
        $upload_setting = $uploadService->getSetting();
        if ($type == 'image') {
            try {
                $upload_res = $uploadService->upload($file, $type);
            } catch (ShopException $exception) {
                return $this->errorJson($exception->getMessage());
            }
            if (config('app.framework') == 'platform') {
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => $auth_uid,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 1,
                    'module_upload_dir' => '',
                    'group_id' => intval($this->uniacid),
                    'upload_type' => $upload_setting['remote']['type'],
                    'tag_id' => 0
                ];
                \app\platform\modules\application\models\CoreAttach::create($data);
                return $this->successJson('上传成功', [
                    'name' => $originalName,
                    'ext' => $ext,
                    'filename' => $upload_res['file_name'],
                    'attachment' => $upload_res['relative_path'],
                    'url' => $upload_res['absolute_path'],
                    'is_image' => 1,
                    'filesize' => 'null',
                    'group_id' => intval($this->uniacid)
                ]);
            } else {
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => isset($_W['uid']) ? $_W['uid'] : 1,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 1,
                    'createtime' => TIMESTAMP,
                    'module_upload_dir' => '',
                    'group_id' => 0,
                ];
                CoreAttach::create($data);
                $info = array(
                    'name' => $originalName,
                    'ext' => $ext,
                    'filename' => $upload_res['file_name'],
                    'attachment' => $upload_res['relative_path'],
                    'url' => $upload_res['absolute_path'],
                    'is_image' => 1,
                    'filesize' => 'null',
                );
                if (request()->is_interface == 1) {
                    return $this->successJson('上传成功',$info);
                }
                $info['state'] = 'SUCCESS';
                die(json_encode($info));
            }
        } elseif ($type == 'video') {
            try {
                $upload_res = $uploadService->upload($file, $type, 'videos');
            } catch (ShopException $exception) {
                return $this->errorJson($exception->getMessage());
            }
            if (config('app.framework') == 'platform') {
                $getID3 = new getID3();
                $ThisFileInfo = $getID3->analyze($realPath); //分析文件，$path为音频文件的地址
                $timeline = $ThisFileInfo['playtime_seconds']; //这个获得的便是音频文件的时长
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => $auth_uid,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 3,
                    'module_upload_dir' => '',
                    'group_id' => intval($this->uniacid),
                    'upload_type' => $upload_setting['remote']['type'],
                    'timeline' => $timeline
                ];
                \app\platform\modules\application\models\CoreAttach::create($data);
                return $this->successJson('上传成功', [
                    'name' => $originalName,
                    'ext' => $ext,
                    'filename' => $upload_res['file_name'],
                    'attachment' => $upload_res['relative_path'],
                    'url' => $upload_res['absolute_path'],
                    'is_image' => 0,
                    'filesize' => 'null',
                    'group_id' => intval($this->uniacid)
                ]);
            } else {
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => isset($_W['uid']) ? $_W['uid'] : 1,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 3,
                    'createtime' => TIMESTAMP,
                    'module_upload_dir' => '',
                    'group_id' => 0,
                ];
                CoreAttach::create($data);
                $info = array(
                    'name' => $originalName,
                    'ext' => $ext,
                    'filename' => $upload_res['file_name'],
                    'attachment' => $upload_res['relative_path'],
                    'url' => $upload_res['absolute_path'],
                    'is_image' => 0,
                    'filesize' => 'null',
                );
                if (request()->is_interface == 1) {
                    return $this->successJson('上传成功',$info);
                }
                $info['state'] = 'SUCCESS';
                die(json_encode($info));
            }
        } elseif ($type == 'audio') {
            try {
                $upload_res = $uploadService->upload($file, $type, 'audios');
            } catch (ShopException $exception) {
                return $this->errorJson($exception->getMessage());
            }
            if (config('app.framework') == 'platform') {
                $getID3 = new getID3();
                $ThisFileInfo = $getID3->analyze($realPath); //分析文件，$path为音频文件的地址
                $timeline = $ThisFileInfo['playtime_seconds']; //这个获得的便是音频文件的时长
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => $auth_uid,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 3,
                    'module_upload_dir' => '',
                    'group_id' => intval($this->uniacid),
                    'upload_type' => $upload_setting['remote']['type'],
                    'timeline' => $timeline,
                    'tag_id' => 0
                ];
                \app\platform\modules\application\models\CoreAttach::create($data);
                return $this->successJson('上传成功', [
                    'name' => $originalName,
                    'ext' => $ext,
                    'filename' => $upload_res['file_name'],
                    'attachment' => $upload_res['relative_path'],
                    'url' => $upload_res['absolute_path'],
                    'is_image' => 0,
                    'filesize' => 'null',
                    'group_id' => intval($this->uniacid)
                ]);
            } else {
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => isset($_W['uid']) ? $_W['uid'] : 1,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 3,
                    'createtime' => TIMESTAMP,
                    'module_upload_dir' => '',
                    'group_id' => 0,
                    'tag_id' => 0
                ];
                CoreAttach::create($data);
                $info = array(
                    'name' => $originalName,
                    'ext' => $ext,
                    'filename' => $upload_res['file_name'],
                    'attachment' => $upload_res['relative_path'],
                    'url' => $upload_res['absolute_path'],
                    'is_image' => 0,
                    'filesize' => 'null',
                );
                $info['state'] = 'SUCCESS';
                die(json_encode($info));
            }
        }
        return true;
    }

    public function fetch()
    {
        $url = trim(request()->url);
        $resp = ihttp_get($url);
        if (!$resp) {
            return $this->errorJson('提取文件失败');
        }
        if (strexists($resp['headers']['Content-Type'], 'image')) {
            switch ($resp['headers']['Content-Type']) {
                case 'application/x-jpg':
                case 'image/jpeg':
                    $ext = 'jpg';
                    break;
                case 'image/png':
                    $ext = 'png';
                    break;
                case 'image/gif':
                    $ext = 'gif';
                    break;
                default:
                    return $this->errorJson('提取资源失败, 资源文件类型错误.');
                    break;
            }
        } else {
            return $this->errorJson('提取资源失败, 仅支持图片提取.');
        }
        $originName = pathinfo($url, PATHINFO_BASENAME);
        $newOriginalName = md5($originName . str_random(6)) . '.' . $ext;
        //本地上传
        $result = \Storage::disk('image')->put($newOriginalName, $resp['content']);
        if (!$result) {
            return $this->successJson('上传失败');
        }
        $url = \Storage::disk('image')->url($newOriginalName);
        if (config('app.framework') == 'platform') {
            $remote = SystemSetting::settingLoad('remote', 'system_remote');
            $data = [
                'uniacid' => $this->uniacid,
                'uid' => \Auth::guard('admin')->user()->uid,
                'filename' => $newOriginalName,
                'attachment' => $url,
                'type' => 1,
                'module_upload_dir' => '',
                'group_id' => intval($this->uniacid),
                'upload_type' => $remote['type'],
                'tag_id' => 0
            ];
            \app\platform\modules\application\models\CoreAttach::create($data);
            //远程上传
            if ($remote['type'] != 0) {
                file_remote_upload($url, true, $remote);
            }
            return $this->successJson('上传成功', [
                'img' => $url,
                'img_url' => yz_tomedia($url),
            ]);
        } else {
            //全局配置
            global $_W;
            //公众号独立配置信息 优先使用公众号独立配置
            $uni_setting = app('WqUniSetting')->get()->toArray();
            if (!empty($uni_setting['remote']) && iunserializer($uni_setting['remote'])['type'] != 0) {
                $setting['remote'] = iunserializer($uni_setting['remote']);
                $remote = $setting['remote'];
            } else {
                $remote = $_W['setting']['remote'];
            }
            $data = [
                'uniacid' => $this->uniacid,
                'uid' => $_W['uid'],
                'filename' => $newOriginalName,
                'attachment' => $url,
                'type' => 1,
                'createtime' => TIMESTAMP,
                'module_upload_dir' => '',
                'group_id' => 0,
                'tag_id' => 0
            ];
            CoreAttach::create($data);
            //远程上传
            if ($remote['type'] != 0) {
                file_remote_upload_wq($url, true, $remote);
            }
            return $this->successJson('上传成功', [
                'img' => $url,
                'img_url' => yz_tomedia($url),
            ]);
        }
    }

    public function getImage()
    {
        if (config('app.framework') == 'platform') {
            $result = $this->getNewImage();
        } else {
            $result = $this->getWqImage();
        }

        return $this->successJson('ok', $result);
    }

    public function getWqImage()
    {
        $uid = \Auth::guard('admin')->user()->uid;
        $year = request()->year;
        $month = intval(request()->month);
        $page = max(1, intval(request()->page));
        $groupid = intval(request()->group_id);
        $page_size = 33;
        if ($page <= 1) {
            $page = 0;
            $offset = ($page)*$page_size;
        } else {
            $offset = ($page-1)*$page_size;
        }
        $core_attach = new CoreAttach;
        $core_attach = $core_attach->where(['uniacid'=>$this->uniacid,'type'=>1]);
        if (\YunShop::app()->isfounder !== true) {
            $core_attach = $core_attach->where('uid', $uid);
        }
        if ($groupid > 0) {
            $core_attach = $core_attach->where('group_id', $groupid);
        }
        if ($groupid == 0) {
            $core_attach = $core_attach->where('group_id', -1);
        }
        if ($year || $month) {
            $start_time = $month ? strtotime("{$year}-{$month}-01") : strtotime("{$year}-1-01");
            $end_time = $month ? strtotime('+1 month', $start_time) : strtotime('+12 month', $start_time);
            $core_attach = $core_attach->where('createtime', '>=', $start_time)->where('createtime', '<=', $end_time);
        }
        $count = $core_attach->count();
        $core_attach = $core_attach->orderby('createtime', 'desc')->offset($offset)->limit($page_size)->get();
        foreach ($core_attach as &$item) {
            $item['attach'] = yz_tomedia($item['attachment'], true);
            $item['url'] = $item['attach'];
        }
        if (request()->is_interface == 1) {
            return $this->successJson('上传成功',$core_attach);
        }
        $pager = pagination($count, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => '1'));
        $result = array('items' => $core_attach, 'pager' => $pager);
        iajax(0, $result);
    }

    public function getNewImage()
    {
        $uid = \Auth::guard('admin')->user()->uid;
        $search = [];
        if (request()->year != '不限') {
            $search['year'] = request()->year;
        }
        if(request()->month != '不限') {
            $search['month'] = request()->month;
        }
        $query = \app\platform\modules\application\models\CoreAttach::search($search)->where('type', 1);
        if (\YunShop::app()->isfounder !== true) {
            $query->where('uid', $uid);
        }
        //type = 1 图片
        $core_attach = $query->orderby('created_at', 'desc')->paginate(33);
        foreach ($core_attach as &$item) {
            $item['url'] = yz_tomedia($item['attachment']);
            unset($item['uid']);
        }
        return $core_attach;
    }

    public function getVideo()
    {
        if (config('app.framework') == 'platform') {
            $uid = \Auth::guard('admin')->user()->uid;
            $search = [];
            if (request()->year != '不限') {
                $search['year'] = request()->year;
            }
            if (request()->month != '不限') {
                $search['month'] = request()->month;
            }
            $query = \app\platform\modules\application\models\CoreAttach::search($search)->where(['type'=>3]);
            if (\YunShop::app()->isfounder !== true) {
                $query->where('uid', $uid);
            }
            //type = 3 视频
            $core_attach = $query->orderby('created_at', 'desc')->paginate(33);
            foreach ($core_attach as &$item) {
                $item['url'] = yz_tomedia($item['attachment']);
                unset($item['uid']);
            }
            return $this->successJson('ok', $core_attach);
        }
        global $_W;
        $uid = $_W['uid'];
        $core_attach = new CoreAttach();
        $page_index = max(1, request()->page);
        $page_size = 5;
        if ($page_index <=1) {
            $page_index = 0;
            $offset = $page_index * $page_size;
        } else {
            $offset = ($page_index - 1) * $page_size;
        }
        if (\YunShop::app()->isfounder !== true) {
            $core_attach = $core_attach->where('uid', $uid);
        }
        $total = $core_attach->count();
        $core_attach = $core_attach->where(['type'=>3,'uniacid'=>$this->uniacid])->orderby('createtime', 'desc')->offset($offset)->limit(24)->get();
        foreach ($core_attach as &$item) {
            $item['url'] = yz_tomedia($item['attachment']);
            unset($item['uid']);
        }
        $pager = pagination($total, 1, 24, '', $context = array('before' => 5, 'after' => 4, 'isajax' => '1'));
        $result = array('items' => $core_attach, 'pager' => $pager);
        iajax(0, $result);die;
    }

    public function delete()
    {
        $id = intval(request()->id);
        if (config('app.framework') == 'platform') {
            $remote = SystemSetting::settingLoad('remote', 'system_remote');
            $core_attach = \app\platform\modules\application\models\CoreAttach::where('id', $id);
        } else {
            global $_W;
            $remote = $_W['setting']['remote'];
            $core_attach = CoreAttach::where('id', $id);
        }
        $core_attach = $core_attach->first();
        if ($core_attach['upload_type']) {
            $remote_url = '';
            if ($remote['type'] == 2) {
                $remote_url = $remote['alioss']['url'];
            }
            if ($remote['type'] == 4) {
                $remote_url = $remote['cos']['url'];
            }
            if ($remote_url && strexists($core_attach['attachment'], $remote_url)) {
                $str_len = strlen($remote_url);
                $core_attach['attachment'] = substr($core_attach['attachment'], $str_len+1);
            }
            $status = file_remote_delete($core_attach['attachment'], $core_attach['upload_type'], $remote);
        } else {
            $status = file_delete($core_attach['attachment']);
        }
        if ($core_attach->delete()) {
            return $this->successJson('删除成功');
        } else {
            return $this->errorJson('删除数据表数据失败');
        }
    }

    public function jsUpload()
    {
        $file = request()->file('file');
        $type = request()->upload_type;
        if (!$file) {
            return $this->errorJson('请传入正确参数.');
        }
        if (!$file->isValid()) {
            return $this->errorJson('上传失败.');
        }
        $auth_uid = \Auth::guard('admin')->user()->uid ?: 1;
        global $_W;
        // 获取文件相关信息
        $originalName = $file->getClientOriginalName(); // 文件原名
        $realPath = $file->getRealPath();   //临时文件的绝对路径
        $ext = $file->getClientOriginalExtension(); //文件后缀
        $uploadService = new UploadService();
        $upload_setting = $uploadService->getSetting();
        if ($type == 'image') {
            $upload_res = $uploadService->upload($file, $type);
            if (config('app.framework') == 'platform') {
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => $auth_uid,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 1,
                    'module_upload_dir' => '',
                    'group_id' => intval($this->uniacid),
                    'upload_type' => $upload_setting['remote']['type'],
                    'tag_id' => 0
                ];
                \app\platform\modules\application\models\CoreAttach::create($data);
            } else {
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => isset($_W['uid']) ? $_W['uid'] : 1,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 1,
                    'createtime' => TIMESTAMP,
                    'module_upload_dir' => '',
                    'group_id' => 0,
                ];
                CoreAttach::create($data);
            }
            $info = [
                'name' => $originalName,
                'ext' => $ext,
                'filename' => $upload_res['file_name'],
                'attachment' => $upload_res['relative_path'],
                'url' => $upload_res['absolute_path'],
                'is_image' => 1,
                'filesize' => 'null',
                'group_id' => intval($this->uniacid),
                'state' => 'SUCCESS'
            ];
            die(json_encode($info));
        } elseif ($type == 'video') {
            $upload_res = $uploadService->upload($file, $type, 'videos');
            if (config('app.framework') == 'platform') {
                $getID3 = new getID3();
                $ThisFileInfo = $getID3->analyze($realPath); //分析文件，$path为音频文件的地址
                $timeline = $ThisFileInfo['playtime_seconds']; //这个获得的便是音频文件的时长
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => $auth_uid,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 3,
                    'module_upload_dir' => '',
                    'group_id' => intval($this->uniacid),
                    'upload_type' => $upload_setting['remote']['type'],
                    'timeline' => $timeline
                ];
                \app\platform\modules\application\models\CoreAttach::create($data);
            } else {
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => isset($_W['uid']) ? $_W['uid'] : 1,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 3,
                    'createtime' => TIMESTAMP,
                    'module_upload_dir' => '',
                    'group_id' => 0,
                ];
                CoreAttach::create($data);
            }
            $info = [
                'name' => $originalName,
                'ext' => $ext,
                'filename' => $upload_res['file_name'],
                'attachment' => $upload_res['relative_path'],
                'url' => $upload_res['absolute_path'],
                'is_image' => 0,
                'filesize' => 'null',
                'group_id' => intval($this->uniacid),
                'state' => 'SUCCESS'
            ];
            die(json_encode($info));
        } elseif ($type == 'audio') {
            $upload_res = $uploadService->upload($file, $type, 'audios');
            if (config('app.framework') == 'platform') {
                $getID3 = new getID3();
                $ThisFileInfo = $getID3->analyze($realPath); //分析文件，$path为音频文件的地址
                $timeline = $ThisFileInfo['playtime_seconds']; //这个获得的便是音频文件的时长
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => $auth_uid,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 3,
                    'module_upload_dir' => '',
                    'group_id' => intval($this->uniacid),
                    'upload_type' => $upload_setting['remote']['type'],
                    'timeline' => $timeline,
                    'tag_id' => 0
                ];
                \app\platform\modules\application\models\CoreAttach::create($data);
            } else {
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => isset($_W['uid']) ? $_W['uid'] : 1,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 3,
                    'createtime' => TIMESTAMP,
                    'module_upload_dir' => '',
                    'group_id' => 0,
                    'tag_id' => 0
                ];
                CoreAttach::create($data);
            }
            $info = [
                'name' => $originalName,
                'ext' => $ext,
                'filename' => $upload_res['file_name'],
                'attachment' => $upload_res['relative_path'],
                'url' => $upload_res['absolute_path'],
                'is_image' => 0,
                'filesize' => 'null',
                'group_id' => intval($this->uniacid),
                'state' => 'SUCCESS',
            ];
            die(json_encode($info));
        }
        return true;
    }

    public function jsImage()
    {
        $year = intval(request()->year);
        $month = intval(request()->month);
        $page = max(1, intval(request()->page));
        $page_size = 24;
        if ($page <= 1) {
            $page = 0;
            $offset = $page * $page_size;
        } else {
            $offset = ($page - 1) * $page_size;
        }
        if (config('app.framework') == 'platform') {
            $core_attach = new \app\platform\modules\application\models\CoreAttach();
            $group_id = $this->uniacid;
            $create_time = 'created_at';
            $uid = \Auth::guard('admin')->user()->uid ?: 1;
        } else {
            $core_attach = new CoreAttach;
            $group_id = 0;
            $create_time = 'createtime';
            global $_W;
            $uid = $_W['uid'];
        }
        $core_attach = $core_attach->where(['uniacid'=>$this->uniacid,'group_id'=>$group_id,'type'=>1]);
        if (\YunShop::app()->isfounder !== true) {
            $core_attach = $core_attach->where('uid', $uid);
        }
        if ($year || $month) {
            $start_time = $month ? strtotime("{$year}-{$month}-01") : strtotime("{$year}-1-01");
            $end_time = $month ? strtotime('+1 month', $start_time) : strtotime('+12 month', $start_time);
            $core_attach = $core_attach->whereBetween($create_time, [$start_time, $end_time]);
        }
        $count = $core_attach->count();
        $core_attach = $core_attach->orderby($create_time, 'desc')->offset($offset)->limit($page_size)->get();
        foreach ($core_attach as &$item) {
            $item['url'] = yz_tomedia($item['attachment']);
            $item['attach'] = yz_tomedia($item['attachment']);
        }
        $pager = pagination($count, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => '1'));
        $result = array('items' => $core_attach, 'pager' => $pager);
        $array = [
            'message' => [
                'erron' => 0,
                'message' => $result
            ],
            'redirect' => '',
            'type' => 'ajax'
        ];
        die(json_encode($array));
    }
}