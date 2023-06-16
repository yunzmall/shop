<?php

namespace app\common\services\member\center;

abstract class BaseMemberCenterService
{
    public function isBackendRoute()
    {
        return \YunShop::isWeb();
    }
}