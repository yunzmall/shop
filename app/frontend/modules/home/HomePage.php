<?php

namespace app\frontend\modules\home;


class HomePage
{
    protected $designer = [
        'livestreaming',            //带货直播
        'business',                 //商家优惠券
        'article',                  //文章
        'store',                    //附近门店
    ];

    /**
     * 处理首页页面数据
     *@param $data 组件详细信息 
     */
    public function getList($data)
    {
        foreach ($data as $key => $value) 
        {
            if (in_array($value['temp'],$this->designer))
            {
                $data[$key] = $this->{$value['temp']}($value);
            }

        }
        return $data;
    }

    /**
     * 首页带货直播
     *@param $value 组件详细信息
     */
    protected function livestreaming($value)
    {
        //插件是否启用
        if (!\YunShop::plugin()->get('room')) 
        {
            return $value;
        }

        $params = [];
        if ($value['params']['number']) //页面显示数量
        {
            $params['page_size'] = $value['params']['number'];
        }

        $LiveListController =  new \Yunshop\Room\frontend\LiveListController();
        switch ($value['params']['type']) //类型
        {
            case '0':
                $result = $LiveListController->getAllLiveList(request(),$params); //全部直播
                break;

            case '1':
                $result = $LiveListController->getLiveRecommend(request(),$params); //推荐直播
                //
                break;

            case '2':
                $result = $LiveListController->getLiveList(request(),$params); //直播中
                break;

            case '3':
                $result = $LiveListController->getLiveList(request(),$params); //直播预告
                break;

            case '4':
                $result = $LiveListController->playBack(request(),$params); //精彩回放
                break;
            
            default:
                return $value;
                break;
        }

        if (is_object($result)) {
            $result = $result->getData(true);
        }
        
        if ($result['data']['data']) 
        {
            $value['data'] = $result['data']['data']; 
        }elseif($result['data'] && !isset($result['data']['data'])){
            $value['data'] = $result['data']; 
        }
        return $value;
    }

    /**
     * 首页文章
     *@param $value 组件详细信息
     */
    protected function article($value)
    {    
        //文章插件没有启用 或者 文章不是自动获取
        if (!\YunShop::plugin()->get('article') || $value['params']['addmethod'] == 1) 
        {
            return $value;
        }

        request()->pageSize = isset($value['params']['shownum'])?$value['params']['shownum']:0; //文章首页显示条数
        $ArticlePageController =  new \Yunshop\Article\api\ArticlePageController();
        $result = $ArticlePageController->page()->getData(true);

        //文章数据
        if ($result['data']['data']) 
        {
            $value['data'] = $result['data']['data']; 
        }
        
        return $value;
    }

    /**
     * 门店优惠券（暂时不优化）
     *@param $value 组件详细信息
     */
    protected function business($value)
    {
        return $value;
    }

    /**
     * 门店（暂时不优化）
     *@param $value 组件详细信息
     */
    protected function store($value)
    {
        return $value;
    }
}