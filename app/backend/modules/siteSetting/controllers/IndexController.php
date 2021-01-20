<?php

namespace app\backend\modules\siteSetting\controllers;

use app\common\components\BaseController;
use app\common\facades\SiteSetting;
use app\common\facades\Setting;
use Illuminate\Filesystem\Filesystem;
use app\common\helpers\Url;

class IndexController extends BaseController
{
    public function index()
    {
        $setting = SiteSetting::get('base');
    
        return view('siteSetting.index', [
            'setting' => json_encode($setting),
        ])->render();
    }
    public function queue()
    {
        $setting = SiteSetting::get('queue');

        return view('siteSetting.queue', [
            'setting' => json_encode($setting),
        ])->render();
    }
    public function physicsPath()
    {
        $physics = request()->physics;
        if(request()->ajax() && $physics){
            $old_url = addslashes($physics['old_url']);

            $setModel = \app\common\models\Setting::uniacid()->where("value","like","%{$old_url}%")->get();

            if (!empty($setModel)) {
                foreach ($setModel as $kk=>$vv) {
                    $set = Setting::get($vv['group'].'.'.$vv['key']);

                    if ($vv['type'] == 'string') {

                        $set = str_replace($physics['old_url'],$physics['new_url'],$set);

                        Setting::set($vv['group'].'.'.$vv['key'],$set);

                    } elseif ($vv['type'] == 'array') {

                        foreach ($set as $key=>$value) {
                            $set[$key] = str_replace($physics['old_url'],$physics['new_url'],$value);
                        }

                        Setting::set($vv['group'].'.'.$vv['key'],$set);
                    }
                }

                \Artisan::call('config:cache');
                \Cache::flush();

                return $this->successJson(' 物理路径更新成功');

            } else {
                return $this->errorJson(' 没有对应的数据');
            }
        }
       
        return view('siteSetting.physics_path');
    }

    //redis配置  写入文件
    public function redisConfig()
    {
        if (config('app.APP_Framework', false) == 'platform') {
            $request = request()->redis;
            if(strtolower($request['password']) == 'null'){
                $pwd = '';
            }else{
                $pwd = $request['password'];
            }
            if($request){
                $str = '<?php
$redis = array();
                        
$redis[\'host\'] = \'' . $request['host']. '\';
$redis[\'password\'] = \'' . $pwd . '\';
$redis[\'port\'] = \'' . $request['port'] . '\';
$redis[\'database\'] = \'' . $request['database']. '\';
$redis[\'cache_database\'] = \'' . $request['cache_database']. '\';
';
                app(Filesystem::class)->put(base_path('database/redis.php'), $str);     //写入配置文件

                \Setting::set('redis', $request);
                \Artisan::call('config:clear');
                \Artisan::call('cache:clear');
                return $this->successJson('修改成功,如没生效可尝试清理缓存后重试');
            }
            $redis = array();
            if (file_exists(base_path('database/redis.php'))) {
                include base_path('database/redis.php');
            }
        }elseif(config('app.APP_Framework', false) != 'platform'){
            //微擎版
            $request = request()->redis;
            if($request){
                try{
                    app('redis')->select($request['database']);
                    app('redis')->select($request['cache_database']);
                }catch (\Exception $e){
                    //redis报错，不给修改
                    return $this->errorJson('修改失败,redis配置连接不成功,请检查填写的redis库');
                }

                $data = [
                    'REDIS_DEFAULT_DATABASE'=>$request['database'],
                    'REDIS_CACHE_DATABASE'=>$request['cache_database']
                ];
                $this->editEnvFile($data);
                \Artisan::call('config:clear');
                \Artisan::call('cache:clear');
                return $this->successJson('修改成功,如没生效可尝试清理缓存后重试');
            }
            $redis = [
                'database' => config('app.redis_default_database'),
                'cache_database' => config('app.redis_cache_database')
            ];
        }

        return view('siteSetting.redis_config',['set'=>json_encode($redis)]);
    }
    //MongoDB配置  写入文件
    public function mongoDBConfig()
    {
            $data = request()->mongoDB;
            if($data){
                $str = '<?php
$mongoDB = array();
                        
$mongoDB[\'host\'] = \'' . $data['host']. '\';
$mongoDB[\'username\'] = \'' . $data['username']. '\';
$mongoDB[\'password\'] = \'' . $data['password'] . '\';
$mongoDB[\'port\'] = \'' . $data['port'] . '\';
$mongoDB[\'database\'] = \'' . $data['database']. '\';
';
                app(Filesystem::class)->put(base_path('database/mongoDB.php'), $str);     //写入配置文件

                //清理缓存
                \Artisan::call('config:clear');
                \Artisan::call('cache:clear');
                return $this->successJson('修改成功,如没生效可尝试清理缓存后重试');
            }
        $mongoDB = array();
        if (file_exists(base_path('database/mongoDB.php'))) {
            include base_path('database/mongoDB.php');
        }

        return view('siteSetting.mongoDB_config',['set'=>json_encode($mongoDB)]);
    }


    /**
     * 修改.env文件值
     * @param array $data
     * @return bool
     */
    public function editEnvFile($data = [])
    {
        $envPath = base_path() . DIRECTORY_SEPARATOR . '.env';
        $contentArray = collect(file($envPath, FILE_IGNORE_NEW_LINES));

        $inArr = [];
        $contentArray->transform(function ($item) use ($data,&$inArr){
            foreach ($data as $key => $value){
                if(str_contains($item, $key)){
                    $inArr[] = $key;

                    return $key . '=' . $value;
                }
            }

            return $item;
        });

        foreach ($data as $key => $value){
            if(!in_array($key,$inArr)){
                $contentArray->push($key . '=' . $value);
            }
        }

        $content = implode($contentArray->toArray(), "\n");
        app(Filesystem::class)->put(base_path('.env'),$content);     //写入配置文件
        return true;
    }

}