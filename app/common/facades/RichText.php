<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2021-04-19
 * Time: 18:53
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

namespace app\common\facades;


use app\common\models\RichTextModel;
use Illuminate\Support\Facades\Facade;

class RichText extends Facade
{
    public static $uniqueAccountId = 0;
    private static $instance;

    protected static function getFacadeAccessor()
    {
        return 'richText';
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new RichTextModel();
        }
        return self::$instance;
    }

    public static function set($key, $value = null)
    {
        return self::getInstance()->setValue(self::$uniqueAccountId, $key, $value);
    }

    public static function get($key, $default = null)
    {
        return self::getInstance()->getValue(self::$uniqueAccountId, $key, $default);
    }
}