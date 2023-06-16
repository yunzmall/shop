<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/9/8
 * Time: 14:13
 */

namespace app\backend\modules\goods\widget;

use app\common\helpers\Url;
use app\common\models\Goods;
use app\framework\Http\Request;
use Illuminate\Contracts\Support\Arrayable;
/**
 * Class BaseGoodsWidget
 * @package app\backend\modules\goods\widget
 * @property Goods goods
 */
abstract class BaseGoodsWidget  implements Arrayable
{

//    private $module = [
//    'base' => '商品信息',
//    'tool' => '商品工具',
//    'marketing' => '营销设置',
//    'profit' => '分润设置',
//    'industry' => '行业设置'
//    ];

    private $route; //路由

    protected $goods;

    protected $whiteList = []; //挂件白名单

    protected $blackList = []; //挂件黑名单

    public $title; //挂件名称

    public $group; //挂件所属分类

    public $code; //挂件唯一标识需与组件文件名称保持一致

    public $widget_key = ''; //挂件数据集合键名


    protected $request;


    public function __construct($goods)
    {
        $this->goods = $goods;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getTitle()
    {
        return $this->title;
    }

    protected function setRoute($route)
    {
        $this->route = $route;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getWidgetKey()
    {
        return $this->widget_key;
    }

    //filter
    public function insideAuthorization($route)
    {
        $this->setRoute($route);
        //plugin white list filter
        if ($this->isAllow($route)) {
            return true;
        }
        //plugin black list filter
        if ($this->isBarred($route)) {
            return false;
        }

        return $this->usable();
    }


    /**
     * @param $route string
     * @return bool
     */
    protected function isAllow($route)
    {
        return $this->whiteList() && in_array($route, $this->whiteList());
    }

    /**
     * @param $route string
     * @return bool
     */
    protected function isBarred($route)
    {
        return in_array($route, $this->blackList()) || $this->blackList() == ['*'];
    }

    protected function whiteList()
    {
        return $this->whiteList;
    }

    protected function blackList()
    {
       return $this->blackList;
    }

    protected function getCode()
    {
        return $this->code;
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'group' =>$this->getGroup(), //模板分组
            'title' =>$this->getTitle(), //模板名称
            'attr_hide' => $this->attrHide(),
            'template_code' =>$this->getCode(), //模板名称
            'page_path' => $this->pagePath(), //模板引入路径
            'widget_key' => $this->getWidgetKey(), //模板数据提交键名
            'data' => $this->getWidgetData(), //模板数据
        ];
    }

    public function attrHide()
    {
        return [];
    }


    public function getWidgetData()
    {

        if ($this->defaultValuePlugin()) {
            $config = \Yunshop\GoodsDefaultValue\common\WidgetConfig::appointWidget($this->getWidgetKey());
            if ($config) {
                /**
                 * @var BaseGoodsWidget $widgetClass
                 */
                $widgetClass = new $config['class']($this->goods);
                $widgetClass->setRoute($this->getRoute());
                $widgetClass->setTitle($config['title']);
                $widgetClass->setRequest($this->getRequest());

                if ($widgetClass->usable()) {
                    return $widgetClass->getData();

                }
            }

        }

        return $this->getData();
    }

    protected function defaultValuePlugin()
    {
        //是否新添加商品
        if ($this->goods || !app('plugins')->isEnabled('goods-default-value')) {
            return false;
        }

        return  \Yunshop\GoodsDefaultValue\common\EditPageWidget::isOpen($this->getRoute(), $this->getWidgetKey());
    }


    protected function getPath($path)
    {
        return  Url::shopUrl($path);
    }

    /**
     * @param $request
     */
    public function setRequest($request)
    {
        if (is_null($request)) {
            $this->request = request();
        }
        $this->request = $request;
    }

    /**
     * 获取request对象
     * @return Request
     */
    protected function getRequest($key = null)
    {
        if (!isset($this->request)) {
            $this->request = request();
        }

        if ($key) {
            return $this->request->input($key, '');
        }

        return $this->request;
    }

    /**
     * 权限判断
     * @return boolean
     */
    public function usable()
    {
        return true;
    }

    abstract public function getData();

    //挂件页面路径给前端引入
    abstract public function pagePath();


    //插件文件名称
    abstract public function pluginFileName();


}