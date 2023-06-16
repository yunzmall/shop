<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/9
 * Time: 下午5:26
 */

namespace app\backend\modules\setting\controllers;

use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\models\Address;
use app\common\models\Setting as SettingModel;
use app\common\services\AutoUpdate;
use app\common\services\PluginManager;
use app\common\services\systemUpgrade;
use Ixudra\Curl\Facades\Curl;

class KeyController extends BaseController
{

    public function preAction()
    {
        if (\YunShop::app()->role != 'founder') {
            return $this->message('非法访问', yzWebUrl('survey.survey.index') , 'warning');
        }
        
        $this->uniacid = \YunShop::app()->uniacid;
        $this->_log = app('log');
    }

    /**
     * 密钥填写
     * @return mixed
     */
    public function index()
    {
		$key = '';
		$province = [];
		$plugins = '';
		$unauthorized = '';
		$page = 'auth';
		$domain = request()->getHttpHost();
		$url = config('auto-update.registerUrl') . '/check_domain.json';

		$auth_str = Curl::to($url)
			->withData(['domain' => $domain])
			->asJsonResponse(true)
			->get();


		if (empty($auth_str['data']['key']) && empty($auth_str['data']['secret'])) {
			$page = 'free';
			$province = Address::getProvince()->toArray();
		} else {
			$key = $auth_str['data']['key'];
            Setting::set('shop.key', $auth_str['data']);

			$systemUpgrade = new systemUpgrade($auth_str['data']['key'], $auth_str['data']['secret']);
			$update = new AutoUpdate(null, null, 300);
			$update->setBasicAuth($auth_str['data']['key'], $auth_str['data']['secret']);
			$update->setUpdateUrl(config('auto-update.checkUrl'));

			$pluginsInfo = $systemUpgrade->getPluginsInfo($update);

			if ($pluginsInfo['localNoAuthPluginsTitle']) {
				$unauthorized = implode($pluginsInfo['localNoAuthPluginsTitle'], ',');
			}

			$pluginManager = app('app\common\services\PluginManager');
			$plugins = $pluginManager->getPluginsByCategory();
		}

        return view('setting.key.index', [
			'domain' => json_encode(['data' => $domain]),
            'province' => json_encode(['data' => $province]),
            'page' => json_encode(['type' => $page]),
            'set' => json_encode(['key' => $key]),
			'plugins' => json_encode(['data' => $plugins]),
			'unauthorized' => json_encode(['data' => $unauthorized])
        ])->render();
    }

    /*
     * 处理信息
     */
    private function processingKey($requestModel, $type)
    {
        $domain = request()->getHttpHost();
        $data = [
            'uniacid' => $this->uniacid,
            'key' => $requestModel['key'],
            'secret' => $requestModel['secret'],
            'domain' => $domain,
        ];

        if ($type == 'create') {

            $content = Curl::to(config('auto-update.checkUrl') . '/app-account/create')
                ->withData($data)
                ->get();
            // dd($content);exit();
            $writeRes = Setting::set('shop.key', $requestModel);

            \Cache::forget('app_auth' . $this->uniacid);

            return $writeRes && $content;

        } else if ($type == 'cancel') {

            $content = Curl::to(config('auto-update.checkUrl') . '/app-account/cancel')
                ->withData($data)
                ->get();
            //var_dump($content);exit();

            $writeRes = Setting::set('shop.key', '');

            \Cache::forget('app_auth' . $this->uniacid);

            return $writeRes && $content;
        }
    }

    /*
     * 检测是否有数据存在
     */
    public function isExist($data)
    {

        $type = request()->type;
        $domain = request()->getHttpHost();

        $filename = config('auto-update.checkUrl') . '/check_isKey.json';
        $postData = [
            'type' => $type,
            'domain' => $domain,
        ];
        $update = new AutoUpdate();
        $res = $update->isKeySecretExists($filename, $data, $postData, 'auto_update ' . $this->uniacid . ' ');
        return $res;
    }

    public function getCity()
    {
        $data = request()->data;

        $addressData = Address::getCityByParentId($data['id']);

        return $this->successJson('ok', $addressData);
    }

    public function getArea()
    {
        $data = request()->data;

        $addressData = Address::getAreaByParentId($data['id']);

        return $this->successJson('ok', $addressData);
    }

    public function register()
    {
        $data = request()->data;

        $auth_url = yzWebFullUrl('setting.key.index');

        $key = 'free';
        $secret = request()->getSchemeAndHttpHost();

        $url = config('auto-update.registerUrl') . "/free/{$data['name']}/{$data['trades']}/{$data['province']['areaname']}/{$data['city']['areaname']}/{$data['area']['areaname']}/{$data['address']}/{$data['mobile']}/{$data['captcha']}";

        $register = Curl::to($url)
            ->withHeader(
                "Authorization: Basic " . base64_encode("{$key}:{$secret}")
            )
            ->asJsonResponse(true)
            ->get();

        if (!is_null($register) && 1 == $register['result']) {
            if ($register['data']) {
                //检测数据是否存在
                $res = $this->isExist($register['data']['shop']);
                //var_dump($res);exit();
                if (!$res['isExists']) {
                    if ($res['message'] == 'amount exceeded') {
                        $this->errorJson('您已经没有剩余站点数量了，如添加新站点，请取消之前的站点或者联系我们的客服人员！');
                    } else {
                        $this->errorJson('Key或者密钥出错了！');
                    }

                } else {
                    if ($this->processingKey($register['data']['shop'], 'create')) {
                        if ($register['data']['plugins']) {
                            Setting::set('free.plugin', $register['data']['plugins']);
                        }
                        return $this->successJson("站点添加成功", ['url' => $auth_url]);
                    }
                }
            }
        }

        return $this->errorJson("站点添加失败");
    }

    public function sendSms()
    {
        $key = 'free';
        $secret = request()->getHttpHost();
        $mobile = request()->mobile;

        $url = config('auto-update.registerUrl') . "/sendsms/{$mobile}";

        $res = Curl::to($url)
            ->withHeader(
                "Authorization: Basic " . base64_encode("{$key}:{$secret}")
            )
            ->asJsonResponse(true)
            ->get();

        return $this->successJson('验证码已发送', $res);
    }

    public function reset()
    {
        $data = request()->data;

        $setting = Setting::get('shop.key');

        if ($data['key'] && $data['secret']) {
             try {
                 Setting::set('shop.key', $data);
             }  catch (\Exception $e) {
                 return $this->errorJson($e->getMessage());
             }
        }

        return $this->successJson('成功', $setting);
    }
}
