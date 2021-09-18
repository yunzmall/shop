<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2021-04-19
 * Time: 17:28
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

namespace app\common\models;


class RichTextModel extends BaseModel
{
    public $table = 'yz_rich_text';
    public $guarded = [''];
    public $timestamps = true;

    public $defaultGroup = 'shop';

    public function getValue($uniqueAccountId, $key, $default = null)
    {
        list($group, $groupKey) = $this->parseKey($key);
        $settingGroupItems = $this->getItems($uniqueAccountId, $group);
        $value = array_get($settingGroupItems, $groupKey, $default);
        return $value;
    }

    public function setValue($uniqueAccountId, $key, $value = null)
    {
        list($group, $groupKey) = $this->parseKey($key);
        $result = $this->saveData($uniqueAccountId, $groupKey, $value, $group);
        return $result;
    }

    protected function parseKey($key)
    {
        $explodedGroup = explode('.', $key);
        if (count($explodedGroup) > 1) {
            $group = array_shift($explodedGroup);
            $groupKey = implode('.', $explodedGroup);
        } else {
            $group = $this->defaultGroup;
            $groupKey = $explodedGroup[0];
        }
        return [$group, $groupKey];
    }

    protected function getItems($uniqueAccountId, $group)
    {
        $items = [];
        $data = self::where(['uniacid'=>$uniqueAccountId,'group'=>$group])->get()->toArray();
        foreach ($data as $value) {
            $items[$value['key']] = $value['value'];
        }
        return $items;
    }

    protected function saveData($uniqueAccountId, $key, $value, $group)
    {
        $model = self::where(['key'=>$key,'uniacid'=>$uniqueAccountId,'group'=>$group])->first();
        if (!$model) {
            $data = [
                'uniacid' => $uniqueAccountId,
                'key' => $key,
                'value' => $value,
                'group' => $group,
                'created_at' => time(),
            ];
            return self::create($data);
        } else {
            $model->value = $value;
            return $model->save();
        }
    }
}