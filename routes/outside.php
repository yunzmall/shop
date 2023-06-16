<?php
//Route::any('/', function () {
//    return true;
//});

Route::group(['prefix' => 'outside/{uniacid}'], function () {  //插件路由
    Route::get('index','controllers\IndexController@index');
    Route::get('address','controllers\AddressController@getAddress');
    Route::any('upload','controllers\UploadController@index'); //文件上传

    Route::get('member/level','modules\member\controllers\MemberLevelController@index');
    Route::get('member/info/query','modules\member\controllers\InfoController@query');
    Route::get('member/info/detail','modules\member\controllers\InfoController@detail');
    Route::get('member/address','modules\member\controllers\AddressController@index');

    Route::get('goods/goods/index','modules\goods\controllers\GoodsController@index');

    Route::post('order/buy','modules\order\controllers\BuyController@index');

    //  商城分单问题，第三方下单一个请求号会分成多笔订单
    Route::post('order/create','modules\order\controllers\CreateController@index');
});

