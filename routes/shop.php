<?php
Route::group(['middleware' => ['auth:admin', 'authAdmin', 'authShop']], function () {
	Route::any('shop', function () {
		//在shopRoute中间件重新匹配了路由
		return true;
    });
	return redirect('/');
});
