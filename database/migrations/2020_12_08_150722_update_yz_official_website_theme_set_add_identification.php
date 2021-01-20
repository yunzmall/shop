<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateYzOfficialWebsiteThemeSetAddIdentification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('yz_official_website_theme_set')) {
            Schema::table('yz_official_website_theme_set', function (Blueprint $table) {
                if (!Schema::hasColumn('yz_official_website_theme_set', 'identification')) {
                    $table->string("identification")->default("common")->comment("标识：用于前端显示时区别标题");
                }
            });
            $data = [
                "id"=>11,
                "title" => "官网2",
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
                    "theme_id" => 11,
                    "name" => "首页",
                    "identification" => "default_home",
                    "mul_title" => "测试",
                    "keyword" => "测试",
                    "describe" => "测试",
                    "detail" => '&lt;div class=&quot;container&quot;&gt;
        &lt;div class=&quot;wrap&quot; style=&quot;left: -1200px&quot;&gt;
          &lt;img src=&quot;official/website/images/bg3.png&quot; alt=&quot;&quot; /&gt;
          &lt;img src=&quot;official/website/images/bg1.jpg&quot; alt=&quot;&quot; /&gt;
          &lt;img src=&quot;official/website/images/bg2.png&quot; alt=&quot;&quot; /&gt;
          &lt;img src=&quot;official/website/images/bg3.png&quot; alt=&quot;&quot; /&gt;
          &lt;img src=&quot;official/website/images/bg1.jpg&quot; alt=&quot;&quot; /&gt;
        &lt;/div&gt;
        &lt;div class=&quot;buttons&quot;&gt;
          &lt;span class=&quot;on span-btn&quot;&gt;1&lt;/span&gt;
          &lt;span class=&quot;span-btn&quot;&gt;2&lt;/span&gt;
          &lt;span class=&quot;span-btn&quot;&gt;3&lt;/span&gt;
        &lt;/div&gt;
        &lt;a href=&quot;javascript:;&quot; class=&quot;arrow arrow_left&quot;&gt;&amp;lt;&lt;/a&gt;
        &lt;a href=&quot;javascript:;&quot; class=&quot;arrow arrow_right&quot;&gt;&amp;gt;&lt;/a&gt;
      &lt;/div&gt;

      &lt;div class=&quot;tradition&quot;&gt;
        &lt;div class=&quot;tradition-title&quot;&gt;做好生意 · 用好商城&lt;/div&gt;
        &lt;div class=&quot;tradition-title2&quot;&gt;强大完善的商城系统，助力商家取得成功&lt;/div&gt;
        &lt;div class=&quot;tradition-con&quot;&gt;
          &lt;div class=&quot;tradition-con-li&quot;&gt;
            &lt;div class=&quot;tradition-con-li-img&quot;&gt;适用行业&lt;/div&gt;
            &lt;div class=&quot;tradition-con-li-text&quot;&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/icon-muyin@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      母婴
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;
                      母婴亲子，日用百货
                    &lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/icon-meishi@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      美食
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;
                      母婴亲子，日用百货
                    &lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/icon-meizhuang@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      美妆
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;
                      母婴亲子，日用百货
                    &lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/icon-jiaoyu@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      教育
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;
                      母婴亲子，日用百货
                    &lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
            &lt;/div&gt;

            &lt;hr class=&quot;tradition-con-li-hr&quot; /&gt;
            &lt;div class=&quot;tradition-con-li-link&quot;&gt;
              &lt;a href=&quot;#&quot; style=&quot;color: #295ccd&quot;&gt;了解更多&gt;&lt;/a&gt;
            &lt;/div&gt;
          &lt;/div&gt;

          &lt;div class=&quot;tradition-con-li&quot;&gt;
            &lt;div class=&quot;tradition-con-li-img&quot;&gt;行业内应用&lt;/div&gt;
            &lt;div class=&quot;tradition-con-li-text&quot;&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/icon-o2omendian@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      O2O门店
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;
                      母婴亲子，日用百货
                    &lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/icon-zulin@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      租赁
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;
                      母婴亲子，日用百货
                    &lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/icon-gongyinglian.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      聚合供应链
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;
                      母婴亲子，日用百货
                    &lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/hetong-2@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      电子合同平台系统
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;
                      母婴亲子，日用百货
                    &lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
            &lt;/div&gt;
            &lt;hr class=&quot;tradition-con-li-hr&quot; /&gt;
            &lt;div class=&quot;tradition-con-li-link&quot;&gt;
              &lt;a href=&quot;#&quot; style=&quot;color: #295ccd&quot;&gt;了解更多&gt;&lt;/a&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;tradition-con-li&quot;&gt;
            &lt;div class=&quot;tradition-con-li-img&quot;&gt;营销类应用&lt;/div&gt;
            &lt;div class=&quot;tradition-con-li-text&quot;&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/icon-fenruntixi@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      5大分润体系
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;5大分润体系&lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/icon-zhinengihuace@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      智能画册
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;智能画册&lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/mingpianmdpi@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      商城名片
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;商城名片&lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/haibao-2@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      分享海报
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;分享海报&lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
            &lt;/div&gt;
            &lt;hr class=&quot;tradition-con-li-hr&quot; /&gt;
            &lt;div class=&quot;tradition-con-li-link&quot;&gt;
              &lt;a href=&quot;#&quot; style=&quot;color: #295ccd&quot;&gt;了解更多&gt;&lt;/a&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;tradition-con-li&quot;&gt;
            &lt;div class=&quot;tradition-con-li-img&quot;&gt;工具类应用&lt;/div&gt;
            &lt;div class=&quot;tradition-con-li-text&quot;&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/zhuangxiu@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      店铺装修
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;店铺装修&lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/renlian@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      人脸支付
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;人脸支付&lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/zhibo@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      小程序直播
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;小程序直播&lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
              &lt;a href=&quot;#&quot;&gt;
                &lt;div class=&quot;tradition-con-li-text2&quot;&gt;
                  &lt;div class=&quot;tradition-con-li-text2-1&quot;&gt;
                    &lt;img
                      src=&quot;official/website/images/jiayou@2x.png&quot;
                      alt=&quot;&quot;
                      style=&quot;width: 100%; height: 100%&quot;
                    /&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;tradition-con-li-text2-2&quot;&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2a&quot;&gt;
                      易加油
                      &lt;span class=&quot;tradition-con-li-text2-2a-1&quot;&gt;&amp;gt;&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;div class=&quot;tradition-con-li-text2-2b&quot;&gt;易加油&lt;/div&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/a&gt;
            &lt;/div&gt;
            &lt;hr class=&quot;tradition-con-li-hr&quot; /&gt;
            &lt;div class=&quot;tradition-con-li-link&quot;&gt;
              &lt;a href=&quot;#&quot; style=&quot;color: #295ccd&quot;&gt;了解更多&gt;&lt;/a&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      &lt;div class=&quot;tradition&quot; style=&quot;padding-top: 24px&quot;&gt;
        &lt;div class=&quot;tradition-title&quot;&gt;做生意 · 找我们&lt;/div&gt;
        &lt;div class=&quot;tradition-title2&quot;&gt;
          打造共赢生态，为商家提供全方位一站式服务
        &lt;/div&gt;
        &lt;div class=&quot;win-top&quot;&gt;
          &lt;div class=&quot;win-top-li&quot;&gt;
            &lt;a target=&quot;_blank&quot; href=&quot;#&quot;&gt;
              &lt;div class=&quot;win-top-li-img&quot;&gt;
                &lt;img src=&quot;official/website/images/icon1.png&quot; alt=&quot;&quot; /&gt;
              &lt;/div&gt;
              &lt;div class=&quot;win-top-li-title&quot;&gt;在线客服&lt;/div&gt;
              &lt;div class=&quot;win-top-li-title2&quot;&gt;专业答疑，贴心陪伴&lt;/div&gt;
            &lt;/a&gt;
          &lt;/div&gt;
          &lt;div class=&quot;win-top-li&quot;&gt;
            &lt;a target=&quot;_blank&quot; href=&quot;#&quot;&gt;
              &lt;div class=&quot;win-top-li-img&quot;&gt;
                &lt;img src=&quot;official/website/images/icon2.png&quot; alt=&quot;&quot; /&gt;
              &lt;/div&gt;
              &lt;div class=&quot;win-top-li-title&quot;&gt;视频教学&lt;/div&gt;
              &lt;div class=&quot;win-top-li-title2&quot;&gt;教你开店，教你运营&lt;/div&gt;
            &lt;/a&gt;
          &lt;/div&gt;
          &lt;div class=&quot;win-top-li&quot;&gt;
            &lt;a target=&quot;_blank&quot; href=&quot;#&quot;&gt;
              &lt;div class=&quot;win-top-li-img&quot;&gt;
                &lt;img src=&quot;official/website/images/icon3.png&quot; alt=&quot;&quot; /&gt;
              &lt;/div&gt;
              &lt;div class=&quot;win-top-li-title&quot;&gt;有伴服务&lt;/div&gt;
              &lt;div class=&quot;win-top-li-title2&quot;&gt;服务经理，全程指导&lt;/div&gt;
            &lt;/a&gt;
          &lt;/div&gt;
          &lt;div class=&quot;win-top-li&quot;&gt;
            &lt;a target=&quot;_blank&quot; href=&quot;#&quot;&gt;
              &lt;div class=&quot;win-top-li-img&quot;&gt;
                &lt;img src=&quot;official/website/images/icon4.png&quot; alt=&quot;&quot; /&gt;
              &lt;/div&gt;
              &lt;div class=&quot;win-top-li-title&quot;&gt;服务市场&lt;/div&gt;
              &lt;div class=&quot;win-top-li-title2&quot;&gt;装修 / 拍摄 / 代运营&lt;/div&gt;
            &lt;/a&gt;
          &lt;/div&gt;
        &lt;/div&gt;
        &lt;div class=&quot;win-top&quot;&gt;
          &lt;div class=&quot;win-top-li&quot;&gt;
            &lt;a target=&quot;_blank&quot; href=&quot;#&quot;&gt;
              &lt;div class=&quot;win-top-li-img&quot;&gt;
                &lt;img src=&quot;official/website/images/icon5.png&quot; alt=&quot;&quot; /&gt;
              &lt;/div&gt;
              &lt;div class=&quot;win-top-li-title&quot;&gt;应用市场&lt;/div&gt;
              &lt;div class=&quot;win-top-li-title2&quot;&gt;各种玩法，应有尽有&lt;/div&gt;
            &lt;/a&gt;
          &lt;/div&gt;
          &lt;div class=&quot;win-top-li&quot;&gt;
            &lt;a target=&quot;_blank&quot; href=&quot;#&quot;&gt;
              &lt;div class=&quot;win-top-li-img&quot;&gt;
                &lt;img src=&quot;official/website/images/icon6.png&quot; alt=&quot;&quot; /&gt;
              &lt;/div&gt;
              &lt;div class=&quot;win-top-li-title&quot;&gt;商家社区&lt;/div&gt;
              &lt;div class=&quot;win-top-li-title2&quot;&gt;产品动态，商家交流&lt;/div&gt;
            &lt;/a&gt;
          &lt;/div&gt;
          &lt;div class=&quot;win-top-li&quot;&gt;
            &lt;a target=&quot;_blank&quot; href=&quot;#&quot;&gt;
              &lt;div class=&quot;win-top-li-img&quot;&gt;
                &lt;img src=&quot;official/website/images/icon7.png&quot; alt=&quot;&quot; /&gt;
              &lt;/div&gt;
              &lt;div class=&quot;win-top-li-title&quot;&gt;本地商盟&lt;/div&gt;
              &lt;div class=&quot;win-top-li-title2&quot;&gt;资源对接，抱团发展&lt;/div&gt;
            &lt;/a&gt;
          &lt;/div&gt;
          &lt;div class=&quot;win-top-li&quot;&gt;
            &lt;a target=&quot;_blank&quot; href=&quot;#&quot;&gt;
              &lt;div class=&quot;win-top-li-img&quot;&gt;
                &lt;img src=&quot;official/website/images/icon8.png&quot; alt=&quot;&quot; /&gt;
              &lt;/div&gt;
              &lt;div class=&quot;win-top-li-title&quot;&gt;定制服务&lt;/div&gt;
              &lt;div class=&quot;win-top-li-title2&quot;&gt;个性化需求，帮你实现&lt;/div&gt;
            &lt;/a&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      &lt;div class=&quot;tradition&quot; style=&quot;padding-top: 24px&quot;&gt;
        &lt;div class=&quot;tradition-title&quot;&gt;强大的技术团队 · 为您的商城护航&lt;/div&gt;
        &lt;div class=&quot;tradition-title2&quot;&gt;
          系统稳定高于一切，让商家经营更有保障，更放心！
        &lt;/div&gt;
        &lt;div class=&quot;team-one&quot;&gt;
          &lt;div class=&quot;team-one-li team-one-li-bg1&quot;&gt;更强大&lt;/div&gt;
          &lt;div class=&quot;team-one-li team-one-li-bg2&quot;&gt;更稳定&lt;/div&gt;
          &lt;div class=&quot;team-one-li team-one-li-bg3&quot;&gt;更科学&lt;/div&gt;
        &lt;/div&gt;
        &lt;!-- 滚动数字 --&gt;
        &lt;div class=&quot;team-two&quot;&gt;
          &lt;div class=&quot;team-two-li&quot;&gt;
            &lt;div class=&quot;team-two-li-num&quot;&gt;&lt;span id=&quot;num-1&quot;&gt;336&lt;/span&gt;万&lt;/div&gt;
            &lt;div class=&quot;team-two-li-text&quot;&gt;商家信赖首选&lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;team-two-li&quot;&gt;
            &lt;div class=&quot;team-two-li-num&quot;&gt;&lt;span id=&quot;num-2&quot;&gt;112&lt;/span&gt;个&lt;/div&gt;
            &lt;div class=&quot;team-two-li-text&quot;&gt;覆盖行业和类目&lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;team-two-li&quot;&gt;
            &lt;div class=&quot;team-two-li-num&quot;&gt;&lt;span id=&quot;num-3&quot;&gt;2588&lt;/span&gt;万&lt;/div&gt;
            &lt;div class=&quot;team-two-li-text&quot;&gt;在售商品&lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;team-two-li&quot;&gt;
            &lt;div class=&quot;team-two-li-num&quot;&gt;&lt;span id=&quot;num-4&quot;&gt;218&lt;/span&gt;亿&lt;/div&gt;
            &lt;div class=&quot;team-two-li-text&quot;&gt;成交金额&lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      &lt;div class=&quot;tradition&quot;&gt;
        &lt;div class=&quot;case&quot;&gt;
          &lt;div class=&quot;case-li&quot;&gt;
            &lt;div class=&quot;case-li-img&quot;&gt;
              &lt;img
                src=&quot;official/website/images/contact_part1.png&quot;
                alt=&quot;&quot;
                style=&quot;width: 100%; height: 190px&quot;
              /&gt;
              &lt;div class=&quot;case-li-img-a&quot;&gt;
                &lt;img
                  src=&quot;official/website/images/contact_part1.png&quot;
                  alt=&quot;&quot;
                  style=&quot;width: 100%; height: 100%&quot;
                /&gt;
              &lt;/div&gt;
            &lt;/div&gt;
            &lt;div class=&quot;case-li-two&quot;&gt;
              &lt;div class=&quot;case-li-title&quot;&gt;XXX玩具馆&lt;/div&gt;
              &lt;div class=&quot;case-li-content&quot;&gt;
                幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;case-li&quot;&gt;
            &lt;div class=&quot;case-li-img&quot;&gt;
              &lt;img
                src=&quot;official/website/images/contact_part1.png&quot;
                alt=&quot;&quot;
                style=&quot;width: 100%; height: 190px&quot;
              /&gt;
              &lt;div class=&quot;case-li-img-a&quot;&gt;
                &lt;img
                  src=&quot;official/website/images/contact_part1.png&quot;
                  alt=&quot;&quot;
                  style=&quot;width: 100%; height: 100%&quot;
                /&gt;
              &lt;/div&gt;
            &lt;/div&gt;
            &lt;div class=&quot;case-li-two&quot;&gt;
              &lt;div class=&quot;case-li-title&quot;&gt;XXX玩具馆&lt;/div&gt;
              &lt;div class=&quot;case-li-content&quot;&gt;
                幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;case-li&quot;&gt;
            &lt;div class=&quot;case-li-img&quot;&gt;
              &lt;img
                src=&quot;official/website/images/contact_part1.png&quot;
                alt=&quot;&quot;
                style=&quot;width: 100%; height: 190px&quot;
              /&gt;
              &lt;div class=&quot;case-li-img-a&quot;&gt;
                &lt;img
                  src=&quot;official/website/images/contact_part1.png&quot;
                  alt=&quot;&quot;
                  style=&quot;width: 100%; height: 100%&quot;
                /&gt;
              &lt;/div&gt;
            &lt;/div&gt;
            &lt;div class=&quot;case-li-two&quot;&gt;
              &lt;div class=&quot;case-li-title&quot;&gt;XXX玩具馆&lt;/div&gt;
              &lt;div class=&quot;case-li-content&quot;&gt;
                幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;case-li&quot;&gt;
            &lt;div class=&quot;case-li-img&quot;&gt;
              &lt;img
                src=&quot;official/website/images/contact_part1.png&quot;
                alt=&quot;&quot;
                style=&quot;width: 100%; height: 190px&quot;
              /&gt;
              &lt;div class=&quot;case-li-img-a&quot;&gt;
                &lt;img
                  src=&quot;official/website/images/contact_part1.png&quot;
                  alt=&quot;&quot;
                  style=&quot;width: 100%; height: 100%&quot;
                /&gt;
              &lt;/div&gt;
            &lt;/div&gt;
            &lt;div class=&quot;case-li-two&quot;&gt;
              &lt;div class=&quot;case-li-title&quot;&gt;XXX玩具馆&lt;/div&gt;
              &lt;div class=&quot;case-li-content&quot;&gt;
                幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;case-li&quot;&gt;
            &lt;div class=&quot;case-li-img&quot;&gt;
              &lt;img
                src=&quot;official/website/images/contact_part1.png&quot;
                alt=&quot;&quot;
                style=&quot;width: 100%; height: 190px&quot;
              /&gt;
              &lt;div class=&quot;case-li-img-a&quot;&gt;
                &lt;img
                  src=&quot;official/website/images/contact_part1.png&quot;
                  alt=&quot;&quot;
                  style=&quot;width: 100%; height: 100%&quot;
                /&gt;
              &lt;/div&gt;
            &lt;/div&gt;
            &lt;div class=&quot;case-li-two&quot;&gt;
              &lt;div class=&quot;case-li-title&quot;&gt;XXX玩具馆&lt;/div&gt;
              &lt;div class=&quot;case-li-content&quot;&gt;
                幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;case-li&quot;&gt;
            &lt;div class=&quot;case-li-img&quot;&gt;
              &lt;img
                src=&quot;official/website/images/contact_part1.png&quot;
                alt=&quot;&quot;
                style=&quot;width: 100%; height: 190px&quot;
              /&gt;
              &lt;div class=&quot;case-li-img-a&quot;&gt;
                &lt;img
                  src=&quot;official/website/images/contact_part1.png&quot;
                  alt=&quot;&quot;
                  style=&quot;width: 100%; height: 100%&quot;
                /&gt;
              &lt;/div&gt;
            &lt;/div&gt;
            &lt;div class=&quot;case-li-two&quot;&gt;
              &lt;div class=&quot;case-li-title&quot;&gt;XXX玩具馆&lt;/div&gt;
              &lt;div class=&quot;case-li-content&quot;&gt;
                幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！幼儿绘本租赁，幼儿绘本租赁，幼儿绘本租赁幼儿绘本租赁幼儿绘本租赁！
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;

          &lt;div class=&quot;case-more&quot; style=&quot;width: 100%&quot;&gt;
            &lt;a href=&quot;#&quot;&gt;
              &lt;div class=&quot;case-more-a&quot;&gt;查看更多案例&lt;/div&gt;
            &lt;/a&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;
      &lt;div class=&quot;tradition&quot; style=&quot;padding-top: 36px&quot;&gt;
        &lt;div class=&quot;tradition-title&quot;&gt;合作伙伴&lt;/div&gt;
        &lt;div class=&quot;company&quot;&gt;
          &lt;div class=&quot;company-top&quot;&gt;
            &lt;div class=&quot;company-img&quot;&gt;
              &lt;img src=&quot;official/website/images/brand1.png&quot; alt=&quot;&quot; /&gt;
            &lt;/div&gt;
            &lt;div class=&quot;company-img&quot;&gt;
              &lt;img src=&quot;official/website/images/brand2.png&quot; alt=&quot;&quot; /&gt;
            &lt;/div&gt;
            &lt;div class=&quot;company-img&quot;&gt;
              &lt;img src=&quot;official/website/images/brand3.png&quot; alt=&quot;&quot; /&gt;
            &lt;/div&gt;
            &lt;div class=&quot;company-img&quot;&gt;
              &lt;img src=&quot;official/website/images/brand4.png&quot; alt=&quot;&quot; /&gt;
            &lt;/div&gt;
            &lt;div class=&quot;company-img&quot;&gt;
              &lt;img src=&quot;official/website/images/brand5.png&quot; alt=&quot;&quot; /&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;company-top&quot;&gt;
            &lt;div class=&quot;company-img&quot;&gt;
              &lt;img src=&quot;official/website/images/brand6.png&quot; alt=&quot;&quot; /&gt;
            &lt;/div&gt;
            &lt;div class=&quot;company-img&quot;&gt;
              &lt;img src=&quot;official/website/images/brand7.png&quot; alt=&quot;&quot; /&gt;
            &lt;/div&gt;
            &lt;div class=&quot;company-img&quot;&gt;
              &lt;img src=&quot;official/website/images/brand8.png&quot; alt=&quot;&quot; /&gt;
            &lt;/div&gt;
            &lt;div class=&quot;company-img&quot;&gt;
              &lt;img src=&quot;official/website/images/brand9.png&quot; alt=&quot;&quot; /&gt;
            &lt;/div&gt;
            &lt;div class=&quot;company-img&quot;&gt;
              &lt;img src=&quot;official/website/images/brand10.png&quot; alt=&quot;&quot; /&gt;
            &lt;/div&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;

      &lt;div class=&quot;touch&quot;&gt;
        &lt;div class=&quot;touch-left&quot;&gt;
          &lt;div class=&quot;touch-left-one&quot;&gt;立即联系开店顾问&lt;/div&gt;
          &lt;div class=&quot;touch-left-two&quot;&gt;扫码咨询，早一步开店，早一步成功&lt;/div&gt;
          &lt;div class=&quot;touch-left-three&quot;&gt;
            &lt;img src=&quot;official/website/images/contact_part1.png&quot; alt=&quot;&quot; /&gt;
          &lt;/div&gt;
          &lt;div class=&quot;touch-left-one&quot;&gt;电话：13333333XXX&lt;/div&gt;
          &lt;div class=&quot;touch-left-two&quot;&gt;工作时间：9：00-22：00&lt;/div&gt;
        &lt;/div&gt;
        &lt;div class=&quot;touch-right&quot;&gt;
          &lt;div class=&quot;touch-left-one&quot;&gt;留下您的联系方式&lt;/div&gt;
          &lt;div class=&quot;touch-left-two&quot;&gt;开店顾问会致电与您沟通&lt;/div&gt;
          &lt;div class=&quot;touch-right-input&quot;&gt;
            &lt;input type=&quot;text&quot; placeholder=&quot;姓名&quot; id=&quot;name&quot; /&gt;
          &lt;/div&gt;
          &lt;div class=&quot;touch-right-input&quot;&gt;
            &lt;input type=&quot;text&quot; placeholder=&quot;电话&quot; id=&quot;tel&quot; /&gt;
          &lt;/div&gt;
          &lt;div class=&quot;touch-right-btn&quot;&gt;
            &lt;a onclick=&quot;submit()&quot;&gt;&lt;div&gt;预约开店顾问&lt;/div&gt;&lt;/a&gt;
          &lt;/div&gt;
        &lt;/div&gt;
      &lt;/div&gt;

      &lt;div class=&quot;receive&quot;&gt;
        &lt;div class=&quot;receive-one&quot;&gt;商城社交电商系统 · 免费版&lt;/div&gt;
        &lt;div class=&quot;receive-two&quot;&gt;
          &lt;a href=&quot;#&quot;&gt;&lt;div&gt;立即领取&lt;/div&gt;&lt;/a&gt;
        &lt;/div&gt;
      &lt;/div&gt;',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ],
                [
                    "theme_id" => 11,
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
                    "theme_id" => 11,
                    "name" => "文章列表",
                    "identification" => "resource_page",
                    "mul_title" => "测试",
                    "keyword" => "测试",
                    "describe" => "测试",
                    "detail" => '',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ],
                [
                    "theme_id" => 11,
                    "name" => "文章详情",
                    "identification" => "resource_detail",
                    "mul_title" => "测试",
                    "keyword" => "测试",
                    "describe" => "测试",
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
