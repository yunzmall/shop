<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddThemeToYzOfficialWebsiteThemeSetAndYzOfficialWebsiteMultiple extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_official_website_theme_set')) {
            $data = [
                "id"=>21,
                "title" => "APP介绍官网",
                "is_default" => 0,
                "basic" => '{"cus_url":"","cus_link":"","cus_mobile":"","cus_uniacid":1}',
                "top" => '{"logo":"","is_sign":1,"navigation":[{"name":"\u9996\u9875","url":""},{"name":"\u89e3\u51b3\u65b9\u6848","url":""},{"name":"\u4ea7\u54c1\u4e2d\u5fc3","url":""},{"name":"\u8d44\u6e90\u4e2d\u5fc3","url":""},{"name":"\u5173\u4e8e\u6211\u4eec","url":""}]}',
                "tail" => '&lt;div class=&quot;bottom&quot;&gt;
&lt;div class=&quot;bottom-a&quot;&gt;
&lt;div class=&quot;bottom-left&quot;&gt;
&lt;div class=&quot;bottom-left-one&quot;&gt;
&lt;div class=&quot;bottom-left-title&quot;&gt;
&lt;div class=&quot;bottom-left-title1&quot;&gt;产品服务&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;&lt;a href=&quot;&quot;&gt;产品介绍&lt;/a&gt;&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;&lt;a href=&quot;&quot;&gt;解决方案&lt;/a&gt;&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;&lt;a href=&quot;&quot;&gt;立即登录&lt;/a&gt;&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;bottom-left-title&quot;&gt;
&lt;div class=&quot;bottom-left-title1&quot;&gt;资源中心&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;&lt;a href=&quot;&quot;&gt;电子合同百科&lt;/a&gt;&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;&lt;a href=&quot;&quot;&gt;用户指南&lt;/a&gt;&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;&lt;a href=&quot;&quot;&gt;操作视频&lt;/a&gt;&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;&lt;a href=&quot;&quot;&gt;帮助中心&lt;/a&gt;&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;bottom-left-title&quot;&gt;
&lt;div class=&quot;bottom-left-title1&quot;&gt;关于我们&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;&lt;a href=&quot;&quot;&gt;公司介绍&lt;/a&gt;&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;&lt;a href=&quot;&quot;&gt;联系我们&lt;/a&gt;&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;bottom-left-title&quot; style=&quot;flex: 2;&quot;&gt;
&lt;div class=&quot;bottom-left-title1&quot;&gt;联系我们&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;电话：020-2222222-2222222&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;邮箱：9572315554455@163.com&lt;/div&gt;
&lt;div class=&quot;bottom-left-title2&quot;&gt;地址：广州市白云区&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;bottom-right&quot;&gt;
&lt;div class=&quot;bottom-right-img&quot;&gt;&lt;img src=&quot;official/images/footer_qr_contact.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;bottom-right-tip&quot;&gt;扫一扫添加联系人企业微信&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;bottom-right-img&quot;&gt;&lt;img src=&quot;official/images/footer_qr_contact.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;bottom-right-tip&quot;&gt;扫一扫了解更多&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;bottom-b&quot;&gt;
&lt;div&gt;备案号：11111111111111111111111111111111&lt;/div&gt;
&lt;div&gt;备案号：11111111111111111111111111111111&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;',
                "identification"=>"website",
                "created_at" => time(),
                "updated_at" => time()
            ];
            \Illuminate\Support\Facades\DB::table('yz_official_website_theme_set')->insert($data);

        }

        if (Schema::hasTable('yz_official_website_multiple')) {
            $multipe_data = [
                [
                    "theme_id" => 21,
                    "name" => "首页",
                    "identification" => "default_home",
                    "mul_title" => "app介绍",
                    "keyword" => "app介绍",
                    "describe" => "app介绍",
                    "detail" => '   &lt;div class=&quot;swiper-slide home_page&quot;&gt;&lt;img src=&quot;official/website/images/app_introduce/banner.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
          &lt;div class=&quot;swiper-slide home_detail&quot;&gt;
            &lt;div class=&quot;top_detail&quot;&gt;
              &lt;div class=&quot;image_contain&quot;&gt;
                &lt;img src=&quot;official/website/images/app_introduce/prototype.png&quot; alt=&quot;&quot; class=&quot;img_frame&quot; /&gt;
                &lt;img src=&quot;official/website/images/app_introduce/first.png&quot; alt=&quot;&quot; class=&quot;img_detail&quot; /&gt;
              &lt;/div&gt;
              &lt;div class=&quot;contain&quot;&gt;
                &lt;span class=&quot;contain_title&quot;&gt;聚合CPS&lt;/span&gt;
                &lt;span class=&quot;contain_describe&quot;
                  &gt;用户通过聚合CPS插件APP跳转第三方(淘宝、京东、拼多多、美团、大众点评、携程、口碑、苏宁等平台)消费后，第三方会给到平台返佣，我们可以基于该返佣奖励给会员一部分、推荐者一部分、剩余的即为平台利润!&lt;/span
                &gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;swiper-slide home_detail&quot;&gt;
            &lt;div class=&quot;top_detail&quot;&gt;
              &lt;div class=&quot;contain&quot;&gt;
                &lt;span class=&quot;contain_title&quot;&gt;带货直播&lt;/span&gt;
                &lt;span class=&quot;contain_describe&quot;
                  &gt;带货直播基于腾讯云直播深度结合社交电商系统开发，链接微信私域流量，达到粉丝共享，助力转化现有私域流量,实现流量间的裂变！集社交+直播+电商为一体的新零售转化系统。&lt;/span
                &gt;
              &lt;/div&gt;
              &lt;div class=&quot;image_contain&quot;&gt;
                &lt;img src=&quot;official/website/images/app_introduce/prototype.png&quot; alt=&quot;&quot; class=&quot;img_frame_odd&quot; /&gt;
                &lt;img src=&quot;official/website/images/app_introduce/first.png&quot; alt=&quot;&quot; class=&quot;img_detail_odd&quot; /&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;swiper-slide home_detail&quot;&gt;
            &lt;div class=&quot;top_detail&quot;&gt;
              &lt;div class=&quot;image_contain&quot;&gt;
                &lt;img src=&quot;official/website/images/app_introduce/prototype.png&quot; alt=&quot;&quot; class=&quot;img_frame&quot; /&gt;
                &lt;img src=&quot;official/website/images/app_introduce/first.png&quot; alt=&quot;&quot; class=&quot;img_detail&quot; /&gt;
              &lt;/div&gt;
              &lt;div class=&quot;contain&quot;&gt;
                &lt;span class=&quot;contain_title&quot;&gt;020-门店收银台&lt;/span&gt;
                &lt;span class=&quot;contain_describe&quot;&gt;门店入驻平台，可以设置收银台扫码支付、销售门店商品，平台收取门店交易收入的百分比提成，支持收银台、门店分开设置结算提成比例.&lt;/span&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;swiper-slide home_detail&quot;&gt;
            &lt;div class=&quot;top_detail last_detail&quot;&gt;
              &lt;div class=&quot;contain&quot;&gt;
                &lt;span class=&quot;contain_title&quot;&gt;AI智能测肤&lt;/span&gt;
                &lt;span class=&quot;contain_describe&quot;
                  &gt;一款人工智能测肤工具，通过AI智能检测，对用户的皮肤状况进行分析诊断，肤质检测报告快速准确，当即知道肤质状况，同时针对不同的肌肤问题为用户提供合理的解决方案，帮助用户解决肌肤管理问题。&lt;/span
                &gt;
              &lt;/div&gt;
              &lt;div class=&quot;image_contain&quot;&gt;
                &lt;img src=&quot;official/website/images/app_introduce/prototype.png&quot; alt=&quot;&quot; class=&quot;img_frame_odd&quot; /&gt;
                &lt;img src=&quot;official/website/images/app_introduce/first.png&quot; alt=&quot;&quot; class=&quot;img_detail_odd&quot; /&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ],
                [
                    "theme_id" => 21,
                    "name" => "公司",
                    "identification" => "company",
                    "mul_title" => "测试",
                    "keyword" => "测试",
                    "describe" => "测试",
                    "detail" => '&lt;div class=&quot;b_banner&quot;&gt;
        &lt;div class=&quot;b_banner-a&quot;&gt;
          &lt;div&gt;
            你离成功就差
          &lt;/div&gt;
          &lt;div&gt;
            一个好的移动电商系统
          &lt;/div&gt;
          
        &lt;/div&gt;
      &lt;/div&gt;
      &lt;div class=&quot;b_brief&quot;&gt;
        &lt;div class=&quot;b_brief-left&quot;&gt;
          &lt;div class=&quot;b_brief-left-title1&quot;&gt;公司简介&lt;/div&gt;
          &lt;div class=&quot;b_brief-left-title2&quot;&gt;&lt;/div&gt;
          &lt;div class=&quot;b_brief-left-title3&quot;&gt;公司简介公司简介公司简介公司简介公司简介公司简介公司简介公司简介公司简介公司简介公司简介公司简介公司简介公司简介公司简介公司简介&lt;/div&gt;
        &lt;/div&gt;
        &lt;div class=&quot;b_brief-right&quot;&gt;
          &lt;div class=&quot;b_brief-right-img&quot;&gt;
            &lt;img src=&quot;official/website/images/brief-1.png&quot; alt=&quot;&quot;&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      &lt;div class=&quot;b_culture&quot;&gt;
        &lt;div class=&quot;b_culture-a&quot;&gt;
          &lt;div class=&quot;b_culture-title1&quot;&gt;公司文化&lt;/div&gt;
          &lt;div class=&quot;b_culture-title2&quot;&gt;&lt;/div&gt;
          &lt;div class=&quot;b_culture-title3&quot;&gt;
            公司的使命
          &lt;/div&gt;
          &lt;div class=&quot;b_culture-title4&quot;&gt;
            为客户利益而努力创新，持续为客户创造价值！
          &lt;/div&gt;
          &lt;div class=&quot;b_culture-title3&quot;&gt;
            公司的愿景
          &lt;/div&gt;
          &lt;div class=&quot;b_culture-title4&quot;&gt;
            成为全球优秀领先的移动电商解决方案与服务提供商！
          &lt;/div&gt;
          &lt;div class=&quot;b_culture-title3&quot;&gt;
            公司的价值观
          &lt;/div&gt;
          &lt;div class=&quot;b_culture-title4&quot;&gt;
            快乐工作，快乐生活！
          &lt;/div&gt;
          
        &lt;/div&gt;
      &lt;/div&gt;
      &lt;div class=&quot;b_example&quot;&gt;
        &lt;div class=&quot;b_example-title&quot;&gt;
          &lt;div class=&quot;b_example-title1&quot;&gt;公司实例&lt;/div&gt;
          &lt;div class=&quot;b_example-title2&quot;&gt;&lt;/div&gt;
          &lt;div class=&quot;b_example-title3&quot;&gt;4年专注移动电商系统研发&lt;/div&gt;
          &lt;div class=&quot;b_example-title3&quot;&gt;拥有数十项著作权，专利，高新技术产品&lt;/div&gt;
        &lt;/div&gt;
        &lt;div class=&quot;b_example-img&quot;&gt;
          &lt;img src=&quot;official/website/images/brief-3.png&quot; alt=&quot;&quot;&gt;
        &lt;/div&gt;
        &lt;div class=&quot;case-more&quot; style=&quot;width:100%&quot;&gt;
          &lt;a href=&quot;#&quot;&gt;
            &lt;div class=&quot;case-more-a&quot;&gt;了解更多&gt;&gt;&gt;&lt;/div&gt;
          &lt;/a&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      &lt;div class=&quot;b_touch-all&quot;&gt;
        &lt;div class=&quot;b_touch&quot;&gt;
          &lt;div class=&quot;b_touch-title1&quot;&gt;联系我们&lt;/div&gt;
          &lt;div class=&quot;b_touch-title2&quot;&gt;&lt;/div&gt;
          &lt;div class=&quot;b_touch-con&quot;&gt;
            &lt;div class=&quot;b_touch-left&quot;&gt;
              &lt;div class=&quot;b_touch-left-one&quot;&gt;立即联系开店顾问&lt;/div&gt;
              &lt;div class=&quot;b_touch-left-two&quot;&gt;扫码咨询，早一步开店，早一步成功&lt;/div&gt;
              &lt;div class=&quot;b_touch-left-three&quot;&gt;
                &lt;img src=&quot;official/website/images/contact_part1.png&quot; alt=&quot;&quot;&gt;
              &lt;/div&gt;
              &lt;div class=&quot;b_touch-left-one&quot;&gt;电话：13333333XXX&lt;/div&gt;
              &lt;div class=&quot;b_touch-left-two&quot;&gt;地址：广州市白云区机场路XXX号XXX栋X楼&lt;/div&gt;
            &lt;/div&gt;
            &lt;div class=&quot;b_touch-right&quot;&gt;
              &lt;div class=&quot;b_touch-right-img&quot;&gt;
                &lt;img src=&quot;official/website/images/brief-4.png&quot; alt=&quot;&quot; style=&quot;width:310px;height:290px;&quot;&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      &lt;div class=&quot;b_condition&quot;&gt;
        &lt;div class=&quot;b_condition-box&quot;&gt;
          &lt;div class=&quot;b_condition-title1&quot;&gt;公司动态&lt;/div&gt;',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ],
                [
                    "theme_id" => 21,
                    "name" => "文章列表",
                    "identification" => "resource_page",
                    "mul_title" => "app介绍",
                    "keyword" => "app介绍",
                    "describe" => "app介绍",
                    "detail" => '',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ],
                [
                    "theme_id" => 21,
                    "name" => "文章详情",
                    "identification" => "resource_detail",
                    "mul_title" => "app介绍",
                    "keyword" => "app介绍",
                    "describe" => "app介绍",
                    "detail" => '',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ],
            ];
            \Illuminate\Support\Facades\DB::table('yz_official_website_multiple')->insert($multipe_data);
        }


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
