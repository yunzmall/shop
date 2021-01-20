<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/24
 * Time: 11:49
 */

namespace app\platform\controllers;


class CenterController extends BaseController
{
    public function index()
    {

        $set = \DB::table("yz_setting")->where("uniacid",0)->where("group","official_website")->where("key","theme_set")->value("value");

        $set = empty($set) ? [] : unserialize($set);

        $url = request()->getSchemeAndHttpHost()."/officialwebsite.php?page_name=default_home";

        if (empty($set)) {
            return $this->successJson("",['is_open'=>1,'url'=>$url]);
        } else {
            return $this->successJson("",['is_open'=>$set['is_open'],'url'=>$url]);
        }
    }
}