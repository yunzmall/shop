<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2022/5/7
 * Time: 16:52
 * Author: Merlin
 */

namespace app\process;

/**
 * 内部通讯
 */
class InnerSocket
{
    /**
     * @param array $ids
     * @param string $content
     * @param string $type
     * @return bool
     */
    public static function send(array $ids, array $content, string $type)
    {
        $data['ids'] = $ids;
        $data['content'] = $content;
        $data['type'] = $type;
        $client = stream_socket_client('tcp://127.0.0.1:5678', $errno, $errmsg, 1);
        fwrite($client, json_encode($data)."\n");
        $result = fread($client,8192);
        if ($result == "success\n") {
            return true;
        }
        return str_replace("\n","",$result);
    }
}