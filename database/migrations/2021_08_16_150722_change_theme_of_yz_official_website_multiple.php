<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeThemeOfYzOfficialWebsiteMultiple extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('yz_official_website_multiple')) {
            $update_data = [
                'detail'=>' &lt;div class=&quot;swiper-slide home_page&quot;&gt;&lt;img src=&quot;official/website/images/app_introduce/banner.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
          &lt;div class=&quot;swiper-slide home_detail&quot;&gt;
            &lt;div class=&quot;top_detail&quot;&gt;
              &lt;div class=&quot;contain&quot;&gt;
                &lt;div class=&quot;top_introduce&quot;&gt;
                  &lt;span class=&quot;title&quot;&gt;中国主流的社交商城系统&lt;/span&gt;
                  &lt;span class=&quot;describe&quot;&gt;满足市场20多种社交商城模式&lt;/span&gt;
                  &lt;span class=&quot;objective&quot;&gt;目前:帮您提供在线上做生意做业务的工具，让您在线上实现成交，让电商成为企业增长核心驱动力。&lt;/span&gt;
                &lt;/div&gt;
                &lt;div class=&quot;buttom_introduce&quot;&gt;
                  &lt;div class=&quot;left_func&quot;&gt;
                    &lt;div &gt;	
                      &lt;div&gt;&lt;i class=&quot;iconfont icon-fontclass-huiyuankaquanrukou&quot;&gt;&lt;/i&gt;&lt;/div&gt;
                      &lt;span class=&quot;title&quot;&gt;社交电商&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;span class=&quot;describe&quot;&gt;根据您的需求匹配现有功能，提供多行业多场景的标准电商系统解决方案。&lt;/span&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;right_func&quot;&gt;
                    &lt;div&gt;
                      &lt;div&gt;&lt;i class=&quot;iconfont icon-fontclass-yue&quot;&gt;&lt;/i&gt;&lt;/div&gt;
                      &lt;span class=&quot;title&quot;&gt;定制开发&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;span class=&quot;describe&quot;&gt;根据客户业务需求，梳理业务流程，为客户制定开发个性化商城系统。&lt;/span&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/div&gt;
              &lt;div class=&quot;image_contain&quot;&gt;
                &lt;span class=&quot;image_line&quot;&gt;&lt;/span&gt;
                &lt;img src=&quot;official/website/images/app_introduce/shape.png&quot; alt=&quot;&quot; class=&quot;screen&quot; /&gt;
                &lt;img src=&quot;official/website/images/app_introduce/images_detail_1.png&quot; alt=&quot;&quot; class=&quot;img_detail&quot; /&gt;
                &lt;img src=&quot;official/website/images/app_introduce/screen.png&quot; alt=&quot;&quot; class=&quot;shap&quot; /&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;swiper-slide home_detail&quot;&gt;
            &lt;div class=&quot;top_detail&quot;&gt;
              &lt;div class=&quot;image_contain&quot;&gt;
                &lt;span class=&quot;image_line&quot;&gt;&lt;/span&gt;
                &lt;img src=&quot;official/website/images/app_introduce/shape.png&quot; alt=&quot;&quot; class=&quot;screen&quot; /&gt;
                &lt;img src=&quot;official/website/images/app_introduce/images_detail_2.png&quot; alt=&quot;&quot; class=&quot;img_detail&quot; /&gt;
                &lt;img src=&quot;official/website/images/app_introduce/screen.png&quot; alt=&quot;&quot; class=&quot;shap&quot; /&gt;
              &lt;/div&gt;
              &lt;div class=&quot;contain&quot;&gt;
                &lt;div class=&quot;top_introduce&quot;&gt;
                  &lt;span class=&quot;title&quot;&gt;中国主流的社交商城系统&lt;/span&gt;
                  &lt;span class=&quot;describe&quot;&gt;满足多平台一键开店六网合一&lt;/span&gt;
                  &lt;span class=&quot;objective&quot;&gt;微信公众号、小程序、app、支付宝、pc端、wap等多个平台，数据同步，统一管理，全方位引流，聚合流量曝光引流。&lt;/span&gt;
                &lt;/div&gt;
                &lt;div class=&quot;buttom_introduce&quot;&gt;
                  &lt;div class=&quot;left_func&quot;&gt;
                    &lt;div &gt;	
                      &lt;div&gt;&lt;i class=&quot;iconfont icon-fontclass-dashang&quot;&gt;&lt;/i&gt;&lt;/div&gt;
                      &lt;span class=&quot;title&quot;&gt;微信视频号&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;span class=&quot;describe&quot;&gt;基于微信生态，打通微信视频号等多种功能，可实现视频号拓展链接，视频号直播带货，撬动12亿活跃用户流量。&lt;/span&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;right_func&quot;&gt;
                    &lt;div&gt;
                      &lt;div&gt;&lt;i class=&quot;iconfont icon-ht_content_goods&quot;&gt;&lt;/i&gt;&lt;/div&gt;
                      &lt;span class=&quot;title&quot;&gt;企业微信号&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;span class=&quot;describe&quot;&gt;基于企微管理功能，对接企业微信客服消息通知，实现企业微信群引流，群拓客，群锁客效果，助力企业高效管理。&lt;/span&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;
          &lt;div class=&quot;swiper-slide home_detail&quot;&gt;
            &lt;div class=&quot;top_detail&quot;&gt;
              &lt;div class=&quot;contain&quot;&gt;
                &lt;div class=&quot;top_introduce&quot;&gt;
                  &lt;span class=&quot;title&quot;&gt;中国主流的社交商城系统&lt;/span&gt;
                  &lt;span class=&quot;describe&quot;&gt;满足多样化业务分润模式&lt;/span&gt;
                  &lt;span class=&quot;objective&quot;&gt;完善的分润机制，帮助商家快速裂变推广，助力企业打造“多样化业务模式+高效率成单分润”全网电商平台。&lt;/span&gt;
                &lt;/div&gt;
                &lt;div class=&quot;buttom_introduce&quot;&gt;
                  &lt;div class=&quot;left_func&quot;&gt;
                    &lt;div &gt;	
                      &lt;div&gt;&lt;i class=&quot;iconfont icon-fontclass-jilu&quot;&gt;&lt;/i&gt;&lt;/div&gt;
                      &lt;span class=&quot;title&quot;&gt;推客分红&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;span class=&quot;describe&quot;&gt;多等级身份二级分销功能，实现“自购省钱，分享赚钱”的社交电商平台，进入全民带货新时代。&lt;/span&gt;
                  &lt;/div&gt;
                  &lt;div class=&quot;right_func&quot;&gt;
                    &lt;div&gt;
                      &lt;div&gt;&lt;i class=&quot;iconfont icon-fontclass-daili&quot;&gt;&lt;/i&gt;&lt;/div&gt;
                      &lt;span class=&quot;title&quot;&gt;拼团分红&lt;/span&gt;
                    &lt;/div&gt;
                    &lt;span class=&quot;describe&quot;&gt;阶梯拼团，拼团奖励，抢团等多种拼团玩法，快速传播裂变，提升拉新效率，占领电商万亿风口。&lt;/span&gt;
                  &lt;/div&gt;
                &lt;/div&gt;
              &lt;/div&gt;
              &lt;div class=&quot;image_contain&quot;&gt;
                &lt;span class=&quot;image_line&quot;&gt;&lt;/span&gt;
                &lt;img src=&quot;official/website/images/app_introduce/shape.png&quot; alt=&quot;&quot; class=&quot;screen&quot; /&gt;
                &lt;img src=&quot;official/website/images/app_introduce/images_detail_3.png&quot; alt=&quot;&quot; class=&quot;img_detail&quot; /&gt;
                &lt;img src=&quot;official/website/images/app_introduce/screen.png&quot; alt=&quot;&quot; class=&quot;shap&quot; /&gt;
              &lt;/div&gt;
            &lt;/div&gt;
          &lt;/div&gt;'
            ];

            \Illuminate\Support\Facades\DB::table('yz_official_website_multiple')
                ->where('theme_id',21)
                ->where('identification','default_home')
                ->update($update_data);
        }


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
