<?php
/**
 * Created by PhpStorm.
 * 
 * 
 *
 * Date: 2021/6/25
 * Time: 15:23
 */

namespace app\common\models;


class OrderBehalfPayRecord extends BaseModel
{
	public $table = 'yz_order_behalf_pay_record';
	protected $casts = ['order_ids' => 'json'];
	protected $guarded = [''];
}