<?php

/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/6/11
 * Time: 15:33
 */

//参数获取

include_once "./official/fun.php";

$name = "";
$article_id = 0;
$page = 0;
$cate = 0;
$param = $_SERVER['QUERY_STRING'];
if (!empty($param)) {
    $param = explode("&",$param);

    foreach ($param as $kk=>$vv) {
        if (strpos($vv,'page_name=') !== false) {
            $name = explode('page_name=',$vv)[1];
        }

        if (strpos($vv,'article=') !== false) {
            $article_id = explode('article=',$vv)[1];
        }

        if (strpos($vv,'page=') !== false) {
            $page = explode('page=',$vv)[1];
        }

        if (strpos($vv,'cate=') !== false) {
            $cate = explode('cate=',$vv)[1];
        }
    }
}

if (empty($name)) {
    $name = "default_home";
}

//获取数据库配置
$result = getOfficial($name);

$connect = null;
$top = [];
$custom = [];
$basic = [];
$content = "";
$tail = [];

if (!$result) {
    include_once "./official/common/404.html";
    return ;
}

//处理数据
if ($result) {

    if ($result['top']) {
        $result['top'] = json_decode($result['top'],true);
        $result['top']['logo'] = empty($result['top']['logo']) ? "official/images/footer_qr_contact.png" : $result['top']['logo'];

        if (count($result['top']['navigation'])>0) {
            foreach ($result['top']['navigation'] as $kk=>$vv) {
                $result['top']['navigation'][$kk]['url'] = $vv['url'];
                $tool = explode("page_name=",$vv['url']);
                $result['top']['navigation'][$kk]['tool'] = $tool[1];
            }
        }

        $top = $result['top'];
    }

    $app_top = '';
    $app_bottom='';

//    if ($result['theme_id'] == 21){
//        $app_top = '<div class="swiper-container" style="display: block; height: calc(100vh - 74px)"><div class="swiper-wrapper middle_detail">';
//        $app_bottom = '</div><div class="swiper-pagination"></div></div>';
//    }


    if ($result['basic']) {
        $result['basic'] = json_decode($result['basic'],true);
        if (isset($result['basic']['cus_url'])) {
            $result['basic']['cus_url'] = empty($result['basic']['cus_url']) ? "official/images/footer_qr_contact.png" : $result['basic']['cus_url'];
        }

        $basic = $result['basic'];
    }

    if ($result['tail']) {
        if ($result['tail']) {
            $result['tail'] = htmlspecialchars_decode($result['tail']);
        }

        $tail = $result['tail'];
    }

    $result['detail'] = htmlspecialchars_decode($result['detail']);

    $identification = $result['idft'];

    $title = $result['mul_title'];
    $keywords = $result['keyword'];
    $describe = $result['describe'];
    $content = $result['detail'];
    $article_name = [];
    $article = [];
    $help = [];


    if ($identification == "common") {
        if ($name == "resource_page") {

            $article = empty($result['article']) ? [] : json_decode($result['article'],true);

            if (!empty($article)) {
                foreach ($article as $kk=>$vv) {
                    $data = [];
                    if (!empty($vv['has_many_article'])) {
                        foreach ($vv['has_many_article'] as $k=>$v) {
                            $data[$k]['id'] = $v['id'];
                            $data[$k]['img'] = $v['thumb'];
                            $data[$k]['author'] = $v['author'];
                            $data[$k]['created_at'] = $v['created_at'];
                            $data[$k]['title'] = $v['title'];
                            $data[$k]['content'] = $v['desc'];
                            $data[$k]['url'] = "https://".$_SERVER['HTTP_HOST']."/officialwebsite.php?page_name=resource_detail&article=".$v['id'];
                        }
                    }

                    $article[$kk]['datas'] = $data;
                }
            }

            $help = empty($result['helper']) ? [] : json_decode($result['helper'],true);

            if (!empty($help)) {
                foreach ($help as $kk=>$vv) {
                    $help[$kk]['id'] = $kk;
                }
            }
        }
    } elseif ($identification == "website") {
        //获取文章

        $uniacid = $basic['cus_uniacid'];

        $web_rel = getCommonArticle($uniacid,$cate);

        $pager = $page+1;
        $follow_url = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/admin/followArticle";
        if ($name == "default_home") {
            $contact_url = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/admin/contactInformation";

            $home_data = getReadVolumeArticle($uniacid);
        }

        if ($name == "resource_page") {

            $article_cate_data = getPageCate($uniacid,$cate);

            $article_total_rel = getTotalCate($uniacid,$cate);

        }
    }

    if ($name == "resource_detail") {

        $rel = getDetailArticle($article_id);

        $describe = $rel['desc'];
        $keywords = $rel['keyword'];
        $title = $rel['title']."-".$shop_data['value']['name'];

        $cate_url = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/officialwebsite.php?page_name=resource_page&cate=".$rel['cate_id'];

        $title_url = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/addons/yun_shop/?menu#/articleContent/".$rel['id']."?i=".$rel['uniacid'];

        $shop_data = getShopData();
        $shop_data['value'] = unserialize($shop_data['value']);
        $connect2 = null;
    }

    if (file_exists("./official/".$identification."/".$name.".html")) {
        include_once("./official/".$identification."/".$name.".html");
        return ;
    } else {
        include_once "./official/common/404.html";
        return ;
    }

}











