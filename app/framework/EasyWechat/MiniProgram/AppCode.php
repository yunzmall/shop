<?php

namespace app\framework\EasyWechat\MiniProgram;

use EasyWeChat\MiniProgram\AppCode\Client as BaseClient;


class AppCode extends BaseClient
{
    public function getUrlLink(string $path, array $optional = [])
    {
        $params = array_merge([
            'path' => $path,
        ], $optional);

        return $this->getStream('wxa/generate_urllink', $params);
    }
}