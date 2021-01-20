<?php


namespace app\framework\Database;


use app\common\facades\Setting;
use Illuminate\Support\Facades\DB;

class DatabaseManager extends \Illuminate\Database\DatabaseManager
{
    public $cacheSelect = false;
    private $openCacheSelect;

    /**
     * @return bool
     */
    public function openCacheSelect()
    {
        if (!isset($this->isCacheSelect)) {
            // 获取设置时，不走缓存，避免死循环
            $temp = $this->cacheSelect;
            $this->cacheSelect = false;
            $this->openCacheSelect = Setting::get('shop.cache.select.open');
            $this->cacheSelect = $temp;
        }
        return $this->openCacheSelect;

    }

    public function __get($name)
    {
        if ($name == 'openCacheSelect') {
            return $this->openCacheSelect();
        }
    }

    public function refresh()
    {
        app()->forgetInstance('db');
        DB::clearResolvedInstance('db');
        (new DatabaseServiceProvider(app()))->boot();
    }

}
