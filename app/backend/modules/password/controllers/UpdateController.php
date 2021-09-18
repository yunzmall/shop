<?php
/****************************************************************
 * Author:  king -- LiBaoJia
 * Date:    5/19/21 3:41 PM
 * Email:   livsyitian@163.com
 * QQ:      995265288
 * IDE:     PhpStorm
 * User:    www.yunzshop.com  www.yunzshop.com
 * Company: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务
 ****************************************************************/


namespace app\backend\modules\password\controllers;


use app\common\components\BaseController;
use app\common\services\password\PasswordService;
use app\frontend\models\MemberShopInfo;

class UpdateController extends BaseController
{
    public function index()
    {
        $this->validator();

        return $this->update() ? $this->successJson() : $this->errorJson();
    }

    private function update()
    {
        $data = (new PasswordService())->create(trim(request()->password));

        return MemberShopInfo::where('member_id', request()->member_id)->update(['pay_password' => $data['password'], 'salt' => $data['salt']]);
    }

    /**
     * 验证数据
     */
    public function validator()
    {
        $validator = $this->getValidationFactory()->make(request()->all(), $this->rules(), $this->messages(), $this->attributeNames());

        if ($validator->fails()) $this->errorJson($validator->errors()->first());
    }

    /**
     * 字段规则
     *
     * @return array
     */
    public function rules()
    {
        if((new PasswordService())->masterSwitch()) {
            return [
                'member_id' => 'required|integer|min:0',
//                'password'  => 'required|min:6|max:6|regex:/^[0-9]*$/|same:confirmed',
                'confirmed' => 'required|same:password'
            ];
        }
        return [
            'member_id' => 'required|integer|min:0',
            'password'  => 'required|min:6|max:6|regex:/^[0-9]*$/|same:confirmed',
            'confirmed' => 'required|same:password'
        ];
    }

    /**
     * 自定义消息
     *
     * @return array
     */
    private function messages()
    {
        return [
            'same' => '两次输入不一致',
        ];
    }

    /**
     * 自定义名称
     *
     * @return array
     */
    public function attributeNames()
    {
        return [
            'password'  => '密码',
            'confirmed' => '确认密码',
        ];
    }
}
