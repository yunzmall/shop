<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2022/2/10
 * Time: 18:12
 */

namespace app\outside\controllers;


class UploadController extends OutsideController
{
    public function index()
    {
        try{
            return (new \app\frontend\controllers\UploadController())->uploadPic();
        }catch (\Exception $e) {
            return $this->errorJson($e->getMessage());
        }
    }
}