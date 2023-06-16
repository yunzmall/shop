<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/4/17
 * Time: 下午6:10
 */

namespace app\common\exceptions;


class UniAccountNotFoundException extends ShopException
{

    public function getStatusCode()
    {
        return ErrorCode::UNI_ACCOUNT_NOT_FOUND;
    }

}