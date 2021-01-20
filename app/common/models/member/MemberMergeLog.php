<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-06-04
 * Time: 14:10
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

class MemberMergeLog extends BaseModel
{
    public $table = 'yz_member_merge_log';
    public $guarded = [''];
    public $timestamps = true;
}