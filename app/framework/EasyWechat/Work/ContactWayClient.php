<?php
namespace app\framework\EasyWechat\Work;

use EasyWeChat\Work\ExternalContact\ContactWayClient as BaseClient;


class ContactWayClient extends BaseClient
{


    /*
     * 删除进群码
     */
    public function deleteGroupCode(string $config_id)
    {
        $params = [
            'config_id' => $config_id,
        ];
        return $this->httpPostJson('cgi-bin/externalcontact/groupchat/del_join_way', $params);
    }

    /*
     * 获取进群码
     */
    public function getGroupCode(string $config_id)
    {
        $params = [
            'config_id' => $config_id,
        ];
        return $this->httpPostJson('cgi-bin/externalcontact/groupchat/get_join_way', $params);
    }


    /*
     * 添加进群码
     */
    public function addGroupCode(array $data = [])
    {
        $params = [
            'scene' => isset($data['scene']) ? $data['scene'] : 2,
            'remark' => isset($data['remark']) ? $data['remark'] : '',
            'auto_create_room' => isset($data['auto_create_room']) ? $data['auto_create_room'] : 1,
            'chat_id_list' => $data['chat_id_list'],
        ];

        if (isset($data['state'])) {
            $params['state'] = $data['state'];
        }

        if ($params['auto_create_room']) {
            $params['room_base_name'] = isset($data['room_base_name']) ? trim($data['room_base_name']) : '客户群';
            $params['room_base_id'] = isset($data['room_base_id']) ? intval($data['room_base_id']) : '1';
        }

        return $this->httpPostJson('cgi-bin/externalcontact/groupchat/add_join_way', $params);
    }

    /**
     * 更新进群码
     * @param array $data
     * @return array|\EasyWeChat\Kernel\Support\Collection|object|\Psr\Http\Message\ResponseInterface|string
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function editGroupCode(array $data = [])
    {
        $params = [
            'config_id' => $data['config_id'],
            'scene' => isset($data['scene']) ? $data['scene'] : 2,
            'remark' => isset($data['remark']) ? $data['remark'] : '',
            'auto_create_room' => isset($data['auto_create_room']) ? $data['auto_create_room'] : 1,
            'chat_id_list' => $data['chat_id_list'],
        ];

        if (isset($data['state'])) {
            $params['state'] = $data['state'];
        }

        if ($params['auto_create_room']) {
            $params['room_base_name'] = isset($data['room_base_name']) ? trim($data['room_base_name']) : '客户群';
            $params['room_base_id'] = isset($data['room_base_id']) ? intval($data['room_base_id']) : '1';
        }

        return $this->httpPostJson('cgi-bin/externalcontact/groupchat/update_join_way', $params);
    }
}
