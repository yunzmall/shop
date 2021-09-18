<?php

if (env('APP_Framework',false) == 'platform') {
    $attachment = 'static/upload';
} else {
    $attachment = '../../attachment';
}


return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => 's3',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        //图片
        'syst_images' => [
            //仅新框架用
            'driver' => 'local',
            'root' => base_path('static/upload/images/0/'.date('Y').'/'.date('m')),
            'url' => 'images/0/'.date('Y').'/'.date('m'),
            'visibility' => 'public',
        ],

        //图片
        'newimages' => [
            'driver' => 'local',
            'root' => base_path($attachment . '/newimage'),
            'url' => 'newimage',
            'visibility' => 'public',
        ],

        //视频
        'videos' => [
            'driver' => 'local',
            'root' => base_path($attachment . '/videos/0/'.date('Y').'/'.date('m')),
            'url' => 'videos/0/'.date('Y').'/'.date('m'),
            'visibility' => 'public',
        ],

        //音频
        'audios' => [
            'driver' => 'local',
            'root' => base_path($attachment . '/audios/0/'.date('Y').'/'.date('m')),
            'url' => 'audios/0/'.date('Y').'/'.date('m'),
            'visibility' => 'public',
        ],

        //聚合cps的apk文件
        'cps_apk' => [
            'driver' => 'local',
            'root' => base_path($attachment . '/cps_apk/0/'.date('Y').'/'.date('m')),
            'url' => 'cps_apk/0/'.date('Y').'/'.date('m'),
            'visibility' => 'public',
        ],
        
        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        'avatar' => [
            'driver' => 'local',
            'root' => base_path($attachment .'/avatar'),
            'url' => env('APP_URL').'/attachment/avatar',
            'visibility' => 'public',
        ],

        'image' => [
            'driver' => 'local',
            'root' => base_path($attachment . '/image'),
            'url' => 'image',
            'visibility' => 'public',
        ],

        // 商品相册 批量上传商品图片 文件保存路径
        'photoimage' => [
            'driver' => 'local',
            'root' => base_path($attachment . '/photoimage'),
            'url' => env('APP_URL').'photoimage',
            'visibility' => 'public',
        ],

        // 保单pdf文件上传
        'insurance' => [
            'driver' => 'local',
            'root' => storage_path('app/insurance'),
            'url' => env('APP_URL').'/storage/app/insurance',
        ],

        'cert' => [
            'driver' => 'local',
            'root' => storage_path('cert'),
        ],

        'pem' => [
            'driver' => 'local',
            'root' => storage_path('pem'),
        ],
        // 批量发货上传excel文件保存路径
        'recharge' => [
            'driver' => 'local',
            'root' => storage_path('app/public/recharge'),
        ],
        'ownerOrderImport' => [
            'driver' => 'local',
            'root' => storage_path('plugins/owner-order-import'),
        ],

        // 批量发货上传excel文件保存路径
        'orderexcel' => [
            'driver' => 'local',
            'root' => storage_path('app/public/orderexcel'),
        ],

        // 海粉上传excel文件保存
        'levelexcel' => [
            'driver' => 'local',
            'root' => storage_path('app/public/levelexcel'),
        ],

        // 批量卡密上传excel文件保存路径
        'virtualcard' => [
            'driver' => 'local',
            'root' => storage_path('app/public/virtualcard'),
        ],

        // 网约车 批量上传excel文件保存路径
        'netcar' => [
            'driver' => 'local',
            'root' => storage_path('app/public/netcar'),
        ],

        
        // 易宝支付图片上传
        'yop' => [
            'driver' => 'local',
            'root' => storage_path('app/public/yop'),
            'url' => env('APP_URL').'/storage/public/yop',
        ],

        // 易宝支付图片上传
        'business_card' => [
            'driver' => 'local',
            'root' => storage_path('app/public/business_card'),
            'url' => env('APP_URL').'/storage/public/business_card',
        ],

        //直播海报图片上传
        'room_poster' => [
            'driver' => 'local',
            'root' => storage_path('app/public/room_post'),
            'url' => env('APP_URL').'/storage/public/room_post',
        ],

        //龙存管插件文件上传路径
        'dragon_deposit' => [
            'driver' => 'local',
            'root' => storage_path('app/dragon-deposit'),
            'url' => env('APP_URL').'/storage/app/dragon-deposit',
        ],

        'upload' => [
            'driver' => 'local',
            'root' => storage_path('app/public/avatar'),
            'url' => env('APP_URL').'/storage/public/avatar',
            'visibility' => 'public',
        ],

        'banner' => [
            'driver' => 'local',
            'root' => storage_path('app/public/banner'),
            'url' => env('APP_URL').'/storage/public/banner',
            'visibility' => 'public',
        ],

        //淘宝CSV实例
        'taobaoCSV' => [
            'driver' => 'local',
            'root'=> base_path('plugins/goods-assistant/storage/examples'),
            'url' => env('APP_URL').'plugins/goods-assistant/storage/examples',
            'visibility' => 'public',
        ],

        //淘宝CSV上传
        'taobaoCSVupload' => [
            'driver' => 'local',
            'root'=> base_path('plugins/goods-assistant/storage/upload'),
            'url' => env('APP_URL').'plugins/goods-assistant/storage/upload',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
        ],
        /*'yun_sign'=>[
            'driver' => 'local',
            'root'=> base_path('plugins/yun-sign/static'),
            'url' => env('APP_URL').'plugins/yun-sign/static',
            'visibility' => 'public',
        ],*/
        'yun_sign'=>[
            'driver' => 'local',
            'root' => base_path('static/yun-sign'),
            'url' => '/static/yun-sign/',
            'visibility' => 'public',
        ],
        'shop_esign'=>[
            'driver' => 'local',
            'root' => base_path('static/shop-esign'),
            'url' => '/static/shop-esign/',
            'visibility' => 'public',
        ],
    ],
];
