<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2022/1/7
 * Time: 11:54
 */

namespace app\frontend\modules\wechat\controllers;


use app\common\components\BaseController;
use app\frontend\modules\wechat\services\MediaService;

class MediaController extends BaseController
{
    public function getTemporary()
    {
        $media = new MediaService();
        $media->mediaId = request()->media_id;
        $media->plugin = request()->plugin;
        $result = $media->getTemporaryFile();
        if ($result['code']) {
            return $this->errorJson($result['message']);
        }
        return $this->successJson('ok',$result['file']);
    }
}