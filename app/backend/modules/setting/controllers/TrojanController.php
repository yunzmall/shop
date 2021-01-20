<?php


namespace app\backend\modules\setting\controllers;


use app\common\components\BaseController;
use Illuminate\Filesystem\Filesystem;

class TrojanController extends BaseController
{

    public function check()
    {
        if(request()->ajax()){
            $del_files = [];
            if (config('app.framework') != 'platform') {
                $path = base_path() . '/../../attachment/image';
            } else {
                $path = base_path('static/upload');
            }

            if (request()->trojan == 'check') {

                $filesystem = app(Filesystem::class);

                $files = $filesystem->allFiles($path);
                foreach ($files as $item) {
                    if ($item->getExtension() == 'php') {
                        $del_files[] = $item->getPathname();
                    }
                }
            }
            return $this->successJson('请求接口成功',[
                'files' => $del_files,
                'del_file' => implode('|', $del_files)
            ]);
        }

        return view('setting.trojan.check');
    }


    public function del()
    {
        $files = request()->files;
        if (!empty($files)) {
            $filesystem = app(Filesystem::class);
            foreach ($files as $file) {
                if (!$filesystem->delete($file)) {
                    return $this->errorJson('删除失败');
                }
            }

            return $this->successJson('删除成功');
        }

        return $this->errorJson('删除失败');
    }
}