<?php
/****************************************************************
 * Author:  libaojia
 * Date:    2017/9/16 下午4:58
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * User:    芸众商城 www.yunzshop.com
 ****************************************************************/

namespace app\common\services\password;


use app\common\exceptions\PaymentException;
use app\common\facades\Setting;
use app\common\models\MemberShopInfo;

class PasswordService
{
    //todo 后台登陆密码、会员登陆密码、会员支付密码间公用关系，拆解模型、整理模型（还需要一点点梳理啊）

    /**
     * 支付密码总开关，如果关闭全部不需要密码验证
     *
     * @return bool
     */
    public function masterSwitch()
    {
        return (bool)Setting::get('pay_password.pay_state');
    }

    /**
     * 是否开启多位数密码
     *
     * @return bool
     */
    public function multipleSwitch()
    {
        return (bool)Setting::get('pay_password.pay_multiple');
    }

    /**
     * 验证虚拟币操作方式是否需要密码验证，需要返回 true，不需要返回 false
     *
     * 虚拟币类型，如：balance point love
     * @param string $property
     *
     * 虚拟币操作方式，如：pay transfer withdraw
     * @param string $operate
     *
     * @return bool
     */
    public function isNeed($property = '', $operate = '')
    {
        if (!$this->masterSwitch()) return false;

        return $this->propertySwitch($property, $operate);
    }

    /**
     * 虚拟币操作方式开关状态，开启 true，关闭 false
     *
     * @param string $property
     * @param string $operate
     *
     * @return bool
     */
    private function propertySwitch($property, $operate)
    {
        $setting = Setting::get("pay_password.{$property}") ?: [];

        return $setting ? in_array($operate, $setting) : false;
    }


    //todo 该方法应该可以提到 会员yzMember模型中
    public function checkPayPassword($memberId, $password)
    {
        if (!$this->masterSwitch()) throw (new PaymentException())->settingClose();

        $memberModel = $this->yzMember($memberId);

        if (!$memberModel->hasPayPassword()) throw (new PaymentException())->notSet();

        if (!$this->passwordCheck($password, $memberModel->pay_password, $memberModel->salt)) throw (new PaymentException())->passwordError();
    }

    /**
     * @param int $memberId
     *
     * @return MemberShopInfo
     */
    private function yzMember($memberId)
    {
        return MemberShopInfo::select('pay_password', 'salt')->where('member_id', $memberId)->first();
    }

    /**
     * 密码验证
     *
     * @param string $salt
     * @param string $password
     * @param string $sha1_value
     *
     * @return bool
     */
    public function check($password, $sha1_value, $salt)
    {
        return $sha1_value == $this->make($password, $salt) ? true : false;
    }

    /**
     * 生成哈希加密密码值
     *
     * @param string $salt
     * @param string $password
     *
     * @return string
     */
    public function make($password, $salt)
    {
        return sha1("{$password}-{$salt}");
    }

    /**
     * 创建密码
     * @param $password
     * @return array
     */
    public function create($password)
    {
        $salt = $this->randNum(8);
        return ['password' => $this->make($password, $salt), 'salt' => $salt];
    }

    /**
     * 获取随机字符串
     * @param number $length 字符串长度
     * @param boolean $numeric 是否为纯数字
     * @return string
     */
    public function randNum($length, $numeric = FALSE)
    {
        $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed) . '012340567890') : ($seed . 'zZ' . strtoupper($seed));
        if ($numeric) {
            $hash = '';
        } else {
            $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
            $length--;
        }
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed{mt_rand(0, $max)};
        }
        return $hash;
    }

    /**
     * 前端支付密码验证
     *
     * @param $password
     * @param $sha1_value
     * @param $salt
     * @return bool
     */
    public function passwordCheck($password, $sha1_value, $salt)
    {
        //最新验证方式
        if ($sha1_value == sha1("{$password}-{$salt}")) {
            return true;
        }

        //原前端修改密码
        if ($sha1_value == sha1("{$password}-{$salt}-")) {
            return true;
        }

        //原后端修改密码
        if (config('app.framework') != 'platform') {
            global $_W;

            $authkey = $_W['config']['setting']['authkey'];

            if ($sha1_value == sha1("{$password}-{$salt}-{$authkey}")) {
                return true;
            }
        }

        return false;
    }

}
