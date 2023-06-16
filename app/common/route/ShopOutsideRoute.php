<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/1/5
 * Time: 14:05
 */

namespace app\common\route;


use app\common\exceptions\AppException;
use Illuminate\Support\Str;

class ShopOutsideRoute extends AbstractShopRoute
{
    public $namespace = 'app\\outside';

//    protected $middleware = [BasicInformation::class];

    public function __construct($path)
    {
        parent::__construct($path);

    }

    public function shopMatch($routes, $first)
    {
        $namespace = $this->namespace;
        $class_name = '';
        $action = '';
        if (class_exists($namespace.'\\controllers\\'.ucfirst(Str::camel($first)).'Controller')) {
            $class_name = $namespace.'\\controllers\\'.ucfirst(Str::camel($first)).'Controller';
            $action = array_shift($routes);
        } else {
            $namespace .= '\\modules\\'.$first;
            $namespace_module = $namespace;
            foreach ($routes as $route) {
                if ($class_name) {
                    $action = $route;
                    break;
                }
                $controller = ucfirst(Str::camel($route)).'Controller';
                if (class_exists($namespace.'\\controllers\\'.$controller)) {
                    $class_name = $namespace.'\\controllers\\'.$controller;
                } elseif (class_exists($namespace_module.'\\controllers\\'.$controller)) {
                    $class_name = $namespace_module.'\\controllers\\'.$controller;
                } else {
                    $namespace .= '\\'.$route;
                    $namespace_module .= '\\modules\\'.$route;
                }

            }
        }
        return [$class_name,$action];
    }
}