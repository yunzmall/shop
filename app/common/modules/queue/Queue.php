<?php


namespace app\common\modules\queue;


use app\common\facades\SiteSetting;
use app\common\modules\shop\ShopConfig;

class Queue
{
    private $setting;
    /**
     * @var self
     */
    static $current;

    /**
     *  constructor.
     */
    public function __construct()
    {
        self::$current = $this;
    }

    static public function current()
    {
        if (!isset(self::$current)) {
            return new static();
        }
        return self::$current;
    }

    private function _setting()
    {
        $result = [];

        $siteSettings = SiteSetting::get('base.queue');

        foreach (ShopConfig::current()->get('queue') as $setting) {
            foreach ($siteSettings as $siteSetting) {
                if ($siteSetting['code'] == $setting['code']) {
                    $setting['num'] = $siteSetting['num'];
                    break;
                }
            }
            $result[] = $setting;
        }

        return $result;
    }

    public function setting()
    {
        if (!isset($this->setting)) {
            $this->setting = $this->_setting();
        }

        return $this->setting;
    }
}