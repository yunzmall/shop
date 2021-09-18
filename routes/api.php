<?php
Route::any('/', function () {
    //支付回调
    if (strpos(request()->getRequestUri(), '/payment/') !== false) {
        preg_match('#(.*)/payment/(\w+)/(\w+).php(.*?)#', request()->getRequestUri(), $match);
        if (isset($match[2])) {
            $namespace      = 'app\\payment\\controllers\\' . ucfirst($match[2]) . 'Controller';
            $modules        = [];
            $controllerName = ucfirst($match[2]);
            $action         = $match[3];
            $currentRoutes  = [];
            Yunshop::run($namespace, $modules, $controllerName, $action, $currentRoutes);
        }
    }
    //api
    if (strpos(request()->getRequestUri(), '/addons/') !== false &&
        strpos(request()->getRequestUri(), '/api.php') !== false
    ) {
        $shop = Setting::get('shop.shop');
        if ($shop['close'] == 1) {
            throw new \app\common\exceptions\AppException('站点已关闭', -1);
        }
        return YunShop::parseRoute(request()->input('route'));
    }
	return;
});



