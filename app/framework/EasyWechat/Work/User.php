<?php

namespace app\framework\EasyWechat\Work;

use EasyWeChat\Work\User\Client as BaseClient;


class User extends BaseClient
{

    public function getUserInfo(array $data)
    {
        return $this->httpGet('cgi-bin/user/getuserinfo', $data);
    }


    public function getUserDetail(array $data)
    {
        return $this->httpPostJson('cgi-bin/user/getuserdetail',$data);
    }

}
