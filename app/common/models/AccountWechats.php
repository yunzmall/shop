<?php
/**
 * Created by PhpStorm.
 * Author:  
 * Date: 2017/4/5
 * Time: 上午10:00
 */

namespace app\common\models;



use Illuminate\Support\Facades\Cache;

class AccountWechats extends BaseModel
{
    public $table = 'account_wechats';

    public function __construct(array $attributes = [])
    {
		parent::__construct($attributes);

        if (config('app.framework') == 'platform') {
			$this->table = 'yz_uniacid_app';
        }
	}

    public static function getAccountByUniacid($uniacid)
    {
        if (!config('app.framework') == 'platform' || file_exists(base_path().'/bootstrap/install.lock')) {
			//这里使用laravel Cache是因为商城cache调用了Yunshop::app()->uniacid导致死循环
			return Cache::remember('account_app_'.$uniacid,3600,function () use ($uniacid) {
				return self::where('uniacid', $uniacid)->first();
			});

        }
    }

    /**
     * 设置公众号
     * @param $account
     */
    public static function setConfig($account)
    {
        if($account){
            \Config::set('wechat.app_id',$account->key);
            \Config::set('wechat.secret',$account->secret);
            \Config::set('wechat.token',$account->token);
            \Config::set('wechat.aes_key',$account->encodingaeskey);
        }
        return;
    }


}