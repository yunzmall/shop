<?php

namespace app\common\services\wechatApiV3;

class ApiV3Status
{
    /**
     * 判定成功的http状态码
     */
    const SUCCESS_HTTP_CODE = [
        200,204
    ];

    const NO_CHECK_SIGN_ERROR_CODE = [
        'PARAM_ERROR','	SIGN_ERROR','INVALID_REQUEST'
    ];

    /**
     * @param $http_status
     * @return int
     */
    public static function returnCode($http_status):int
    {
        if (in_array($http_status,self::SUCCESS_HTTP_CODE)) {
            return 1;
        }
        return 0;
    }

    /**
     * 是否需要应答验签
     * @param $err_code
     * @return bool
     */
    public static function isCheckSign($err_code):bool
    {
        if (in_array($err_code,self::NO_CHECK_SIGN_ERROR_CODE)) {
            return false;
        }
        return true;
    }
}