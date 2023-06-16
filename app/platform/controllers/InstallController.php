<?php
/**
 * Created by PhpStorm.
 * User: liuyifan
 * Date: 2019/4/2
 * Time: 13:36
 */

namespace app\platform\controllers;


use Illuminate\Redis\Database;
use Predis\Connection\ConnectionException;
use \Illuminate\Support\Facades\DB;
use app\platform\modules\system\models\SystemSetting;
use app\platform\modules\user\models\AdminUser;
use app\common\services\Utils;
use app\platform\modules\user\models\YzUserProfile;
use app\common\traits\JsonTrait;
use Illuminate\Support\Facades\Redis;
use League\Flysystem\Filesystem;

class InstallController
{
    use JsonTrait;

    public $user_txt;

    public function __construct()
    {
        if (file_exists(base_path().'/bootstrap/install.lock')) {
            return $this->errorJson('操作失败，商城已部署', ['status' => -1]);
        }

        $this->user_txt = base_path().'/app/platform/controllers/user.txt';
    }

    public function agreement()
    {
        /* // 取出 xml 单独标签列
        $file = base_path().'/manifest.xml';
        $con = file_get_contents($file);

        $xmlTag = 'version';
        preg_match_all("/<".$xmlTag.">.*<\/".$xmlTag.">/", $con, $temp);

        // 返回 ] 第一次出现的位置
        $first = strpos($temp[0][0],"]");
        // 返回 [ 最后一次出现的位置
        $end = strripos($temp[0][0],"[")+1;
        $version = substr($temp[0][0], $end, $first-$end);*/


       /* $app = config('database.redis.default');
        $app['host'] = '123';
        $a = new Database($app);
        $b = $a->connection();
        return $this->successJson('成功', [
            'version' => $a,
            'version' => $a,
            'version1' => $app,
        ]);*/
     /*  $redis['host']= '127.0.0.1';
        $pwd= null;
       $redis['port']= '6379';
       $redis['cache_database']= '1';
       $redis['database']= '0';

        $str = '<?php
$redis = array();

$redis[\'host\'] = \'' . $redis['host']. '\';
$redis[\'password\'] = \'' . $pwd . '\';
//$redis[\'port\'] = \'' . $redis['port'] . '\';
//$redis[\'database\'] = \'' . $redis['database']. '\';
//$redis[\'cache_database\'] = \'' . $redis['cache_database']. '\';
//';
       file_put_contents(base_path('database/redis.php'), $str);     //写入配置文件
        //\Artisan::call('config:cache');
        config(['database.redis.default.password' => $pwd]);
        config(['database.redis.cache.password' => $pwd]);
        app()->singleton('redis', function ($app) {
            return new Database($app['config']['database.redis']);
        });
        $a = \Illuminate\Support\Facades\Redis::ping();
        dd($a);*/
        $version = require base_path('config/version.php').'';

        return $this->successJson('成功', [
            'version' => $version
        ]);
    }

    /**
     * 运行环境检测
     */
    public function check()
    {
        // 服务器操作系统
        $ret['server_os'] = php_uname();
        // 服务器域名
        $ret['server_name'] =  $_SERVER['SERVER_NAME'];
        // PHP版本
        $ret['php_version'] = PHP_VERSION;
        // 程序安装目录
        $ret['servier_dir'] = base_path();
        // 磁盘剩余空间
        if(function_exists('disk_free_space')) {
            $ret['server_disk'] = floor(disk_free_space(base_path()) / (1024*1024)).'M';
        } else {
            $ret['server_disk'] = 'unknow';
        }
        // 上传限制
        $ret['upload_size'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';


        // 检测PHP版本必须为5.6
        $ret['php_version_remark'] = true;
        if(version_compare(PHP_VERSION, '5.6.0') == -1 && version_compare(PHP_VERSION, '5.7') == 1 ) {
            $ret['php_version_remark'] = false;
        }
        // 检测 cURL
        $ret['php_curl'] = extension_loaded('curl') && function_exists('curl_init');
        // cURL 版本
        $ret['php_curl_version'] = curl_version()['version'];
        // 检测 PDO
        $ret['php_pdo'] = extension_loaded('pdo') && extension_loaded('pdo_mysql');
        // 检测 openSSL
        $ret['php_openssl'] = extension_loaded('openssl');
        // 检测 GD
        $ret['php_gd'] = extension_loaded('gd');
        // GD 库版本
        $ret['php_gd_version'] = gd_info()['GD Version'];
        // 检测 session.auto_start开启
       // $ret['php_session_auto_start'] = strtolower(ini_get('session.auto_start')) == '0' || 'off' ? true : false;
        // 检测 json
       // $ret['json'] = extension_loaded('json') && function_exists('json_decode') && function_exists('json_encode');
        
        // 检测 file_get_content
        $ret['file_get_content'] = function_exists('file_get_contents');

        //检测redis
       // $ret['redis'] = $this->getRedisStatus();

        $result = $this->returnData($ret);

        return $this->successJson('成功', [
            'server_info'=> $result['server_info'],
            'sysytem_environment' => $result['sysytem_environment'],
            'check_function' => $result['check_function']
        ]);
    }

    /**
     * 运行环境检测 返回数据
     *
     * @param $ret
     * @return mixed
     */
    public function returnData($ret)
    {
        $result['server_info'] = [
            [
                'name' => '服务器操作系统',
                'value' => $ret['server_os'],
            ],
            [
                'name' => '服务器域名',
                'value' => $ret['server_name'],
            ],
            [
                'name' => 'PHP版本',
                'value' => $ret['php_version'],
            ],
            [
                'name' => '程序安装目录',
                'value' => $ret['servier_dir'],
            ],
            [
                'name' => '磁盘剩余空间',
                'value' => $ret['server_disk'],
            ],
            [
                'name' => '上传限制大小',
                'value' => $ret['upload_size'],
            ],
        ];

        $result['sysytem_environment'] = [
            [
                'name' => 'PHP版本',
                'need' => '7.4',
                'optimum' => '7.4',
                'check' => $ret['php_version_remark'],
                'value' => $ret['php_version'],
            ],
            [
                'name' => 'cURL',
                'need' => '支持',
                'optimum' => '无限制',
                'check' => $ret['php_curl'],
                'value' => $ret['php_curl_version'],
            ],
            [
                'name' => 'PDO',
                'need' => '支持',
                'optimum' => '无限制',
                'check' => $ret['php_pdo'],
                'value' => $ret['php_pdo'] ? '支持' : '不支持',
            ],
            [
                'name' => 'openSSL',
                'need' => '支持',
                'optimum' => '无限制',
                'check' => $ret['php_openssl'],
                'value' => $ret['php_openssl'] ? '支持' : '不支持',
            ],
            [
                'name' => 'GD',
                'need' => '支持',
                'optimum' => 'GD2',
                'check' => $ret['php_gd'],
                'value' => $ret['php_gd_version'],
            ]
          /*  [
                'name' => 'Redis',
                'need' => '支持',
                'optimum' => '无限制',
                'check' => $ret['redis'],
                'value' => $ret['redis'] ? '支持' : '不支持',
            ],*/
        ];

        $result['check_function'] = [
            [
                'name' => 'file_get_content',
                'need' => '支持',
                'value' => $ret['file_get_content'] ? '支持' : '不支持',
            ],
        ];

        return $result;
    }

    /**
     * 文件权限设置
     */
    public function filePower()
    {
        $check_filename = [
            '/bootstrap',
            '/storage/framework',
            '/storage/logs'
        ];

        $check = [
            base_path() . '/bootstrap/cache',
            base_path() . '/storage/framework/view',
            base_path() . '/storage/logs',
        ];

        $ret = [];
        for($i=0; $i<count($check); $i++) {
            $ret[$i]['name'] =  $check_filename[$i];
            $ret[$i]['need'] =  '可写';
            $ret[$i]['value'] = (bool)$this->check_writeable($check[$i]);
        }

        //判断是否存在static文件夹,没有的话就下载免费版，并覆盖掉原来的文件

        $static_dir = base_path()."/static";

        if (is_dir($static_dir)) {
            $dh = @opendir($static_dir);
            $i = 0;
            while (($file = readdir($dh)) !== false){
                if($file != "." && $file != ".."){
                    $i += 1;
                }
            }
            closedir($dh);

            if ($i <= 0) {
                //下载免费版并覆盖原来的数据
                $this->downloadFree();
            }

        } else {
            //下载免费版并覆盖原来的数据
            $this->downloadFree();
        }

        return $this->successJson('成功', $ret);
    }

    /**
     * 检查目录是否有可读可写的权限
     * @param $dir
     * @return int
     */
    private function check_writeable($dir)
    {
        $writeable = 0;
        if(!is_dir($dir)) {
            @mkdir($dir, 0777);
        }
        if(is_dir($dir)) {
            if($fp = fopen("$dir/test.txt", 'w+')) {
                fclose($fp);
                unlink("$dir/test.txt");
                $writeable = 1;
            } else {
                $writeable = 0;
            }
        }

        return $writeable;
    }

    /**
     * 账号设置
     */
    public function setInformation()
    {
        $set = request()->set;
        $user = request()->user;
        $redis = request()->redis;
        $set['AUTH_PASSWORD'] = randNum(8);

        $filename = base_path().'/.env';
        $env = file_get_contents($filename);

        // 验证超级管理员信息
        if (!$user['username'] || !$user['password']) {
            return $this->errorJson('用户名或密码不能为空');
        } elseif ($user['password'] !== $user['repassword']) {
            return $this->errorJson('两次密码不一致');
        }

        foreach ($set as $item=>$value) {
            // 获取键第一次出现位置
            $check_env_key = strpos($env, $item);
            // 获取键后面 \n 换行符第一次出现位置
            $check_env_value = strpos($env, "\n", $check_env_key);
            // 得出两个位置之间的内容进行替换
            $num = $check_env_value - $check_env_key;
            if ((bool)$check_env_key) {
                $env = substr_replace($env, "{$item}='{$value}'", $check_env_key, $num);
            } else {
                $env .= "\n{$item}='$value'\n";
            }
        }

        try{
            $link = new \PDO("mysql:host=".$set['DB_HOST'].";port=".$set['DB_PORT'], $set['DB_USERNAME'], $set['DB_PASSWORD']);
            $link->exec("CREATE DATABASE IF NOT EXISTS `{$set['DB_DATABASE']}` DEFAULT CHARACTER SET utf8");
            new \PDO("mysql:host=".$set['DB_HOST'].";dbname=".$set['DB_DATABASE'].";port=".$set['DB_PORT'], $set['DB_USERNAME'], $set['DB_PASSWORD']);
        } catch (\Exception $e){
            return $this->errorJson($e->getMessage());
        }

        $result = file_put_contents($filename, $env);

        if (!$result) {
            return $this->errorJson('保存mysql配置数据有误');
        }

        fopen($this->user_txt, 'w+');
            file_put_contents($this->user_txt, serialize($user));
        $this->mvenv($set);
        if ($redis) {
            if(strtolower($redis['password']) == 'null' || empty($redis['password'])) {
                $redis['password'] = null;
            }
            $str = '<?php
$redis = array();
                        
$redis[\'host\'] = \'' . $redis['host']. '\';
$redis[\'password\'] = \'' . $redis['password'] . '\';
$redis[\'port\'] = \'' . $redis['port'] . '\';
$redis[\'database\'] = \'' . $redis['database']. '\';
$redis[\'cache_database\'] = \'' . $redis['cache_database']. '\';
';
            file_put_contents(base_path('database/redis.php'), $str);     //写入配置文件
            config(['database.redis.default.host' => $redis['host']]);
            config(['database.redis.default.password' => $redis['password']]);
            config(['database.redis.default.port' => $redis['port']]);
            config(['database.redis.default.database' => $redis['database']]);


            config(['database.redis.cache.host' => $redis['host']]);
            config(['database.redis.cache.password' => $redis['password']]);
            config(['database.redis.cache.port' => $redis['port']]);
            config(['database.redis.cache.database' => $redis['database']]);

            app()->singleton('redis', function ($app) {
                $redisConfig = $app->make('config')->get('database.redis', []);
                return new \app\framework\Redis\Database($app,$redisConfig['client'],$redisConfig);
            });

            //检测redis连接
           return  $this->getRedisStatus();

        }

        return $this->successJson('成功');
    }

    /**
     * 迁移数据库信息
     */
    public function mvenv($set)
    {
            $str = '<?php
$config = array();

$config[\'db\'][\'master\'][\'host\'] = \'' . $set['DB_HOST'] . '\';
$config[\'db\'][\'master\'][\'username\'] = \'' . $set['DB_USERNAME'] . '\';
$config[\'db\'][\'master\'][\'password\'] = \'' .$set['DB_PASSWORD'] . '\';
$config[\'db\'][\'master\'][\'port\'] = \'' .$set['DB_PORT'] . '\';
$config[\'db\'][\'master\'][\'database\'] = \'' . $set['DB_DATABASE'] . '\';
$config[\'db\'][\'master\'][\'tablepre\'] = \'' . $set['DB_PREFIX'] . '\';

$config[\'db\'][\'slave_status\'] = false;
$config[\'db\'][\'slave\'][\'1\'][\'host\'] = \'\';
$config[\'db\'][\'slave\'][\'1\'][\'username\'] = \'\';
$config[\'db\'][\'slave\'][\'1\'][\'password\'] = \'\';
$config[\'db\'][\'slave\'][\'1\'][\'port\'] = \'\';
$config[\'db\'][\'slave\'][\'1\'][\'database\'] = \'\';
$config[\'db\'][\'slave\'][\'1\'][\'tablepre\'] = \'\';
';
            app(\Illuminate\Filesystem\Filesystem::class)->put(base_path('database/config.php'), $str);

    }

    /**
     * 创建数据
     */
    public function createData()
    {
        ini_set('max_execution_time','0');

        include_once base_path() . '/sql.php';

//        try{
//            exec('php artisan migrate',$result); //执行命令
//        }catch (\Exception $e) {
//            $e->getMessage();
//        }

        try {
            \Log::debug('安装初始数据迁移');
            \Artisan::call('migrate',['--force' => true]);
            \Artisan::call('db:seed', ['--force' => true, '--class' => 'YzSystemSettingTableSeeder']);
        }catch (\Exception $e) {
            return $this->errorJson('创建数据失败,请重新安装');
        }

        $user_model = new AdminUser;
        if ($user_model->find(1)) {
            return $this->successJson('成功');
        }
        // 取出账号设置的信息
        $user = unserialize(file_get_contents($this->user_txt));

        // 保存站点名称
        $site_name = SystemSetting::settingSave(['name' => $user['name']], 'copyright', 'system_copyright');
        if (!$site_name) {
            return $this->errorJson('创建数据失败,请重新安装');
        }

        $user['password'] = bcrypt($user['password']);
        $user['status'] = 0;
        $user['type'] = 0;
        $user['lastvisit'] = time();
        $user['lastip'] = Utils::getClientIp();
        $user['joinip'] = Utils::getClientIp();
        $mobile = $user['mobile'];
        unset($user['name']);
        unset($user['repassword']);
        unset($user['mobile']);
        $user_model->fill($user);

        if (!$user_model->save()) {
            $this->errorJson('创建数据失败,请重新安装');
        }

        // 保存用户信息关联表信息
        $user_profile = YzUserProfile::create(['uid' => $user_model['uid'], 'mobile' => $mobile]);
        if (!$user_profile) {
            $this->errorJson('创建数据失败,请重新安装');
        }


        @unlink(base_path().'/app/platform/controllers/user.txt');

        fopen(base_path()."/bootstrap/install.lock", "w+");

        \Artisan::call('key:generate', ['--force' => true]);


        return $this->successJson('成功');
    }



    public function downloadFree()
    {
        //①：下载压缩包到指定位置
        $fileURL = "https://downloads.yunzmall.com/bt_static/static.zip";
        $filename = date("Ymd")."_yun_shop.zip";

        $fh = fopen(base_path().'/'.$filename, 'w');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fileURL);
        curl_setopt($ch, CURLOPT_FILE, $fh);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // this will follow redirects
        curl_exec($ch);
        curl_close($ch);
        fclose($fh);

        //②：解压覆盖当前文件夹，删除压缩包
        $path = pathinfo(realpath($filename), PATHINFO_DIRNAME);
        $file_name = $path."/".$filename;
        chmod($file_name,0755);
        $zip = new \ZipArchive();
        $res = $zip->open($file_name);


        if ($res === TRUE) {
            for($i=0;$i<$zip->numFiles;$i++){
                $entry_info = $zip->statIndex($i);
                if(substr($entry_info["name"],0,strlen("static/")) != "static/"){
                    $zip->deleteIndex($i);
                }
            }
        }

        if ($res === TRUE) {
            $zip->extractTo($path);
            $zip->close();
        }

        @unlink(base_path().'/'.$filename);
    }

    public function delete()
    {
        if (env('APP_ENV') == 'production') {
            @unlink(base_path().'/app/platform/controllers/InstallController.php');
        }

        return $this->successJson('成功');
    }

    public function getRedisStatus()
    {

        try {
            if (!class_exists('Predis\Client')) {
                return $this->errorJson('Predis未安装');
            }

            $res = \Illuminate\Support\Facades\Redis::ping() == 'PONG';

            if ($res) {
                return $this->successJson('成功');
            }
        } catch (ConnectionException $exception) {
            return $this->errorJson('redis连接失败');
        } catch (\Exception $exception) {
            return $this->errorJson('redis无法使用');
        }

    }

    //生成6位随机账号
    public function getRandomUser()
    {
        $digits    = array_flip(range('0', '9'));
        $lowercase = array_flip(range('a', 'z'));
        $combined  = array_merge($digits, $lowercase);
        $password  = str_shuffle(array_rand($digits) .
            array_rand($lowercase) .
            implode(array_rand($combined,4))
        );

        return $this->successJson('生成随机账号成功',$password);
    }

    //生成12位随机密码串
    public function getRandomPsw()
    {
        $digits    = array_flip(range('0', '9'));
        $lowercase = array_flip(range('a', 'z'));
        $uppercase = array_flip(range('A', 'Z'));
        $special   = array_flip(str_split('.!@#$%^&*()_+-?'));
        $combined  = array_merge($digits, $lowercase, $uppercase, $special);

        $password  = str_shuffle(array_rand($digits) .
            array_rand($lowercase) .
            array_rand($uppercase) .
            array_rand($special) .
            implode(array_rand($combined,8))
        );

        return $this->successJson('生成随机密码成功',$password);
    }

}
