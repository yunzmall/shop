<?php

namespace app\common\modules\site;


class SiteSetting
{
    private $setting;
    private $expiredTimestamp;


    public function loadSettingFromCache()
    {
        $this->setting = \app\common\facades\SiteSettingCache::get();
        $this->expiredTimestamp = time() + 20;

    }

    public function getSetting()
    {
        if (!isset($this->setting)) {
            $this->loadSettingFromCache();
        }
        if (time() > $this->expiredTimestamp) {
            $this->loadSettingFromCache();
        }
        return $this->setting;

    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return array_has($this->getSetting(), $key);
    }

    /**
     * @param $key
     * @param null $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return array_get($this->getSetting(), $key, $default);

    }

    /**
     * @param $key
     * @param $value
     * @param null $minutes
     * @return mixed
     */
    public function set($key, $value)
    {
        \app\common\facades\SiteSettingCache::put($key, $value);
    }
}