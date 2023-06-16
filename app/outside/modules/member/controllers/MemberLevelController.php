<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2022/3/26
 * Time: 12:39
 */

namespace app\outside\modules\member\controllers;


use app\common\models\MemberLevel;
use app\outside\controllers\OutsideController;

class MemberLevelController extends OutsideController
{
    public function index()
    {
       $list =  MemberLevel::select('id', 'level_name', 'level')->orderBy('level','ASC')->get();


       return $this->successJson('list', $list);
    }
}