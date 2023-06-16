<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/6
 * Time: 17:01
 */

namespace app\outside\modes;


use app\common\models\BaseModel;
use app\outside\services\OutsideAppService;

class OutsideAppSetting extends BaseModel
{
    protected $table = 'yz_outside_app_setting';

    protected $guarded = [];

    static protected $needLog = true;

    public $attributes = [];


    protected $casts = [
        'value' => 'json',
    ];

    public static function current()
    {
        return self::uniacid()->first();
    }


    public static function uniqueApp()
    {
        $appId = OutsideAppService::createAppId();
        while (1) {
            if (!self::where('app_id', $appId)->first()) {
                break;
            }
            $appId = OutsideAppService::createAppId();
        }

        return $appId;
    }

    public static function uniqueSecret($appId = '')
    {
        $app_secret = OutsideAppService::createSecret($appId);
        while (1) {
            if (!self::where('app_secret', $app_secret)->first()) {
                break;
            }
            $app_secret = OutsideAppService::createSecret($appId);
        }

        return $app_secret;
    }


}