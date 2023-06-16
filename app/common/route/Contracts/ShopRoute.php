<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/8/20
 * Time: 14:17
 */
namespace app\common\route\Contracts;

interface ShopRoute
{
	public function pluginMatch($routes);
	public function shopMatch($routes,$first);
}