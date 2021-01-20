<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzOfficialWebsiteMultipleList extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('yz_official_website_multiple')) {
            Schema::create('yz_official_website_multiple',function (Blueprint $table){
                $table->increments('id')->comment('主键id');
                $table->integer('uniacid')->default(0)->comment('公众号id');
                $table->integer('theme_id')->comment('主题ID');
                $table->string('name')->comment('名称');
                $table->string('identification')->comment('标识');

                $table->string('mul_title')->comment('标题');
                $table->string('keyword')->comment('关键词');
                $table->string('describe')->nullable()->comment('描述');

                $table->text('detail')->nullable()->comment('详情');
                $table->string('url')->comment('链接');
                $table->longText('article')->nullable()->comment('文章部分');
                $table->longText('helper')->nullable()->comment('帮助部分');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            $data = [
                [
                    "theme_id" => 1,
                    "name" => "首页",
                    "identification" => "default_home",
                    "mul_title" => "测试",
                    "keyword" => "测试",
                    "describe" => "测试",
                    "detail" => '&lt;div class=&quot;banner nav-bottom&quot;&gt;
&lt;div class=&quot;banner-a&quot;&gt;
&lt;div class=&quot;banner-title&quot;&gt;可信赖的第三方电子合同平台&lt;/div&gt;
&lt;div class=&quot;banner-title-en&quot;&gt;YOU TRUSTED THIRD PARTY PLATFORM OF ELECTRONIC CONTRACT&lt;/div&gt;
&lt;div class=&quot;banner-slogan&quot;&gt;
&lt;div class=&quot;banner-slogan-a&quot;&gt;便捷&lt;/div&gt;
&lt;div class=&quot;banner-slogan-a&quot;&gt;高效&lt;/div&gt;
&lt;div class=&quot;banner-slogan-a&quot; style=&quot;border: 0;&quot;&gt;安全&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;传统纸质合同的aa痛处&lt;/div&gt;
&lt;div class=&quot;tradition-con&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/index_part1_1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;合同签署成本高&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;纸质合同线下签署的方式，带来了高昂的纸张成本，打印成本，异地签名的快递成本等&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/index_part1_2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;合同签署效率低&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;线下签订合同需要走各种审批流程，等待公章，异地签署更是要来回快递，平均耗时3-10天，成本高，周期长，效率低，严重影响业务进度&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/index_part1_3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;合同管理难&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;大量纸质合同的管理，查找困难，并且容易损坏丢失&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/index_part1_4.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;合同易篡改&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;线下签名无法验证签署人身份真实性，容易出现盗用、私刻公章和篡改合同等现象，存在伪假冒代签的风险&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition advantage&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;便捷签署，安全可靠&lt;/div&gt;
&lt;div class=&quot;tradition-con&quot;&gt;
&lt;div class=&quot;tradition-one tradition-two&quot;&gt;&lt;img src=&quot;official/images/index_part2_1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;安全可靠&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;区块链存储 防止篡改、信 息泄漏&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one tradition-two&quot;&gt;&lt;img src=&quot;official/images/index_part2_2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;合法合规&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;真正具有法律效力的电子 签名、印章&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one tradition-two&quot;&gt;&lt;img src=&quot;official/images/index_part2_3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;快捷高效&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;随时随地一键签署&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one tradition-two&quot;&gt;&lt;img src=&quot;official/images/index_part2_4.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;应用广泛&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;满足多维度和个性化需求&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;serve&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;一站式电子合同服务&lt;/div&gt;
&lt;div class=&quot;serve-con&quot;&gt;
&lt;div class=&quot;serve-one&quot;&gt;
&lt;div&gt;&lt;img src=&quot;official/images/index_part3_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;serve-title&quot;&gt;电子签名&lt;/div&gt;
&lt;div class=&quot;serve-content&quot;&gt;
&lt;div&gt;可靠合规&lt;/div&gt;
&lt;div&gt;具有法律效应&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;serve-one&quot;&gt;
&lt;div&gt;&lt;img src=&quot;official/images/index_part3_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;serve-title&quot;&gt;身份认证&lt;/div&gt;
&lt;div class=&quot;serve-content&quot;&gt;
&lt;div&gt;依托高品质权威数据源&lt;/div&gt;
&lt;div&gt;确保签署主体真实有效&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;serve-one&quot;&gt;
&lt;div&gt;&lt;img src=&quot;official/images/index_part3_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;serve-title&quot;&gt;智能合同&lt;/div&gt;
&lt;div class=&quot;serve-content&quot;&gt;
&lt;div&gt;实现在线签署&lt;/div&gt;
&lt;div&gt;智能审批&lt;/div&gt;
&lt;div&gt;合同管理&lt;/div&gt;
&lt;div&gt;等便捷功能&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;serve-one&quot;&gt;
&lt;div&gt;&lt;img src=&quot;official/images/index_part3_4.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;serve-title&quot;&gt;存证服务&lt;/div&gt;
&lt;div class=&quot;serve-content&quot;&gt;
&lt;div&gt;接入蚂蚁区块链技术&lt;/div&gt;
&lt;div&gt;证据链同步各权威机构&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;serve-one&quot;&gt;
&lt;div&gt;&lt;img src=&quot;official/images/index_part3_5.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;serve-title&quot;&gt;法律服务&lt;/div&gt;
&lt;div class=&quot;serve-content&quot;&gt;
&lt;div&gt;联合各权威司法机构&lt;/div&gt;
&lt;div&gt;打造安全服务体系&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;industry&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;芸签，适用对象和行业&lt;/div&gt;
&lt;div class=&quot;industry-con&quot;&gt;
&lt;div id=&quot;golast&quot; class=&quot;industry-icon&quot;&gt;&amp;lt;&lt;/div&gt;
&lt;div id=&quot;industry_one&quot; class=&quot;industry-one&quot;&gt;
&lt;div class=&quot;industry-one-a industry-one-a-yh&quot;&gt;银行金融&lt;/div&gt;
&lt;div class=&quot;industry-one-a industry-one-a-jy&quot;&gt;教育培训&lt;/div&gt;
&lt;div class=&quot;industry-one-a industry-one-a-wl&quot;&gt;物流运输&lt;/div&gt;
&lt;div class=&quot;industry-one-a industry-one-a-ly&quot;&gt;旅游服务&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;gonext&quot; class=&quot;industry-icon&quot;&gt;&amp;gt;&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ],
                [
                    "theme_id" => 1,
                    "name" => "解决方案",
                    "identification" => "project_page",
                    "mul_title" => "测试",
                    "keyword" => "测试",
                    "describe" => "测试",
                    "detail" => '&lt;div class=&quot;project-banner nav-bottom&quot;&gt;
&lt;div class=&quot;banner-a&quot;&gt;
&lt;div class=&quot;banner-title&quot;&gt;应用场景丰富&lt;/div&gt;
&lt;div class=&quot;banner-title2&quot;&gt;一站式解决企业签署难题，降本增效&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;project-industry&quot;&gt;
&lt;div class=&quot;project-industry-con&quot;&gt;
&lt;div id=&quot;golast&quot; class=&quot;project-industry-icon&quot;&gt;&amp;lt;&lt;/div&gt;
&lt;div id=&quot;industry_one&quot; class=&quot;project-industry-one&quot;&gt;
&lt;div class=&quot;project-industry-one-a&quot;&gt;
&lt;div id=&quot;industry-li1&quot; class=&quot;project-industry-one-a-li&quot;&gt;&lt;img class=&quot;img-show&quot; src=&quot;official/images/project_part1_1_active.png&quot; /&gt; &lt;img class=&quot;img-hidden&quot; src=&quot;official/images/project_part1_1_normal.png&quot; /&gt;
&lt;div class=&quot;project-industry-word blue-color&quot;&gt;银行金融&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;project-industry-one-a&quot;&gt;
&lt;div id=&quot;industry-li2&quot; class=&quot;project-industry-one-a-li&quot;&gt;&lt;img class=&quot;img-hidden&quot; src=&quot;official/images/project_part1_2_active.png&quot; /&gt; &lt;img class=&quot;img-show&quot; src=&quot;official/images/project_part1_2_normal.png&quot; /&gt;
&lt;div class=&quot;project-industry-word&quot;&gt;互联网＋&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;project-industry-one-a&quot;&gt;
&lt;div id=&quot;industry-li3&quot; class=&quot;project-industry-one-a-li&quot;&gt;&lt;img class=&quot;img-hidden&quot; src=&quot;official/images/project_part1_3_active.png&quot; /&gt; &lt;img class=&quot;img-show&quot; src=&quot;official/images/project_part1_3_normal.png&quot; /&gt;
&lt;div class=&quot;project-industry-word&quot;&gt;人力资源&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;project-industry-one-a&quot;&gt;
&lt;div id=&quot;industry-li4&quot; class=&quot;project-industry-one-a-li&quot;&gt;&lt;img class=&quot;img-hidden&quot; src=&quot;official/images/project_part1_4_active.png&quot; /&gt; &lt;img class=&quot;img-show&quot; src=&quot;official/images/project_part1_4_normal.png&quot; /&gt;
&lt;div class=&quot;project-industry-word&quot;&gt;教育培训&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;project-industry-one-a&quot;&gt;
&lt;div id=&quot;industry-li5&quot; class=&quot;project-industry-one-a-li&quot;&gt;&lt;img class=&quot;img-hidden&quot; src=&quot;official/images/project_part1_5_active.png&quot; /&gt; &lt;img class=&quot;img-show&quot; src=&quot;official/images/project_part1_5_normal.png&quot; /&gt;
&lt;div class=&quot;project-industry-word&quot;&gt;物流运输&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;project-industry-one-a&quot;&gt;
&lt;div id=&quot;industry-li6&quot; class=&quot;project-industry-one-a-li&quot;&gt;&lt;img class=&quot;img-hidden&quot; src=&quot;official/images/project_part1_6_active.png&quot; /&gt; &lt;img class=&quot;img-show&quot; src=&quot;official/images/project_part1_6_normal.png&quot; /&gt;
&lt;div class=&quot;project-industry-word&quot;&gt;房产租赁&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;project-industry-one-a&quot;&gt;
&lt;div id=&quot;industry-li7&quot; class=&quot;project-industry-one-a-li&quot;&gt;&lt;img class=&quot;img-hidden&quot; src=&quot;official/images/project_part1_7_active.png&quot; /&gt; &lt;img class=&quot;img-show&quot; src=&quot;official/images/project_part1_7_normal.png&quot; /&gt;
&lt;div class=&quot;project-industry-word&quot;&gt;旅游行业&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;project-industry-one-a&quot;&gt;
&lt;div id=&quot;industry-li8&quot; class=&quot;project-industry-one-a-li&quot;&gt;&lt;img class=&quot;img-hidden&quot; src=&quot;official/images/project_part1_8_active.png&quot; /&gt; &lt;img class=&quot;img-show&quot; src=&quot;official/images/project_part1_8_normal.png&quot; /&gt;
&lt;div class=&quot;project-industry-word&quot;&gt;零售制造&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;project-industry-one-a&quot;&gt;
&lt;div id=&quot;industry-li9&quot; class=&quot;project-industry-one-a-li&quot;&gt;&lt;img class=&quot;img-hidden&quot; src=&quot;official/images/project_part1_9_active.png&quot; /&gt; &lt;img class=&quot;img-show&quot; src=&quot;official/images/project_part1_9_normal.png&quot; /&gt;
&lt;div class=&quot;project-industry-word&quot;&gt;汽车服务&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;gonext&quot; class=&quot;project-industry-icon&quot;&gt;&amp;gt;&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;行业痛点&lt;/div&gt;
&lt;div id=&quot;tradition-id1&quot; class=&quot;tradition-con tradition-con-show&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;投放／贷款周期长&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;异地签约时间长&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;业务流程繁琐&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-4.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;纸质合同成本高&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tradition-id2&quot; class=&quot;tradition-con tradition-con-hidden&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;纸质合同安全隐患&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;管理效率低&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;运营成本高&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-4.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;快递时间长&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tradition-id3&quot; class=&quot;tradition-con tradition-con-hidden&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;纸质合同签署周期长&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;异地签署繁琐&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;纸质合同成本高&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-4.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;纸质合同管理难&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tradition-id4&quot; class=&quot;tradition-con tradition-con-hidden&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;异地签署时间长&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;流程繁琐&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;线下行业发展瓶颈&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-4.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;报名周期长&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tradition-id5&quot; class=&quot;tradition-con tradition-con-hidden&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;发货、提货效率低&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;权责难分&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;合同成本高&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-4.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;合同管理难&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tradition-id6&quot; class=&quot;tradition-con tradition-con-hidden&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;签署效率低&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;合同管理难&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;签约成本高&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tradition-id7&quot; class=&quot;tradition-con tradition-con-hidden&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;签署效率低&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;用户体验差、纠纷多&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;合同管理难&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-4.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;合同成本高&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tradition-id8&quot; class=&quot;tradition-con tradition-con-hidden&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;合同管理难&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;合同成本高&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;合同签署效率低&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tradition-id9&quot; class=&quot;tradition-con tradition-con-hidden&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;合同签署效率低&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;签署成本高&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/project_part2-3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;合同管理难&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scene&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;应用场景&lt;/div&gt;
&lt;div class=&quot;scene-one&quot;&gt;
&lt;div class=&quot;scene-left&quot;&gt;&lt;img src=&quot;official/images/product_part1_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scene-right&quot;&gt;
&lt;div id=&quot;scene-right-id1&quot; style=&quot;display: block;&quot;&gt;信用卡用户:信用卡开卡&lt;br /&gt;协议按揭用户:按揭协议 &lt;br /&gt; 零售业务用户:业务办理协议&lt;br /&gt;贷款用户:贷款协议&lt;/div&gt;
&lt;div id=&quot;scene-right-id2&quot; style=&quot;display: none;&quot;&gt;采购合同&lt;br /&gt;物流合同&lt;br /&gt;销售供货合同&lt;br /&gt;抵押贷款合同&lt;br /&gt;入驻协议&lt;/div&gt;
&lt;div id=&quot;scene-right-id3&quot; style=&quot;display: none;&quot;&gt;劳动合同&lt;br /&gt;网络招聘平台服务协议&lt;br /&gt;人事代理平台合同&lt;br /&gt;人才派遣服务协议&lt;/div&gt;
&lt;div id=&quot;scene-right-id4&quot; style=&quot;display: none;&quot;&gt;培训合同&lt;br /&gt;线上购买课程合同&lt;br /&gt;外聘协议&lt;br /&gt;合作协议&lt;/div&gt;
&lt;div id=&quot;scene-right-id5&quot; style=&quot;display: none;&quot;&gt;发货、提货单、签收单&lt;br /&gt;承运合作协议&lt;br /&gt;司机注册协议&lt;br /&gt;运费保理协议&lt;/div&gt;
&lt;div id=&quot;scene-right-id6&quot; style=&quot;display: none;&quot;&gt;房屋交易合同&lt;br /&gt;租赁合同&lt;br /&gt;委托协议&lt;br /&gt;物业服务协议&lt;/div&gt;
&lt;div id=&quot;scene-right-id7&quot; style=&quot;display: none;&quot;&gt;旅游合同&lt;br /&gt;旅游平台合作&lt;br /&gt;酒店、景点协议&lt;br /&gt;代理协议&lt;br /&gt;旅游金融服务&lt;/div&gt;
&lt;div id=&quot;scene-right-id8&quot; style=&quot;display: none;&quot;&gt;采购协议&lt;br /&gt;销售合同&lt;br /&gt;运输协议&lt;br /&gt;供应商合作协议&lt;/div&gt;
&lt;div id=&quot;scene-right-id9&quot; style=&quot;display: none;&quot;&gt;采购合同&lt;br /&gt;销售合同&lt;br /&gt;委托服务协议&lt;br /&gt;汽车金融协议&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;方案优势&lt;/div&gt;
&lt;div id=&quot;scheme-a1&quot; class=&quot;scheme-a&quot; style=&quot;display: flex;&quot;&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;在线办理签约，用户体验好&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;节约纸墨消耗、人员场所管理成本&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;安全合规，加速投资、放贷效率&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_4.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;高性能和高稳定性&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;scheme-a2&quot; class=&quot;scheme-a&quot; style=&quot;display: none;&quot;&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;采用电子签约平台加密存储，杜绝合同泄密、丢失及损坏&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;分类管理统计合同，查找管理方便&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;线上签署，无需快递，快速完成签约合作&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_4.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;合同云存储，不需要专人专场管理&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;scheme-a3&quot; class=&quot;scheme-a&quot; style=&quot;display: none;&quot;&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;远程入职，大幅缩减员工入职周期及HR入职操作&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;远程签署，派遣，加速劳务合作&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;合规有效，即使生效&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_4.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;传统人力资源公司实现业务全程线上化&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;scheme-a4&quot; class=&quot;scheme-a&quot; style=&quot;display: none;&quot;&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;帮助学院快速购买课程，快速学习&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;缩短报名周期，用户体验良好&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;高效聘用，快速与教师达成雇佣关系&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;scheme-a5&quot; class=&quot;scheme-a&quot; style=&quot;display: none;&quot;&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;降低物流企业运营成本&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;实时掌握流转过程，确保货物高效、准确流转；&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;实时签收，提升用户体验&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;scheme-a6&quot; class=&quot;scheme-a&quot; style=&quot;display: none;&quot;&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;高效签约，减少用户购房流程及周期&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;线上租房，快速达成租赁协议&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;用户无需到场，即可完成合同签订，用户体验好&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_4.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;加强物业管理灵活性&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;scheme-a7&quot; class=&quot;scheme-a&quot; style=&quot;display: none;&quot;&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;线上签署，高效便捷&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;法律闭环，确保纠纷发生时，能够提供相应的司法保障&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;自动存放到存证平台，实现云存储&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_4.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;加速签署环节及云存储环节，降低成本&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;scheme-a8&quot; class=&quot;scheme-a&quot; style=&quot;display: none;&quot;&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;分类管理统计，查找管理方便&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;无需到达现场即可完成签订，用户体验良好&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;线上一键签署，快速签约合作&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;scheme-a9&quot; class=&quot;scheme-a&quot; style=&quot;display: none;&quot;&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;缩短采购周期，确保车辆即使送达&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;在线销售，简化购车流程&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one blue-bg&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;在线委托，提升用户体验感&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;scheme-one&quot;&gt;
&lt;div class=&quot;scheme-one-img&quot;&gt;&lt;img src=&quot;official/images/project_part4_4.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;scheme-one-word&quot;&gt;在线贷款分期，提升用户购车灵活性&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ],
                [
                    "theme_id" => 1,
                    "name" => "关于我们",
                    "identification" => "about_us",
                    "mul_title" => "测试",
                    "keyword" => "测试",
                    "describe" => "测试",
                    "detail" => '&lt;div class=&quot;contact-banner nav-bottom&quot;&gt;
&lt;div class=&quot;contact-banner-a&quot;&gt;
&lt;div class=&quot;contact-banner-left&quot;&gt;
&lt;div class=&quot;contact-banner-left-one&quot;&gt;安全有保障，让签约更高效&lt;/div&gt;
&lt;div class=&quot;contact-banner-left-two&quot;&gt;SAFEGUARD FOR SIGNING MORE EFFICIENT&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;contact-banner-right&quot;&gt;&lt;img src=&quot;official/images/contact_bannerbg_content.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;brief&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;芸签简介&lt;/div&gt;
&lt;div class=&quot;brief-con&quot;&gt;
&lt;div class=&quot;brief-left&quot;&gt;
&lt;div&gt;芸签科技信息有限公司位于国际商贸中心的广州，是基于SAAS模式的第三方电子合同平台，依托中国电子签名行业第一品牌E签宝，融合芸众科技的领先创新技术，为客户提供电子合同、电子签名、证据保全及法律维权等服务，帮助客户轻松完成线上一键合同签署，实现合同签约电子化、智能化。&lt;/div&gt;
&lt;div&gt;服务于电子商务、人力资源、汽车服务、零售、物流、旅游、教育、供应链、房地产、租赁、金融等全行业以及政府机构。&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;brief-right&quot;&gt;&lt;img src=&quot;official/images/contact_part2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;culture&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;公司文化&lt;/div&gt;
&lt;div class=&quot;culture-con&quot;&gt;
&lt;div class=&quot;culture-one&quot;&gt;
&lt;div class=&quot;culture-one-img&quot;&gt;&lt;img src=&quot;official/images/contact_part2_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;culture-one-word&quot;&gt;
&lt;div class=&quot;culture-one-word-one&quot;&gt;宗旨&lt;/div&gt;
&lt;div&gt;为用户提供便捷高效，安全可靠的在线合同签署服务&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;culture-one&quot;&gt;
&lt;div class=&quot;culture-one-img&quot;&gt;&lt;img src=&quot;official/images/contact_part2_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;culture-one-word&quot;&gt;
&lt;div class=&quot;culture-one-word-one&quot;&gt;理念&lt;/div&gt;
&lt;div&gt;科学诚信、开拓创新、优质高效&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;culture-one&quot;&gt;
&lt;div class=&quot;culture-one-img&quot;&gt;&lt;img src=&quot;official/images/contact_part2_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;culture-one-word&quot;&gt;
&lt;div class=&quot;culture-one-word-one&quot;&gt;价值观&lt;/div&gt;
&lt;div&gt;拼搏、严谨、责任、共赢&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;culture-one&quot;&gt;
&lt;div class=&quot;culture-one-img&quot;&gt;&lt;img src=&quot;official/images/contact_part2_4.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;culture-one-word&quot;&gt;
&lt;div class=&quot;culture-one-word-one&quot;&gt;愿景&lt;/div&gt;
&lt;div&gt;致力成为全国优秀领先的电子合同签约平台&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;contactus&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;联系我们&lt;/div&gt;
&lt;div class=&quot;contactus-con&quot;&gt;
&lt;div class=&quot;contactus-left&quot;&gt;&lt;img src=&quot;official/images/contact_part3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;contactus-right&quot;&gt;
&lt;div&gt;咨询电话：12223333333&lt;/div&gt;
&lt;div&gt;邮箱：9572315554455@163.com&lt;/div&gt;
&lt;div&gt;公司地址：广州市白云区44511222&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ],
                [
                    "theme_id" => 1,
                    "name" => "资源中心",
                    "identification" => "resource_page",
                    "mul_title" => "测试",
                    "keyword" => "测试",
                    "describe" => "测试",
                    "detail" => '&lt;div class=&quot;sec-nav&quot; style=&quot;padding-top: 74px;&quot;&gt;
&lt;div class=&quot;sec-nav-con&quot;&gt;
&lt;div id=&quot;sec-nav-one1&quot; class=&quot;sec-nav-one&quot; style=&quot;color: #00a2ff;&quot;&gt;电子合同百科&lt;/div&gt;
&lt;div id=&quot;sec-nav-one2&quot; class=&quot;sec-nav-one&quot;&gt;用户指南&lt;/div&gt;
&lt;div id=&quot;sec-nav-one3&quot; class=&quot;sec-nav-one&quot;&gt;帮助中心&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tab1&quot; class=&quot;article&quot;&gt;
&lt;div class=&quot;menu&quot;&gt;资源中心&amp;nbsp;&amp;nbsp;&amp;gt;&amp;nbsp;&amp;nbsp;&lt;span id=&quot;category_name1&quot; class=&quot;word-blue&quot;&gt;&lt;/span&gt;&lt;/div&gt;
&lt;div class=&quot;article-con&quot;&gt;
&lt;div class=&quot;article-one&quot;&gt;
&lt;div class=&quot;rticle-one-title&quot;&gt;合同百科&lt;/div&gt;
&lt;div id=&quot;category_ul&quot; class=&quot;rticle-one-li&quot;&gt;&amp;nbsp;&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;article-two&quot;&gt;
&lt;div id=&quot;category_name2&quot; class=&quot;rticle-one-title&quot;&gt;&amp;nbsp;&lt;/div&gt;
&lt;div id=&quot;article-ul&quot; class=&quot;article-two-lis&quot;&gt;&amp;nbsp;&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tab2&quot; class=&quot;sec&quot; style=&quot;display: none;&quot;&gt;
&lt;div class=&quot;guide&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;视频教程&lt;/div&gt;
&lt;div class=&quot;guide-con&quot;&gt;
&lt;div class=&quot;guide-one&quot;&gt;&lt;video src=&quot;http://1251768088.vod2.myqcloud.com/9b37a98dvodgzp1251768088/c2ebe9a65285890802888538998/AF9QQ3rpRCkA.mp4&quot; controls=&quot;controls&quot; width=&quot;270&quot; height=&quot;150&quot;&gt;&lt;/video&gt;
&lt;div class=&quot;video-title&quot;&gt;PC端创建电子合同&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;guide-one&quot;&gt;&lt;video src=&quot;http://1251768088.vod2.myqcloud.com/9b37a98dvodgzp1251768088/c2fa0d125285890802888541246/3JFNNdaAKCoA.mp4&quot; controls=&quot;controls&quot; width=&quot;270&quot; height=&quot;150&quot;&gt;&lt;/video&gt;
&lt;div class=&quot;video-title&quot;&gt;PC端发起合同签署&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;guide-one&quot;&gt;&lt;video src=&quot;http://1251768088.vod2.myqcloud.com/9b37a98dvodgzp1251768088/c2c337915285890802888510276/O1p4FpAfeTgA.mp4&quot; controls=&quot;controls&quot; width=&quot;270&quot; height=&quot;150&quot;&gt;&lt;/video&gt;
&lt;div class=&quot;video-title&quot;&gt;（PC端）客户签署合同&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;guide-one&quot;&gt;&lt;video src=&quot;http://1251768088.vod2.myqcloud.com/9b37a98dvodgzp1251768088/f16104fb5285890802770187889/aAo8jbwIvQoA.mp4&quot; controls=&quot;controls&quot; width=&quot;270&quot; height=&quot;150&quot;&gt;&lt;/video&gt;
&lt;div class=&quot;video-title&quot;&gt;（手机端-微信）客户签署合同&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;attestation&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;实名认证&lt;/div&gt;
&lt;div class=&quot;attestation-con&quot;&gt;
&lt;div class=&quot;attestation-one&quot;&gt;
&lt;div class=&quot;attestation-one-left&quot;&gt;&lt;img src=&quot;official/images/resource_part2_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div&gt;个人实名认证&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;attestation-one&quot;&gt;
&lt;div class=&quot;attestation-one-left&quot;&gt;&lt;img src=&quot;official/images/resource_part2_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div&gt;企业实名认证&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;contract&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;签署合同&lt;/div&gt;
&lt;div class=&quot;contract-con&quot;&gt;
&lt;div class=&quot;contract-one&quot;&gt;
&lt;div class=&quot;contract-one-img&quot;&gt;&lt;img src=&quot;official/images/resource_part3_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;contract-one-word&quot;&gt;创建合同模板&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;contract-one&quot;&gt;
&lt;div class=&quot;contract-one-img&quot;&gt;&lt;img src=&quot;official/images/resource_part3_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;contract-one-word&quot;&gt;发起合同签署&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;contract-one&quot;&gt;
&lt;div class=&quot;contract-one-img&quot;&gt;&lt;img src=&quot;official/images/resource_part3_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;contract-one-word&quot;&gt;客户端签署&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tab3&quot; class=&quot;third&quot; style=&quot;display: none;&quot;&gt;
&lt;div class=&quot;search&quot;&gt;
&lt;div class=&quot;search-con&quot;&gt;&lt;input id=&quot;search-input&quot; class=&quot;search-input&quot; type=&quot;text&quot; placeholder=&quot;请输入搜索内容&quot; /&gt;
&lt;div id=&quot;search-btn&quot; class=&quot;search-btn&quot;&gt;搜索&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;problem&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;常见问题&lt;/div&gt;
&lt;div id=&quot;problem-con&quot; class=&quot;problem-con&quot;&gt;&amp;nbsp;&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ],
                [
                    "theme_id" => 1,
                    "name" => "资源详情",
                    "identification" => "resource_detail",
                    "mul_title" => "测试",
                    "keyword" => "测试",
                    "describe" => "测试",
                    "detail" => '',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ],
                [
                    "theme_id" => 1,
                    "name" => "产品",
                    "identification" => "product_num",
                    "mul_title" => "测试",
                    "keyword" => "测试",
                    "describe" => "测试",
                    "detail" => '&lt;div class=&quot;product-banner nav-bottom&quot;&gt;
&lt;div class=&quot;product-banner-a&quot;&gt;
&lt;div class=&quot;product-banner-left&quot;&gt;
&lt;div class=&quot;product-banner-left-one&quot;&gt;芸签为用户提供一套完整的&lt;/div&gt;
&lt;div class=&quot;product-banner-left-two&quot;&gt;全生态电子签名服务&lt;/div&gt;
&lt;div class=&quot;product-banner-left-three&quot;&gt;帮助企业随时随地处理有关合同的一切事务，助力企业解决签署难题，降低成本&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;product-banner-right&quot;&gt;&lt;img src=&quot;official/images/product_bannerbg_content.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tabs&quot;&gt;
&lt;div class=&quot;tabs-a&quot;&gt;
&lt;div id=&quot;tabs-one1&quot; class=&quot;tabs-one&quot;&gt;
&lt;div class=&quot;tabs-one-word&quot;&gt;电子签名&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tabs-one2&quot; class=&quot;tabs-one&quot;&gt;
&lt;div&gt;身份认证&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tabs-one3&quot; class=&quot;tabs-one&quot;&gt;
&lt;div&gt;智能合同&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tabs-one4&quot; class=&quot;tabs-one&quot;&gt;
&lt;div&gt;存证服务&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tabs-one5&quot; class=&quot;tabs-one&quot;&gt;
&lt;div&gt;法律服务&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tabs-b1&quot; class=&quot;tabs-b tabs-b-show&quot;&gt;
&lt;div class=&quot;tabs-b-left&quot;&gt;&lt;img src=&quot;official/images/product_part1_1.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;tabs-b-right&quot;&gt;芸签从身份认证数据源、证书核验、可信时间戳、私钥保存位置等多个关键点入手提供技术保障，同时从实名认证、意愿认证、签名、存证等环节提供可靠签署流程，证据实时上链，免除平台客户自证清白的成本，也为用户提供放心的签署服务。&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tabs-b2&quot; class=&quot;tabs-b&quot;&gt;
&lt;div class=&quot;tabs-b-left&quot;&gt;&lt;img src=&quot;official/images/product_part1_2.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;tabs-b-right&quot;&gt;芸签身份认证服务依托高品质权威数据源，为客户提供可信、可靠、灵活的身份认证。&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tabs-b3&quot; class=&quot;tabs-b&quot;&gt;
&lt;div class=&quot;tabs-b-left&quot;&gt;&lt;img src=&quot;official/images/product_part1_3.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;tabs-b-right&quot;&gt;芸签为客户提供智能合同管理服务，在线发起合同，智能审批，线上云存储，能精准快捷查找所需合同。&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tabs-b4&quot; class=&quot;tabs-b&quot;&gt;
&lt;div class=&quot;tabs-b-left&quot;&gt;&lt;img src=&quot;official/images/product_part1_4.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;tabs-b-right&quot;&gt;芸签采用蚂蚁区块链存证，多重数据防篡改，帮助客户固化了一系列与签署、合同管理场景相关的电子证据链，这些证据链同步存证至各权威机构共同见证，大大增强了证据链的公信力。&lt;/div&gt;
&lt;/div&gt;
&lt;div id=&quot;tabs-b5&quot; class=&quot;tabs-b&quot;&gt;
&lt;div class=&quot;tabs-b-left&quot;&gt;&lt;img src=&quot;official/images/product_part1_5.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;tabs-b-right&quot;&gt;芸签提供追溯签署过程的各类证据报告，联合各权威司法机构，打造安全服务体系，满足用户合规性要求及应对司法纠纷。&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition&quot;&gt;
&lt;div class=&quot;tradition-title&quot;&gt;产品优势&lt;/div&gt;
&lt;div class=&quot;tradition-con&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/product_part2_1.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;安全可靠&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;区块链存储，签约主体、签约时间不可篡改。&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/product_part2_2.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;合法合规&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;具有法律效力的电子签名、印章，联合数字认证中心，确保签名主体的真实性和有效性。&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/product_part2_3.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;司法保障&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;联合公证处、司法坚定权威部门，打造安全可靠的司法保障体系。&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/product_part2_4.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;技术专业&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;采用E签宝底层技术，专业的技术团队，打造安全可靠的电子合同签约平台。&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;div class=&quot;tradition-con&quot;&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/product_part2_4.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;应用灵活&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;支持PC、H5、微信小程序等多终端签署，满足客户多样化需求。&lt;/div&gt;
&lt;/div&gt;
&lt;div style=&quot;flex: 0 0 400px;&quot;&gt;&lt;img style=&quot;width: 275px; height: 275px;&quot; src=&quot;official/images/product_part2_6.png&quot; alt=&quot;&quot; /&gt;&lt;/div&gt;
&lt;div class=&quot;tradition-one&quot;&gt;&lt;img src=&quot;official/images/product_part2_5.png&quot; alt=&quot;&quot; /&gt;
&lt;div class=&quot;tradition-one-title&quot;&gt;优质服务&lt;/div&gt;
&lt;div class=&quot;tradition-one-content&quot;&gt;优秀的售后服务团队，在线咨询、培训服务、技术支持等，多维度解决客户难题。&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;
&lt;/div&gt;',
                    "url" => '',
                    "created_at" => time(),
                    "updated_at" => time(),
                ]
            ];

            \Illuminate\Support\Facades\DB::table('yz_official_website_multiple')->insert($data);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_official_website_multiple');
    }
}
