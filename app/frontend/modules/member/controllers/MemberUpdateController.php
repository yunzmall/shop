<?php
/**
 * Created by PhpStorm.
 * User: weifeng
 * Date: 2020-08-12
 * Time: 18:18
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

namespace app\frontend\modules\member\controllers;


use app\common\components\ApiController;
use app\frontend\modules\member\services\MemberUpdateService;

class MemberUpdateController extends ApiController
{
    public function index()
    {
        $type = request()->type;

        $result = (new MemberUpdateService($type))->update();

        if ($result['status'] == 0) {
            return $this->errorJson($result['message']);
        } else {
            return $this->successJson($result['message']);
        }
    }
}