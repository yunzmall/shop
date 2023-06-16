<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/24
 * Time: 17:12
 */

namespace app\frontend\modules\order\models;


use app\common\models\Goods;
use app\common\models\order\OrderInvoice;

class PreOrderInvoice extends OrderInvoice
{

    protected $order;


    protected function _initAttributes()
    {
        $get = \Setting::get('plugin.invoice');
        $data = $this->order->getRequest()->input('invoice');
        $is_open = false;
        if (app('plugins')->isEnabled('invoice') && $get['is_open']) {
            $is_open = true;
        }
        $content = ['商品明细', '商品类别', '不开发票'];
        $attributes = [
            'uid' => \YunShop::app()->getMemberId(),
            'invoice_type' => $data['invoice_type'] ?:0,//发票类型
            'email' =>  $data['email'] ?: '',//电子邮箱
            'rise_type' =>  isset($data['rise_type'])? $data['rise_type']:1,//收件人或单位
            'collect_name' =>  $data['collect_name'] ?: '',//抬头或单位名称
            'uniacid'       => \Yunshop::app()->uniacid,
            'enterprise_id' => empty($get['enterprise_name']) ? '' : $get['enterprise_name'],// 企业唯一标识
            'invoice_sn'  => empty($get['appid']) ? '' : $get['appid'] . $this->createInvitecode(),// 发票请求流水号 (全局唯一)
            'equipment_number' =>  empty($get['enterprise_equipment']) ? '' : $get['enterprise_equipment'],     // 税控设备号
            'xsf_name' => empty($get['enterprise_name']) ? '' : $get['enterprise_name'],// 销售方名称
            'xsf_taxpayer' =>  empty($get['taxpayer_number']) ? '' : $get['taxpayer_number'],  // 销售方纳税人识别号,
            'xsf_address' => empty($get['taxpayer_number']) ? '' : $get['address'],   // 销售方地址
            'xsf_mobile' => empty($get['mobile']) ? '' : $get['mobile'],  // 销售方电话
            'xsf_bank_admin' => empty($get['bank_admin']) ? '' : $get['bank_admin'],  // 销售方开户银行
            'content' => $is_open&&$data['content'] ? $content[$data['content']] : '',  // 发票内容
            'drawer' => empty($get['invoice_drawer']) ? '' : $get['invoice_drawer'],  // 开票人
            'payee' => empty($get['billing_payee']) ? '' : $get['billing_payee'],  // 收款人
            'reviewer' => empty($get['invoice_reviewer']) ? '' : $get['invoice_reviewer'],  // 收款人
            'bill_type' => !empty($data['bill_type']) ? $data['bill_type'] : 0,  // 开票类型：0-蓝字发票；1-红字发票
            'special_type' => !empty($data['special_type']) ? $data['special_type'] : 0, // 特殊票种：0-不是；1-农产品销售；2-农产品收购(收购票)
            'collection' => !empty($data['collection']) ? $data['collection'] : 0, // 征收方式：0- 专普票；1-减按计增；2-差额征收
            'list_identification'=> !empty($data['list_identification']) ? $data['list_identification'] : 0,// 清单标识：0- 非清单；1- 清单
            'gmf_taxpayer' => !empty($data['gmf_taxpayer']) ? $data['gmf_taxpayer']:  '',// 购买方纳税人识别号
            'gmf_address' => !empty($data['gmf_address']) ? $data['gmf_address'] : '', // 注册地址
            'gmf_bank' => !empty($data['gmf_bank']) ? $data['gmf_bank'] : '', // 开户银行
            'gmf_bank_admin' => !empty($data['gmf_bank_admin']) ? $data['gmf_bank_admin'] : '',// 购买方银行账户
            'gmf_mobile' => !empty($data['gmf_mobile']) ? $data['gmf_mobile'] : '',   // 注册手机号码
            'remarks' => !empty($data['remarks']) ? $data['remarks'] : '',   // 备注
            'notice_no' => !empty($data['notice_no']) ? $data['notice_no'] : '',// 通知单编号
            'applicant' => !empty($data['applicant']) ? $data['applicant'] : '',// 申请人
            'is_audit' => !empty($data['is_audit']) ? $data['is_audit'] : 0, // 是否自动审核：0-非自动审核；1-自动审核
            'tax_rate' => !empty($data['tax_rate']) ? $data['tax_rate'] : 0, // 税率
            'zero_tax_rate' => !empty($data['zero_tax_rate']) ? $data['zero_tax_rate'] : 0, // 零税率标识：0-正常税率；1-免税；2-不征税；3-普通零税率
            'invoice_no' => !empty($data['invoice_no']) ? $data['invoice_no'] : '', // 发票编号
            'invoice_nature' => !empty($data['invoice_nature']) ? $data['invoice_nature'] : 0, // 发票行性质: 0-正常行;1-折扣行 (折扣票金额正);2-被折扣行'(折扣票金额负)
            'status' => !empty($data['status']) ? $data['status'] : 0,// 开票状态 0-未开票，1-开票成功，2-开票中
            'apply' => !empty($data['apply']) ? $data['apply'] : 0,
            'col_address' => !empty($data['col_address']) ? $data['col_address'] : '', // 收票地址
            'col_name' => !empty($data['col_name']) ? $data['col_name'] : '', //收票人
            'col_mobile' => !empty($data['col_mobile']) ? $data['col_mobile'] : '', //收票人手机号码
            'detail_param' => !empty($data['detail_param']) ? $data['detail_param'] : '',
            'invoice_time' => time()
        ];
       /* $attributes = [
            'invoice_type' => $this->order->getRequest()->input('invoice_type', null),//发票类型
            'email' =>  $this->order->getRequest()->input('email'),//电子邮箱
//            'rise_type' =>  $this->order->getRequest()->input('rise_type', null),//收件人或单位
            'collect_name' =>  $this->order->getRequest()->input('call', ''),//抬头或单位名称
//            'company_number' =>  $this->order->getRequest()->input('company_number', ''),//单位识别号
            'uniacid'       => \Yunshop::app()->uniacid
        ];
        if ($this->order->getRequest()->input('invoice_type')){
            $attributes['invoice_type'] = $this->order->getRequest()->input('invoice_type');
        }
        if ($this->order->getRequest()->input('rise_type')){
            $attributes['rise_type'] = $this->order->getRequest()->input('rise_type');
        }
        $this->dataOrder();
*/
        $attributes = array_merge($this->getAttributes(), $attributes);
        $this->setRawAttributes($attributes);
    }

    /**
     * @param PreOrder $order
     */
    public function setOrder(PreOrder $order)
    {
        $this->order = $order;

        $this->_initAttributes();

        $this->order->setRelation('orderInvoice', $this);

    }

    public function createInvitecode()
    {
        $length = 8;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}