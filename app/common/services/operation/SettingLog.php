<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/11/23
 * Time: 12:16
 */

namespace app\common\services\operation;

use app\common\models\AdminOperationLog;
use app\common\models\OperationLog;
use app\common\models\user\User;

class SettingLog
{
    public $modules = 'shop';

    public $type = 'setting';

    //需要记录的参数数组
    protected $logs;


    protected $values = [];

    /**
     * SettingLog constructor.
     * @param $setting_key string 设置键名称
     */
    public function __construct($setting_key)
    {
        $this->uid = intval(\YunShop::app()->uid);

        if (empty($this->uid) || is_null($setting_key)) {
            return;
        }

        $this->logs['table_id'] = \YunShop::app()->uniacid;
        $this->logs['admin_uid'] = $this->uid;
        $this->logs['table_name'] = $setting_key;
        $this->logs['ip'] = $_SERVER['REMOTE_ADDR'];
    }


    /**
     * @param $before_value array 之前值
     * @param $after_value array 之后值
     */
    public function recordLog($before_value = [], $after_value)
    {

        $this->setLog('before', $before_value?:[]);
        $this->setLog('after', $after_value?:[]);

        $createData = $this->getAllLogs();

        $log = new AdminOperationLog();

        $log->fill($createData);

        $log->created_at = time();
        $log->updated_at = time();
        $log->save();
    }



    /**
     * 设置日志值
     * @param $log
     * @param $logValue
     */
    public function setLog($log, $logValue)
    {
        $this->logs[$log] = $logValue?:'';
    }

    /**
     * 获取日志值
     * @param $log
     * @return string
     */
    public function getLog($log)
    {
        return isset($this->logs[$log])?$this->logs[$log] : '';
    }


    /**
     *获取所有请求的参数
     *@return array
     */
    public function getAllLogs()
    {
        return $this->logs;
    }
}