<?php
namespace app\backend\modules\upload\controllers;


use app\backend\modules\upload\models\CoreAttach;
use app\common\components\BaseController;
use app\common\exceptions\ShopException;
use app\common\services\ImageZip;
use app\common\services\upload\UploadService;
use app\platform\modules\application\models\CoreAttachTags;
use app\platform\modules\system\models\SystemSetting;
use getID3;
class UploadV3Controller extends BaseController
{
    protected $isPublic = true;
    protected $uniacid;
    protected $set;

    public function __construct()
    {
        $this->uniacid = \YunShop::app()->uniacid ?: 0;
        $this->set = UploadService::getSetting();
    }

    public function upload()
    {
        $file = request()->file('file');
        $type = request()->upload_type;
        $tagId = request()->tag_id;
        if (!$file) {
            return $this->errorJson('文件上传失败.');
        }
        if (!$file->isValid()) {
            return $this->errorJson('文件上传失败.');
        }
        // 获取文件相关信息
        $originalName = $file->getClientOriginalName(); // 文件原名
        $realPath = $file->getRealPath();   //临时文件的绝对路径
        $ext = $file->getClientOriginalExtension(); //文件后缀
        $uploadService = new UploadService();
        $upload_setting = $uploadService->getSetting();
        $auth_uid = \YunShop::app()->uid?:1;
        global $_W;
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
                    'group_id' => (int)$this->uniacid,
                    'upload_type' => $upload_setting['remote']['type'],
                    'tag_id' => $tagId
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
                    'tag_id' => $tagId
                ];
                CoreAttach::create($data);
            }
            return $this->successJson('上传成功', [
                'name' => $originalName,
                'ext' => $ext,
                'filename' => $upload_res['file_name'],
                'attachment' => $upload_res['relative_path'],
                'url' => $upload_res['absolute_path'],
                'is_image' => 1,
                'filesize' => 'null',
                'group_id' => (int)$this->uniacid,
                'state' => 'SUCCESS'
            ]);
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
                    'group_id' => (int)$this->uniacid,
                    'upload_type' => $upload_setting['remote']['type'],
                    'tag_id' => $tagId,
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
                    'group_id' => (int)$this->uniacid,
                    'timeline' => $timeline
                ]);
            } else {
                $getID3 = new getID3();
                $ThisFileInfo = $getID3->analyze($realPath); //分析文件，$path为音频文件的地址
                $timeline=$ThisFileInfo['playtime_seconds']; //这个获得的便是音频文件的时长
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => isset($_W['uid']) ? $_W['uid'] : 1,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 3,
                    'createtime' => TIMESTAMP,
                    'module_upload_dir' => '',
                    'group_id' => 0,
                    'tag_id' => $tagId,
                    'timeline' => $timeline
                ];
                CoreAttach::create($data);
                return $this->successJson('上传成功', [
                    'name' => $originalName,
                    'ext' => $ext,
                    'filename' => $upload_res['file_name'],
                    'attachment' => $upload_res['relative_path'],
                    'url' => $upload_res['absolute_path'],
                    'is_image' => 0,
                    'filesize' => 'null',
                    'group_id' => (int)$this->uniacid,
                ]);
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
                    'type' => 2,
                    'module_upload_dir' => '',
                    'group_id' => (int)$this->uniacid,
                    'upload_type' => $upload_setting['remote']['type'],
                    'tag_id' => $tagId,
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
                    'group_id' => (int)$this->uniacid,
                    'timeline' => $timeline
                ]);
            } else {
                $getID3 = new getID3();
                $ThisFileInfo = $getID3->analyze($realPath); //分析文件，$path为音频文件的地址
                $timeline = $ThisFileInfo['playtime_seconds']; //这个获得的便是音频文件的时长
                $data = [
                    'uniacid' => $this->uniacid,
                    'uid' => isset($_W['uid']) ? $_W['uid'] : 1,
                    'filename' => safe_gpc_html(htmlspecialchars_decode($originalName, ENT_QUOTES)),
                    'attachment' => $upload_res['relative_path'],
                    'type' => 2,
                    'createtime' => TIMESTAMP,
                    'module_upload_dir' => '',
                    'group_id' => 0,
                    'tag_id' => $tagId,
                    'timeline' => $timeline
                ];
                CoreAttach::create($data);
                return $this->successJson('上传成功', [
                    'name' => $originalName,
                    'ext' => $ext,
                    'filename' => $upload_res['file_name'],
                    'attachment' => $upload_res['relative_path'],
                    'url' => $upload_res['absolute_path'],
                    'is_image' => 0,
                    'filesize' => 'null',
                    'group_id' => (int)$this->uniacid,
                ]);
            }
        } elseif ($type == 'file') {
            try {
                $upload_res = $uploadService->upload($file, $type, 'files');
            } catch (ShopException $exception) {
                return $this->errorJson($exception->getMessage());
            }
            return $this->successJson('上传成功', [
                'name' => $originalName,
                'ext' => $ext,
                'filename' => $upload_res['file_name'],
                'attachment' => $upload_res['relative_path'],
                'url' => $upload_res['absolute_path'],
                'is_image' => 0,
                'filesize' => 'null',
                'group_id' => (int)$this->uniacid,
            ]);
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
            $remote = $this->set['remote'];
            $data = [
                'uniacid' => $this->uniacid,
                'uid' => \YunShop::app()->uid,
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
            $remote = $this->set['remote'];
            $data = [
                'uniacid' => $this->uniacid,
                'uid' => isset($_W['uid']) ? $_W['uid'] : 1,
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
            $result = $this->getWqImageV2();
        }
        return $this->successJson('ok', $result);
    }

    public function getWqImageV2()
    {
        $year = request()->year;
        $month = intval(request()->month);
        $pageSize = request()->pageSize;
        $core_attach = new CoreAttach;
        $core_attach = $core_attach->where('uniacid', $this->uniacid);
        $tagId = request()->tag_id;
        if (is_numeric($tagId)) {
            if ($tagId === 0) {
                $core_attach = $core_attach->where(function($query) {
                    $query->where('tag_id', 0)->orWhere('tag_id', null);
                });
            } else {
                $core_attach = $core_attach->where('tag_id', $tagId);
            }
        }
        global $_W;
        if (\YunShop::app()->isfounder !== true) {
            $core_attach = $core_attach->where('uid', $_W['uid']?:1);
        }
        if ($year || $month) {
            $start_time = $month ? strtotime("{$year}-{$month}-01") : strtotime("{$year}-1-01");
            $end_time = $month ? strtotime('+1 month', $start_time) : strtotime('+12 month', $start_time);
            $core_attach = $core_attach->where('createtime', '>=', $start_time)->where('createtime', '<=', $end_time);
        }
        $core_attach = $core_attach->select('id','attachment')->where('type', 1);
        $core_attach = $core_attach->orderby('createtime', 'desc')->orderby('id', 'desc');
        $core_attach->search(request()->date);
        $core_attach = $core_attach->paginate($pageSize);
        if (!empty($core_attach)) {
            $core_attach = $core_attach->toArray();
        }
        foreach ($core_attach['data'] as &$attach) {
            $attach['attach'] = yz_tomedia($attach['attachment']);
            $attach['url'] = $attach['attach'];
        }
        return $core_attach;
    }

    public function getWqImage()
    {
        $year = request()->year;
        $month = intval(request()->month);
        $page = max(1, intval(request()->page));
        $page_size = 33;
        if ($page <= 1) {
            $page = 0;
            $offset = ($page)*$page_size;
        } else {
            $offset = ($page-1)*$page_size;
        }
        $core_attach = new CoreAttach;
        $core_attach = $core_attach->where('uniacid', $this->uniacid);
        global $_W;
        if (\YunShop::app()->isfounder !== true) {
            $core_attach = $core_attach->where('uid', $_W['uid']?:1);
        }
        if ($year || $month) {
            $start_time = $month ? strtotime("{$year}-{$month}-01") : strtotime("{$year}-1-01");
            $end_time = $month ? strtotime('+1 month', $start_time) : strtotime('+12 month', $start_time);
            $core_attach = $core_attach->where('createtime', '>=', $start_time)->where('createtime', '<=', $end_time);
        }
        $core_attach->search(request()->date);
        $core_attach = $core_attach->where('type', 1);
        $core_attach = $core_attach->orderby('createtime', 'desc');
        $count = $core_attach->count();
        $core_attach = $core_attach->offset($offset)->limit($page_size)->get();
        foreach ($core_attach as &$attach) {
            $attach['attach'] = yz_tomedia($attach['attachment']);
            $attach['url'] = $attach['attach'];
        }
        $pager = pagination($count, $page, $page_size,'',$context = array('before' => 5, 'after' => 4, 'isajax' => '1'));
        $result = array('items' => $core_attach, 'pager' => $pager);
        iajax(0, $result);
    }

    public function getNewImage()
    {
        $core_attach = new \app\platform\modules\application\models\CoreAttach();
        $pageSize = request()->pageSize;
        $core_attach = $core_attach->search(request()->date)->where('uniacid', $this->uniacid)->where('type', 1);
        if ($tagId = request()->tag_id AND is_numeric($tagId)) {
            if ($tagId === 0) {
                $core_attach = $core_attach->where(function($query) {
                    $query->where('tag_id', 0)->orWhere('tag_id', null);
                });
            } else {
                $core_attach = $core_attach->where('tag_id', $tagId);
            }
        }
        if (\YunShop::app()->isfounder !== true) {
            $core_attach = $core_attach->where('uid', \YunShop::app()->uid);
        }
        $core_attach = $core_attach->select('id','attachment','filename')
            ->orderby('created_at', 'desc')
            ->orderby('id', 'desc')
            ->paginate($pageSize);
        foreach ($core_attach as &$attach) {
            $attach['url'] = yz_tomedia($attach['attachment']);
        }
        return $core_attach;
    }

    public function getVideo()
    {
        $date = request()->date;
        $tag_id = request()->tag_id;
        $page_size = request()->pageSize;
        $search = ['year'=>$date['year'],'month'=>$date['month'],'tag_id'=>$tag_id];
        if (config('app.framework') == 'platform') {
            $core_attach = \app\platform\modules\application\models\CoreAttach::search($search);
            $core_attach = $core_attach->orderby('created_at', 'desc');
        } else {
            $core_attach = CoreAttach::search($search);
            $core_attach = $core_attach->orderby('createtime', 'desc');
        }
        $core_attach = $core_attach->where('type', 3)->paginate($page_size);
        foreach ($core_attach as &$attach) {
            $attach['url'] = yz_tomedia($attach['attachment']);
        }
        return $this->successJson('ok', $core_attach);
    }

    public function getAudio()
    {
        if (config('app.framework') == 'platform') {
            $core_attach = new \app\platform\modules\application\models\CoreAttach();
            if (request()->year != '不限') {
                $search['year'] = request()->year;
            }
            if (request()->month != '不限') {
                $search['month'] = request()->month;
            }
            $pageSize = request()->pageSize;
            $core_attach = $core_attach->search($search)->where('uniacid', $this->uniacid);
            $tagTitle = '';
            if ($tagId = request()->tag_id AND !empty($tagId)) {
                $core_attach->where('tag_id', $tagId);
                $tag = CoreAttachTags::find($tagId);
                $tagTitle = $tag?$tag->title:'';
            }
            if ($tagTitle != '未分组') {
                $core_attach = $core_attach->where('uid', \YunShop::app()->uid);
            }
            //type = 2 音频
            $core_attach = $core_attach->where('type', 2);
            $core_attach = $core_attach->orderby('created_at', 'desc')->paginate($pageSize);
            foreach ($core_attach as &$attach) {
                $attach['url'] = yz_tomedia($attach['attachment']);
            }
            return $this->successJson('ok', $core_attach);
        } else {
            $core_attach = new CoreAttach();
            $page_index = max(1, request()->page);
            $page_size = 5;
            if ($page_index <= 1) {
                $page_index = 0;
                $offset = $page_index * $page_size;
            } else {
                $offset = ($page_index - 1) * $page_size;
            }
            $core_attach = $core_attach->where(['type'=>2,'uniacid'=>$this->uniacid]);
            if (!$this->uniacid) {
                $core_attach = $core_attach->where('uid', \YunShop::app()->uid);
            }
            $total = $core_attach->count();
            if (request()->platform_type == '1') {
                $search['year'] = request()->year;
                $search['month'] = request()->month;
                $list = $core_attach->search($search)->orderby('createtime', 'desc')->paginate(8);
                $list->map(function ($l) {
                    $l->url = yz_tomedia($l->attachment);
                });
                return $this->successJson('ok', $list);
            }
            $core_attach = $core_attach->orderby('createtime', 'desc')->offset($offset)->limit(24)->get();
            foreach ($core_attach as &$attach) {
                $attach['url'] = yz_tomedia($attach['attachment']);
            }
            $pager = pagination($total, 1, 24, '', $context = array('before' => 5, 'after' => 4, 'isajax' => '1'));
            $result = array('items' => $core_attach, 'pager' => $pager);
            iajax(0, $result);
        }
        return true;
    }

    public function delete()
    {
        $id = request()->id;
        if (!is_array($id)) {
            $id = array(intval($id));
        }
        $id = safe_gpc_array($id);
        if (config('app.framework') == 'platform') {
            $core_attach = \app\platform\modules\application\models\CoreAttach::find($id);
            if ($core_attach['upload_type']) {
                $status = file_remote_delete($core_attach['attachment'], $core_attach['upload_type'], $this->set['remote']);
            } else {
                $status = file_delete($core_attach['attachment']);
            }
            if (is_error($status)) {
                return $this->errorJson($status['message']);
            }
            if (!$core_attach->delete()) {
                return $this->errorJson('删除数据表数据失败');
            }
            return $this->successJson('删除成功');
        } else {
            $core_attach = CoreAttach::find($id);
            if ($core_attach['upload_type']) {
                $status = file_remote_delete($core_attach['attachment']);
            } else {
                $status = file_delete($core_attach['attachment']);
            }
            if (is_error($status)) {
                return $this->errorJson($status['message']);
            }
            if (!$core_attach->delete()) {
                return $this->errorJson('删除数据表数据失败');
            }
            return $this->successJson('删除成功');
        }
    }
}