<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-09-04
 * Time: 14:02
 *
 *    .--,       .--,
 *   ( (  \.---./  ) )
 *    '.__/o   o\__.'
 *       {=  ^  =}
 *        >  -  <
 *       /       \
 *      //       \\
 *     //|   .   |\\
 *     "'\       /'"_.-~^`'-.
 *        \  _  /--'         `
 *      ___)( )(___
 *     (((__) (__)))     梦之所想,心之所向.
 */

namespace app\frontend\modules\member\controllers;


use app\common\components\ApiController;

class MiniDecryptController extends ApiController
{
    public function index()
    {
        include dirname(__FILE__) . "/../vendors/wechat/wxBizDataCrypt.php";

        $min_set = \Setting::get('plugin.min_app');

        if (is_null($min_set) || 0 == $min_set['switch']) {
            return show_json(0, '未开启小程序');
        }

        //小程序登录后返回的code
        $para = request()->para_arr;

        $data = '';
        $errCode = '';
        if (!empty($para['info'])) {
            $json_data = $para['info'];

            $pc = new \WXBizDataCrypt($min_set['key'], $para['session_key']);
            $errCode = $pc->decryptData($json_data['encryptedData'], $json_data['iv'], $data);
        }

        if ($errCode == 0) {
            $json_data = json_decode($data, true);
            return $this->successJson('ok', $json_data);
        } else {
            return $this->errorJson('解密失败', [
                'error_code' => $errCode,
            ]);
        }
    }

    public function getSessionKey()
    {
        $session_id = request()->session_key_id;

        $data = $_SESSION['wx_app'][$session_id];
        $session_key_data = unserialize($data);

        return $this->successJson('ok', [
            'session_key_data' => $session_key_data,
        ]);
    }
}