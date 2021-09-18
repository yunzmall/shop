<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/7/6
 * Time: 14:07
 */

namespace app\common\services;

use app\common\services\Utils;

class LangService
{
    public static function getCurrentLang()
    {
        $content = (new self())->getFileContent();

        return json_decode($content, true);
    }

    /**
     * 读取文件内容
     * @return bool|string
     */
    public function getFileContent()
    {
        $file_path = $this->getPath();

        $content = file_get_contents($file_path . '/language.json');
        if (strlen($content) < 1) {
            $content = $this->getDefault();
        }

        return $content;
    }


    /**
     * 获取文件目录
     * @return string
     */
    public function getPath()
    {

        if (config('app.framework') == 'platform') {
            $file_dir = base_path().'/addons/yun_shop/static';
        } else {
            $file_dir = base_path().'/static';
        }
        $path = $file_dir.'/locales/'.\YunShop::app()->uniacid;

        if (!is_dir($path)) {
            Utils::mkdirs($path);
        }

        return $path;
    }

    /**
     * 默认语言设置
     * @return bool|string
     */
    public function getDefault()
    {

        $file = base_path().'/static/language.json';

        return file_get_contents($file);
    }
}