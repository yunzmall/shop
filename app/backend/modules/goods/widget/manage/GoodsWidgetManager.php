<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2021/9/14
 * Time: 15:02
 */

namespace app\backend\modules\goods\widget\manage;

use app\backend\modules\goods\models\Goods;
use app\backend\modules\goods\widget\BaseGoodsWidget;
use Illuminate\Http\Request;
use app\common\helpers\Url;

class GoodsWidgetManager
{

    public $request;

    public function __construct($attributes = [])
    {

    }

    public function handle($request = null)
    {

        $this->setRequest($request);

        $goods_widget = $this->getSetting();

        $route = $this->currentRoute($this->getRequest()->input('route'));

        $goodsModel = $this->getGoodsModel($this->getRequest()->input('id'));

        $widgetClassCollect = collect([]);
        foreach ($goods_widget as $configItem) {

            if (class_exists($configItem['class'])) {
                /**
                 * @var BaseGoodsWidget $widgetClass
                 */
                $widgetClass = new $configItem['class']($goodsModel);
                if ($widgetClass->code == 'goods' && request()->goods_type == 1) {
                    $widgetClass->code = 'code';
                }
                //通过验证返回
                if ($widgetClass->insideAuthorization($route)) {
                    $widgetClass->setTitle($configItem['title']);
                    $widgetClass->setRequest($this->parameter());
                    if (!$this->externalFilter($widgetClass)) {
                        $widgetClassCollect->push($widgetClass);
                    }
                }
            }
        }

        $widgetColumns = $widgetClassCollect->groupBy(function (BaseGoodsWidget $widget) {
            return $widget->getGroup();
        })->toArray();

        $result = [];
        foreach ($this->getGroup() as $key => $name) {
            if ($widgetColumns[$key]) {
                $result[] = [
                    'group' => $key,
                    'title' => $name,
                    'column' => $widgetColumns[$key],
                ];
            }
        }

        return $result;
    }



    /**
     * 外部禁用某个挂件
     * @param $widgetClass
     * @return bool true 禁用 false 启用
     */
    public function externalFilter($widgetClass)
    {
        return false;
    }

    public function getSetting()
    {
        return \app\common\modules\widget\Widget::current()->getItem('vue-goods');
    }

    public function parameter()
    {
        return request();
    }

    /**
     * 通过路由解析区分商品编辑是那个插件
     * @param $requestRoute
     * @return string 当前页面所属者
     */
    public function currentRoute($requestRoute)
    {

        $routesParams = explode('.', $requestRoute);
        $routeFirst = array_first($routesParams);
        if ($routeFirst === 'plugin') {
            $currentRoute = $routesParams[1];
        } else {
            $currentRoute = 'shop';
        }
        return $currentRoute;
    }

    public function getGoodsModel($goods_id)
    {
        return Goods::withoutGlobalScopes()->find($goods_id);
    }

    protected function getPath($path)
    {
        return  Url::shopUrl($path);
    }

    protected function getGroup()
    {
        $group = [
            'base' => '商品信息', 'tool' => '商品工具', 'marketing' => '营销设置',
            'profit' => '分润设置', 'industry' => '行业设置'
        ];

        return $group;
    }

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
            return $this->request->input($key, null);
        }

        return $this->request;
    }
}