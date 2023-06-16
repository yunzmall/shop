<?php


namespace app\platform\modules\pluginsSetMeal\models;

use app\common\models\BaseModel;
use app\platform\modules\application\models\UniacidApp;

class pluginsMealPlatform  extends BaseModel
{
    protected $table = 'yz_plugins_meal_platform';
    protected $fillable = ['uniacid','plugins_meal_id'];

    public function hasOnePluginsMeal()
    {
        return $this->hasOne(PluginsMealModel::class,'id','plugins_meal_id');
    }

    public function hasOneUniacidApp()
    {
        return $this->hasOne(UniacidApp::class,'uniacid','uniacid');
    }
}