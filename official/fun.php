<?php
/**
 * Created by PhpStorm.
 * User: yunzhong
 * Date: 2020/12/17
 * Time: 11:34
 */

function returnUrl()
{
    $host     = "";
    $port     = 0;
    $database = "";
    $username = "";
    $pass     = "";
    $tablepre = "ims_";
    //获取数据库配置
    if (file_exists("./database/config.php")) {
        include("./database/config.php");
        $host = $config['db']['master']['host'];
        $port = $config['db']['master']['port'];
        $database = $config['db']['master']['database'];
        $username = $config['db']['master']['username'];
        $pass     = $config['db']['master']['password'];
        $tablepre = $config['db']['master']['tablepre'];
    } else {

        $env = file_get_contents('./.env');
        $env = str_replace("\n",",",$env);
        $env = str_replace(",,",",",$env);
        $env = rtrim($env,',');
        $env = explode(",",$env);

        if (is_array($env) && count($env) > 0) {
            foreach ($env as $kk => $vv) {
                if (strstr($vv, "DB_HOST")) {
                    $host = explode("=", $vv)[1];
                }

                if (strstr($vv, "DB_PORT")) {
                    $port = explode("=", $vv)[1];
                }

                if (strstr($vv, "DB_DATABASE")) {
                    $database = explode("=", $vv)[1];
                }

                if (strstr($vv, "DB_USERNAME")) {
                    $username = explode("=", $vv)[1];
                }

                if (strstr($vv, "DB_PASSWORD")) {
                    $pass = explode("=", $vv)[1];
                }

                if (strstr($vv, "DB_PREFIX")) {
                    $tablepre = explode("=", $vv)[1];
                }
            }
        }
    }

    if ($host != "''" && $database != "''" && $username != "''" && $pass != "''") {
        $sql = "select * from " . $tablepre . "yz_setting where uniacid = 0 and `group` = 'official_website' and `key` = 'theme_set'";

        try {
            $connect = new \PDO("mysql:host=" . $host . ";dbname=" . $database . ";port=" . $port, $username, $pass);
			$connect->prepare('set names utf8mb4')->execute();

            $res = $connect->query($sql);

            $data = $res->fetch();
        } catch (\PDOException $e) {
            echo "Error!: " . $e->getMessage() . "<br/>";
            die();
        }

        $set = unserialize($data['value']);

        if ($set['is_open'] == 2) {

            $theme_sql = "select * from " .$tablepre ."yz_official_website_theme_set where `identification` = 'uniacid_theme' and `is_default` = 1";

            try {
                $theme_connect = new \PDO("mysql:host=" . $host . ";dbname=" . $database . ";port=" . $port, $username, $pass);
				$theme_connect->prepare('set names utf8mb4')->execute();

				$theme_res = $theme_connect->query($theme_sql);

                $theme_data = $theme_res->fetch();

                if ($theme_data) {
                    //https://dev8.yunzmall.com/plugins/shop_server/home?i=1
                    $theme_result = !empty($theme_data['basic']) ? json_decode($theme_data['basic'],true) : [];
                    $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . "/plugins/shop_server/home?i=".$theme_result['cus_uniacid'];
                } else {

                    $default_sql = "select * from " .$tablepre ."yz_official_website_theme_set where `identification` != 'uniacid_theme' and `is_default` = 1";

                    $default_connect = new \PDO("mysql:host=" . $host . ";dbname=" . $database . ";port=" . $port, $username, $pass);
					$default_connect->prepare('set names utf8mb4')->execute();

                    $default_res = $default_connect->query($default_sql);

                    $default_data = $default_res->fetch();

                    
                    

                    if ($default_data) {
                        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/officialwebsite.php?page_name=default_home';
                    } elseif($set['is_customize'] == '1' && $set['customize_url']) {
                        $url = $set['customize_url']; //自定义跳转链接
                    } else {
                        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/admin.html';
                    }
                }
            } catch (\PDOException $e) {
                echo "Error!: " . $e->getMessage() . "<br/>";
                die();
            }

        } else {
            $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/admin.html';
        }

    } else {
        $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/admin.html';
    }

    return $url;
}


function getPei()
{
    $host = "";
    $port = 0;
    $database = "";
    $username = "";
    $pass = "";
    $tablepre = "ims_";

    if (file_exists("./database/config.php")) {
        include("./database/config.php");
        $host = $config['db']['master']['host'];
        $port = $config['db']['master']['port'];
        $database = $config['db']['master']['database'];
        $username = $config['db']['master']['username'];
        $pass = $config['db']['master']['password'];
        $tablepre = $config['db']['master']['tablepre'];
    } else {

        $env = file_get_contents('./.env');
        $env = str_replace("\n",",",$env);
        $env = str_replace(",,",",",$env);
        $env = rtrim($env,',');
        $env = explode(",",$env);

        if (is_array($env) && count($env) > 0) {
            foreach ($env as $kk=>$vv) {
                if (strstr($vv,"DB_HOST")) {
                    $host = explode("=",$vv)[1];
                }

                if (strstr($vv,"DB_PORT")) {
                    $port = explode("=",$vv)[1];
                }

                if (strstr($vv,"DB_DATABASE")) {
                    $database = explode("=",$vv)[1];
                }

                if (strstr($vv,"DB_USERNAME")) {
                    $username = explode("=",$vv)[1];
                }

                if (strstr($vv,"DB_PASSWORD")) {
                    $pass = explode("=",$vv)[1];
                }

                if (strstr($vv,"DB_PREFIX")) {
                    $tablepre = explode("=",$vv)[1];
                }
            }
        }
    }

    return ["host"=>$host,"port"=>$port,"database"=>$database,"username"=>$username,"pass"=>$pass,"tablepre"=>$tablepre];

}

function getOfficial($name)
{
    $data = getPei();
    $host = $data['host'];
    $port = $data['port'];
    $database = $data['database'];
    $username = $data['username'];
    $pass = $data['pass'];
    $tablepre = $data['tablepre'];

    $sql = " select ywm.*,ywt.identification as idft,ywt.top,ywt.basic,ywt.tail from ".$tablepre."yz_official_website_multiple as ywm LEFT JOIN ".$tablepre."yz_official_website_theme_set as ywt on ywm.theme_id=ywt.id where ywm.identification = :identification and ywt.is_default = 1";

    $connect = new \PDO("mysql:host=".$host.";dbname=".$database.";port=".$port,$username,$pass);
	$connect->prepare('set names utf8mb4')->execute();

    $official = $connect->prepare($sql);

    $official->bindParam(':identification',$name,PDO::PARAM_STR,15);

    $official->execute();

    $result = $official->fetch();

    $result['thumb'] = empty($result['thumb']) ? "" : yz_tomedia($result['thumb']);

    return $result;
}

function getCommonArticle($uniacid,$cate=0)
{
    $data = getPei();
    $host = $data['host'];
    $port = $data['port'];
    $database = $data['database'];
    $username = $data['username'];
    $pass = $data['pass'];
    $tablepre = $data['tablepre'];

    $web_article_sql = "select ypa.id,ypa.category_id,ypa.title,ypa.`desc`,ypa.thumb,ypa.author,ypa.created_at,ypa.content,ypac.name,ypac.cate_desc,ypac.cate_img from ".$tablepre."yz_plugin_article as ypa left join ".$tablepre."yz_plugin_article_category as ypac on ypa.category_id = ypac.id where state = 1 and type != 1 and ypa.uniacid = ".$uniacid;

    if (!empty($cate)) {
        $web_article_sql .= " and ypa.category_id = :cate ";
    }

    $web_article_sql .= " order by ypa.id desc limit 6";

    $connect3 = new \PDO("mysql:host=".$host.";dbname=".$database.";port=".$port,$username,$pass);
	$connect3->prepare('set names utf8mb4')->execute();

    $web_res = $connect3->prepare($web_article_sql);

    $web_res->bindParam(':cate',$cate,PDO::PARAM_INT);

    $web_res->execute();

    $web_rel = $web_res->fetchAll();

    if ($web_rel) {
        foreach ($web_rel as $key=>$value) {
            $web_rel[$key]['web_url'] = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/officialwebsite.php?page_name=resource_detail&article=".$value['id'];
            $web_rel[$key]['thumb'] = empty($value['thumb']) ? "" : yz_tomedia($value['thumb']);
        }
    }
    $connect3 = null;
    return $web_rel;
}

function getReadVolumeArticle($uniacid)
{
    $data = getPei();
    $host = $data['host'];
    $port = $data['port'];
    $database = $data['database'];
    $username = $data['username'];
    $pass = $data['pass'];
    $tablepre = $data['tablepre'];

    $home_article_sql = "select id,category_id,title,`desc`,thumb,author,created_at,content from ".$tablepre."yz_plugin_article where state = 1 and type != 1 and uniacid = ".$uniacid." order by `id` desc limit 0,6 ";

    $connect4 = new \PDO("mysql:host=".$host.";dbname=".$database.";port=".$port,$username,$pass);
	$connect4->prepare('set names utf8mb4')->execute();

    $res_home = $connect4->query($home_article_sql);

    $home_data = $res_home->fetchAll();

    if ($home_data) {
        foreach ($home_data as $key=>$value) {
            $home_data[$key]['web_url'] = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/officialwebsite.php?page_name=resource_detail&article=".$value['id'];
            $home_data[$key]['thumb'] = empty($value['thumb']) ? "" : yz_tomedia($value['thumb']);
        }
    }

    $connect4 = null;

    return $home_data;
}

function getPageCate($uniacid,$cate=0)
{
    $data = getPei();
    $host = $data['host'];
    $port = $data['port'];
    $database = $data['database'];
    $username = $data['username'];
    $pass = $data['pass'];
    $tablepre = $data['tablepre'];

    $article_cate_sql = "select id,name,cate_desc,cate_img from ".$tablepre."yz_plugin_article_category where uniacid = ".$uniacid;

    if (!empty($cate)) {
        $article_cate_sql .= " and id = :cate ";
    }

    $article_cate_sql ." limit 1";

    $connect5 = new \PDO("mysql:host=".$host.";dbname=".$database.";port=".$port,$username,$pass);
	$connect5->prepare('set names utf8mb4')->execute();

    $res_article_cate = $connect5->prepare($article_cate_sql);

    $res_article_cate->bindParam(':cate',$cate,PDO::PARAM_INT);

    $res_article_cate->execute();

    $article_cate_data = $res_article_cate->fetch();

    if ($article_cate_data) {
        $article_cate_data['cate_url'] = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/officialwebsite.php?page_name=resource_page&cate=".$article_cate_data['id'];
        $article_cate_data['cate_img'] = empty($article_cate_data['cate_img']) ? "" : yz_tomedia($article_cate_data['cate_img']);
    }
    $connect5 = null;

    return $article_cate_data;
}

function getTotalCate($uniacid,$cate=0)
{
    $data = getPei();
    $host = $data['host'];
    $port = $data['port'];
    $database = $data['database'];
    $username = $data['username'];
    $pass = $data['pass'];
    $tablepre = $data['tablepre'];

    $article_total_sql = "select count(id) as article_total,sum(virtual_read_num) as article_read from ".$tablepre."yz_plugin_article where uniacid = ".$uniacid;

    if (!empty($cate)) {
        $article_total_sql .= " and category_id = :cate ";
    }

    $connect6 = new \PDO("mysql:host=".$host.";dbname=".$database.";port=".$port,$username,$pass);
	$connect6->prepare('set names utf8mb4')->execute();

    $article_total = $connect6->prepare($article_total_sql);

    $article_total->bindParam(':cate',$cate,PDO::PARAM_INT);

    $article_total->execute();

    $article_total_rel = $article_total->fetch();
    $connect6 = null;

    return $article_total_rel;
}

function getDetailArticle($article_id)
{
    $data = getPei();
    $host = $data['host'];
    $port = $data['port'];
    $database = $data['database'];
    $username = $data['username'];
    $pass = $data['pass'];
    $tablepre = $data['tablepre'];

    $sql = " select pa.*,ac.id as cate_id,ac.name as category_name,ac.cate_desc,ac.cate_img from ".$tablepre."yz_plugin_article as pa LEFT JOIN ".$tablepre."yz_plugin_article_category as ac on pa.category_id = ac.id where pa.id = :article_id and pa.state=1 and pa.type !=1 ";

    $connect1 = new \PDO("mysql:host=".$host.";dbname=".$database.";port=".$port,$username,$pass);
	$connect1->prepare('set names utf8mb4')->execute();

    $res = $connect1->prepare($sql);

    $res->bindParam(':article_id',$article_id,PDO::PARAM_INT);

    $res->execute();

    $rel = $res->fetch();
    $rel['off_url'] = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST']."/officialwebsite.php?page_name=resource_page";
    $rel['thumb'] = empty($rel['thumb']) ? "" : yz_tomedia($rel['thumb']);
    $rel['cate_img'] = empty($rel['cate_img']) ? "" : yz_tomedia($rel['cate_img']);
    $connect1 = null;

    return $rel;
}

function getShopData()
{
    $data = getPei();
    $host = $data['host'];
    $port = $data['port'];
    $database = $data['database'];
    $username = $data['username'];
    $pass = $data['pass'];
    $tablepre = $data['tablepre'];

    $sql = "select value from ".$tablepre."yz_setting where `group`='shop' and `key`='shop'";
    $connect2 = new \PDO("mysql:host=".$host.";dbname=".$database.";port=".$port,$username,$pass);
	$connect2->prepare('set names utf8mb4')->execute();

	$shop = $connect2->query($sql);
    $shop_data = $shop->fetch();

    return $shop_data;
}

function yz_tomedia($src)
{
    $HttpHost = $_SERVER['REQUEST_SCHEME'] . '://' .$_SERVER['HTTP_HOST'];

    $attachment = '/static/';

    //判断是否是本地不带域名图片地址
    if ((strpos($src, 'http://') === false) && (strpos($src, 'https://') === false)) {
        return $HttpHost. $attachment . 'upload/' . $src;
    }

    return $src;
}

function getUrlAddress()
{

}
