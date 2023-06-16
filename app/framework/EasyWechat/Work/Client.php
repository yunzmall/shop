<?php

namespace app\framework\EasyWechat\Work;

use EasyWeChat\Work\ExternalContact\Client as BaseClient;


class Client extends BaseClient
{


    public function getCorpTags(array $tagIds = [], array $groupIds = [])
    {
        $params = [
            'tag_id' => $tagIds,
            'group_id' => $groupIds
        ];

        return $this->httpPostJson('cgi-bin/externalcontact/get_corp_tag_list', $params);
    }

    public function getGroupChat(string $chatId)
    {
        $params = [
            'chat_id' => $chatId,
            'need_name' => 1,
        ];

        return $this->httpPostJson('cgi-bin/externalcontact/groupchat/get', $params);
    }

}
