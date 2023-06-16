<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/28
 * Time: 下午4:36
 */

namespace app\common\exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class NotFoundException extends NotFoundHttpException
{
    public function getStatusCode()
    {
        return 404;
    }

    public function getHeaders()
    {
        return [];
    }
}