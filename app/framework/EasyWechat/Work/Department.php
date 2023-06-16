<?php

namespace app\framework\EasyWechat\Work;

use EasyWeChat\Work\Department\Client as BaseClient;


class Department extends BaseClient
{


    public function departmentIds($data = [])
    {
        return $this->httpPostJson('cgi-bin/department/simplelist', $data);
    }

    public function departmentDetail($id)
    {
        return $this->httpPostJson('cgi-bin/department/get', ['id' => $id]);
    }


}
