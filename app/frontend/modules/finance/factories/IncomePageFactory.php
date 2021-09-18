<?php
/**
 * Created by PhpStorm.
 *
 * User: king/QQ：995265288
 * Date: 2018/5/8 下午2:05
 * Email: livsyitian@163.com
 */

namespace app\frontend\modules\finance\factories;


use app\backend\modules\member\models\MemberRelation;
use app\common\exceptions\AppException;
use app\common\models\Income;
use app\frontend\modules\finance\interfaces\IIncomePage;
use app\frontend\modules\member\models\MemberModel;

class IncomePageFactory
{
    /**
     * @var IIncomePage
     */
    private $_income;


    /**
     * 会员是否是推客
     *
     * @var bool
     */
    private $is_agent;


    /**
     * 是否开启关系链
     *
     * @var bool
     */
    private $is_relation;



    private $lang_set;

    /**
     * 会员收入
     * @var array
     */
    private $total_income;
    
    private $key;


    public function __construct(IIncomePage $income, $lang_set, $is_relation = false, $is_agent = false, $total_income,$key)
    {
        $this->_income = $income;
        $this->is_agent = $is_agent;
        $this->is_relation = $is_relation;
        $this->lang_set = $lang_set;
        $this->total_income = $total_income;
        $this->key = $key;
    }


    /**
     * 收入模型是否显示
     *
     * @return bool
     */
    public function isShow()
    {
        return $this->_income->isShow();
    }


    /**
     * 是否拥有收入模型使用权限
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->_income->isAvailable();
    }


    /**
     * 是否需要验证 是否开启关系链
     *
     * @return bool
     */
    public function validatorIsRelation()
    {
        return $this->_income->needIsRelation();
    }


    /**
     * 是否需要验证 是否是推客
     *
     * @return bool
     */
    public function validatorIsAgent()
    {
        return $this->_income->needIsAgent();
    }

    /**
     * 收入页的前端路由
     * @return string 前端路由名
     */
    public function getAppUrl()
    {
        return  $this->_income->getAppUrl();
    }


    /**
     * 获取收入模型数据
     * @param bool $level
     * @return array
     * @throws AppException
     */
    public function getIncomeData($level = true)
    {
        if (!$this->isShow()) {
            throw new AppException('IncomeFactory' . $this->_income->getTitle() . 'no use');
        }

        return [
            'url' => $this->_income->getAppUrl(),
            'icon' => $this->_income->getIcon(),
            'mark' => $this->getMark(),
            'title' => $this->getTitle(),
            'level' => $level ? $this->_income->getLevel() : '',    //新增参数控制是否获取等级信息
            'value' => $this->getValue(),
            'is_agent' => $this->isAgent(),
            'is_relation' => $this->isRelation(),
            'mini_url' => $this->getMiniUrl(),
        ];
    }


    private function getMark()
    {
        return $this->_income->getMark();
    }

    private function getMiniUrl(){
        if (method_exists($this->_income, 'getMiniUrl')) {
            return $this->_income->getMiniUrl();
        }

        return '';
    }


    public function getTitle()
    {
        $mark = $this->_income->getMark();

        if (isset($this->lang_set[$mark]['title']) && !empty($this->lang_set[$mark]['title'])) {
            return $this->lang_set[$mark]['title'];
        }
        return $this->_income->getTitle();
    }


    /**
     * 收入模型累计收入值
     *
     * @return float
     */
    private function getValue()
    {
	    $value = $this->_income->getTypeValue();//这里是累计金额
	    if (!is_numeric($value)) {
		    $total_income = $this->total_income->where('incometable_type', $value)->first();
		    $value = $total_income ? $total_income->total_amount : '0.00';
	    }
	    if ($this->getSet()) {//后台插件金额显示设置
		    $value = $this->calculation();//计算可提现金额
	    }
	    return $value;
    }
	
	/**
	 * @return mixed
	 * 类型
	 */
    private function getSet()
    {
	    $type_name = $this->_income->getMark();
    	$type = request()->type;
    	
    	switch ($type){
		    case 5: $res = \Setting::get("popularize.wap.".$type_name);  break;
		    case 1: $res = \Setting::get("popularize.wechat.".$type_name);  break;
		    case 2: $res = \Setting::get("popularize.mini.".$type_name);  break;
		    case 9: $res = \Setting::get("popularize.app.".$type_name);  break;
		    case 8: $res = \Setting::get("popularize.alipay.".$type_name);  break;
		    default : $res = \Setting::get("popularize.wechat.".$type_name);  break;
	    }
	    return $res;
    }
	
	/**
	 * 计算手续费独立提现手续费
	 */
	private function calculation()
	{
		$value = Income::getIncomes()->where('member_id', \YunShop::app()->getMemberId())
			->where('incometable_type', $this->_income->getTypeValue())
			->where('status', 0)->sum('amount');
		return $value;
	}


    /**
     * 如果需要验证关系链，返回关系链状态，默认返回 true
     * @return bool
     */
    private function isRelation()
    {
        //已经获得权限不需要验证关系链
        if ($this->isAvailable()) {
            return true;
        }
        if ($this->_income->needIsRelation()) {
            return $this->is_relation;
        }
        return true;
    }


    /**
     * 如果需要验证是否是推客，返回推客状态，默认返回 true
     *
     * @return bool
     */
    private function isAgent()
    {
        //已经获得权限不需要验证是否是推客
        if ($this->isAvailable()) {
            return true;
        }
        if ($this->_income->needIsRelation()) {
            return $this->is_agent;
        }
        return true;
    }

}
