<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYzOfficialWebsiteThemeSet extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('yz_official_website_theme_set')) {
            Schema::create('yz_official_website_theme_set',function (Blueprint $table){
                $table->increments('id')->comment('主键id');
                $table->integer('uniacid')->default(0)->comment('公众号id');
                $table->string('title')->nullable()->comment('主题名称');
                $table->tinyInteger('is_default')->default(0)->comment('是否默认 1-是 0-否');
                $table->text('basic')->nullable()->comment('基础');
                $table->text('top')->nullable()->comment('头部');
                $table->text('tail')->nullable()->comment('尾部');
                $table->integer('created_at')->nullable();
                $table->integer('updated_at')->nullable();
                $table->integer('deleted_at')->nullable();
            });

            $data = [
                "id" => 1,
                "title" => "官网",
                "is_default" => 1,
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
                "created_at" => time(),
                "updated_at" => time()
            ];

            \Illuminate\Support\Facades\DB::table("yz_official_website_theme_set")->insert($data);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('yz_official_website_theme_set');
    }
}
