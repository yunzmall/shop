<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2021-02-22
 * Time: 14:29
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


class QrCode extends BaseModel
{
    protected $table = 'qrcode';
    protected $guarded = [''];
    public $timestamps = false;

    const MAX_FOREVER_QRCODE_LIMIT = 100000; //微信对"永久二维码"的总数有限制, 必须在100000以内
    const TEMPORARY_QRCODE = 1; //临时二维码
    const FOREVER_QRCODE = 2; //永久二维码

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('APP_Framework') == 'platform') {
            $this->table = 'yz_qrcode';
        } else {
            $this->table = 'qrcode';
        }
    }
}