<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-07-02
 * Time: 10:30
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

namespace app\common\models\member;


use app\common\models\BaseModel;

class MemberChangeMobileLog extends BaseModel
{
    public $table = 'yz_change_mobile_log';
    public $guarded = [''];
    public $timestamps = true;

}