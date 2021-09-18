<!-- <script src="{{static_url('js/echarts.js')}}" type="text/javascript"></script> -->
<script src="{{static_url('assets/js/echarts.min@5.1.2.js')}}"></script>
@extends('layouts.base')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}" />
<link rel="stylesheet" href="//at.alicdn.com/t/font_432132_ira95q5fge8.css" />
<style>
    [v-cloak] {
        display: none
    }

    @font-face {
        font-family: 'iconfont';  /* Project id 432132 */
        src: url('//at.alicdn.com/t/font_432132_ira95q5fge8.woff2?t=1630649370303') format('woff2'),
            url('//at.alicdn.com/t/font_432132_ira95q5fge8.woff?t=1630649370303') format('woff'),
            url('//at.alicdn.com/t/font_432132_ira95q5fge8.ttf?t=1630649370303') format('truetype');
    }

    .head-more {
        color: #4d8bfc;
        font-size: 16px;
    }

    .basic-function_tabs {
        margin: 0 20px;
    }

    .basic-functions {
        display: grid;
        column-gap: 20px;
        row-gap: 20px;
        grid-template-columns: repeat(6, calc(16.6% - 20px));
        padding: 20px;
        text-align: center;
    }

    .basic-functions li {
        color: #333;
        cursor: pointer;
        box-shadow: 0px 2px 13px 0px rgba(218, 218, 218, 0.34);
        border-radius: 10px;
        background-color: white;
    }

    .basic-functions li:hover {
        color: white;
        background-color: #f8a544;
    }

    .basic-functions a {
        display: block;
        padding: 20px 10px 15px;
        color: #333;
    }

    .basic-functions li>a div {
        margin-bottom: 20px;
        color: #f8a544;
        font-size: 30px;
    }

    .basic-functions li:hover>a,
    .basic-functions li:hover>a div {
        color: white;
    }

    .basic-functions li i {
        vertical-align: middle;
        font-size: 14px;
    }

    /** 统计 */
    .statistics {
        display: flex;
        justify-content: space-evenly;
        align-items: center;
        padding: 16px 0;
        text-align: center;
    }

    .statistics li>.iconfont {
        display: inline-block;
        padding: 11px 9px;
        color: #2cc08d;
        font-size: 24px;
        background-color: #e5f8f2;
        border-radius: 4px;
    }

    .statistics-count {
        display: block;
        margin-top: 14px;
        font-size: 24px;
        font-weight: bold;
    }

    .statistics-name {
        margin-top: 10px;
        color: #333;
        font-size: 14px;
    }

    /* 入口 */
    /* .entry-warp {
        height: calc(100% - 15px);
    } */

    .entry {
        display: flex;
        justify-content: space-evenly;
        margin-top: 32px;
        text-align: center;
    }

    .entry li .head-more {
        display: block;
    }

    .entry li img {
        width: 130px;
    }

    .entry-item {
        display: block;
        margin-left: 34px;
    }

    .entry-item:not(:last-child) {
        margin-bottom: 19px;

    }

    .entry-item:nth-of-type(1) {
        margin-top: 55px;
    }

    .entry-legend {
        margin-top: 26px;
        margin: 0 34px;
        color: #333;
        font-size: 14px;
    }

    .entry img {
        display: inline-block;
        margin-top: 26px;
    }

    /* 系统情况 */
    .system-situation {
        height: 300px;
        font-size: 15px;
        color: #333;
    }

    .system-situation .el-button.is-circle {
        padding: 16px;
    }

    .system-situation .el-button .el-icon-s-opportunity {
        font-size: 30px;
    }

    .system-situation_tips {
        margin: 23px 30px;
        font-size: 14px;
        color: #333;
    }

    .system-situation p {
        margin: 0px;
    }

    /** 提现动态 */

    /** 访客播报 */
    .visitor-list,
    .withdraw-list {
        padding: 0 15px;
    }

    .visitor-item:not(:last-child),
    .withdraw-item:not(:last-child) {
        margin-bottom: 35px;
    }

    .ellipsis1 {
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .member-info img {
        width: 36px;
        height: 36px;
        border-radius: 50%;
    }

    .goods-sales_list.el-table td,
    .goods-sales_list.el-table th {
        padding: 11px 0;
        font-size: 14px;
    }
</style>
<div class="all">
    <div id="app" v-cloak v-loading="loading">
        <el-row type="flex">
            <el-col :span="16">
                <div class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">基础功能指引</div>
                    </div>
                    <el-tabs class="basic-function_tabs" v-model="guideTabActiveIndex">
                        <el-tab-pane :label="guideItem.title" :name="String(itemIndex)" v-for="(guideItem,itemIndex) in guide" :key="guideItem.title"></el-tab-pane>
                    </el-tabs>
                    <ul class="basic-functions">
                        <li v-for="guideItem in guideList" :key="guideItem.name" v-if="guideItem.is_plugin===0||guideItem.is_plugin===1&&guideItem.is_enabled">
                            <a :href="guideItem.url">
                                <div class="iconfont" :class="[guideItem.icon]"></div>
                                [[ guideItem.name ]] <i class="iconfont icon-member_right"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <ul class="vue-head statistics">
                    <li v-for="(statisticsItem,itemIndex) in statistics.slice(0,4)" :key="itemIndex">
                        <div class="iconfont" :class="[ statisticsItem.icon ]" :style="{ color:statisticsItem.color,backgroundColor:statisticsItem.backgroundColor }"></div>
                        <div class="statistics-count">
                            <span v-if="itemIndex===4" :style="{ color:statisticsItem.color }">￥</span>
                            <count-to :start-val='0' :end-val="Number(statisticsItem.count)" :duration="4000" :style="{ color:statisticsItem.color }"></count-to>
                        </div>
                        <div class="statistics-name">[[ statisticsItem.title ]]</div>
                    </li>
                    <li>
                        <div class="iconfont" :class="[ statistics[4].icon ]" :style="{ color:statistics[4].color,backgroundColor:statistics[4].backgroundColor }"></div>
                        <div class="statistics-count">
                            <span :style="{ color:statistics[4].color,fontSize:'14px',marginRight:'-5px' }">￥</span>
                            <count-to :start-val='0' :end-val="Number(statistics[4]['count'])" :duration="4000" :style="{ color:statistics[4].color }" :decimals="2"></count-to>
                        </div>
                        <div class="statistics-name">[[ statistics[4].title ]]</div>
                    </li>
                </ul>
                <div class="chart vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">订单趋势</div>
                        <a class="vue-main-title-button head-more" @click="goToOrderStatisticsPage" style="cursor: pointer;">
                            查看更多订单数据统计<i class="iconfont icon-member_right"></i>
                        </a>
                    </div>
                    <div ref="chartmain" style="position:relative;width:100%; height: 433px;margin:0;padding:0;margin-top:20px;">
                        <div style="display:inline-block;position:absolute;inset:0px;margin:auto;width:300px;height:30px;">请先安装并启用统计2.0插件，请联系管理员！</div>
                    </div>
                </div>
                <div class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">销量排行</div>
                        <a class="vue-main-title-button head-more" @click="goToGoodsStatisticsPage" >
                            查看更多商品数据统计<i class="iconfont icon-member_right"></i>
                        </a>
                    </div>
                    <el-table class="goods-sales_list" :data="list" style="width: calc(100% - 30px);font-size:14px;" v-loading="loading">
                        <el-table-column prop="title" label="商品名称"></el-table-column>
                        <el-table-column prop="real_sales" label="销量" align="center"> </el-table-column>
                        <el-table-column prop="created_at" label="创建时间" align="center"> </el-table-column>
                    </el-table>
                </div>
                @if(YunShop::app()->role == 'founder')
                <div class="vue-head chart" style="padding: 11px;height: 300px;box-sizing: border-box;" v-if="status_show==1">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">资源状态</div>
                    </div>
                    <div style="display:flex;justify-content:space-around;margin-top:20px;font-size:18px;text-align: center;">
                        <div class="status-li">
                            <el-tooltip class="item" effect="dark">
                                <div slot="content" v-html="status_content"></div>
                                <el-progress type="circle" :percentage="status_rate" :stroke-width="15" color="#808cff" stroke-linecap="butt"></el-progress>
                            </el-tooltip>
                            <div style="margin-top:21px;">负载状态</div>
                        </div>
                        <div class="status-li">
                            <el-tooltip class="item" effect="dark" :content="cpu?cpu.model:''">
                                <el-progress type="circle" :percentage="cpu?cpu.using:0" :stroke-width="15" color="#496eff"></el-progress>
                            </el-tooltip>
                            <div style="margin-top:21px;">CPU使用率</div>
                            <div v-if="cpu" style="margin-top:13px;">[[cpu.num]]核心</div>
                        </div>
                        <div class="status-li">
                            <el-progress type="circle" :percentage="ram?ram.memPercent:0" :stroke-width="15" color="#ffbe4d"></el-progress>
                            <div style="margin-top:21px;">总内存使用率</div>
                            <div v-if="ram" style="margin-top:13px;">[[ram.memUsed]]/[[ram.memTotal]]</div>
                        </div>
                        <div class="status-li">
                            <el-tooltip class="item" effect="dark">
                                <div slot="content" v-html="disk_content"></div>
                                <el-progress type="circle" stroke-linecap="butt" :percentage="disk?disk.percent:0" :stroke-width="15" color="#f68838"></el-progress>
                            </el-tooltip>
                            <div style="margin-top:21px;">当前站点磁盘使用率</div>
                            <div v-if="disk" style="margin-top:13px;">[[disk.used]]G/[[disk.total]]G</div>
                        </div>
                    </div>
                </div>
                @endif
            </el-col>
            <el-col :span="8">
                <div class="vue-head entry-warp">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">主要入口</div>
                    </div>
                    <ul class="entry">
                        <li>
                            <a :href="entrance.home_url" class="head-more" target="_blank">点击访问商城首页 >></a>
                            <img :src="entrance.home_code" alt="">
                        </li>
                        <li>
                            <a :href="entrance.more_home_url" class="head-more" >更多前端入口 >></a>
                            <img :src="entrance.mini_app_code" :alt="entrance.mini_app_code_error" v-if="entrance.is_enabled_mini_app&&entrance.is_mini_app_config">
                        </li>
                    </ul>
                    <!-- <a :href="entrance.customer_service_url" class="head-more entry-item" v-if="entrance.is_enabled_customer_service" @click="goToCustomerService" target="_blank">客服登录 >></a>
                    <a :href="entrance.contract_url" class="head-more entry-item" v-if="entrance.is_enabled_contract" target="_blank">电子合同管理登录 >></a> -->
                    <div class="entry-legend" style="margin-top:20px;">
                        门店、供应商、酒店、企业管理、区域代理等独立后台登录域名同总平台，使用对应的店员账号密码即可，注意不能使用同一浏览器登录
                    </div>
                </div>
                <div class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">提现动态</div>
                    </div>
                    <div v-if="withdrawalList.length==0" style="text-align: center;transform:translateY(150px);">
                        <img src="{{ static_url('images/empty@2x.png') }}" width="300px" />
                        <p> 暂无数据~</p>
                    </div>
                    <el-carousel :interval="5000" arrow="always" direction="vertical" autoplay indicator-position="none" height="391">
                        <el-carousel-item v-for="(listItem,itemIndex) in withdrawalList" :key="itemIndex">
                            <div class="withdraw-list">
                                <el-row class="withdraw-item" v-for="withdrawalItem in listItem" :key="withdrawalItem.id" type="flex" align="middle" justify="space-between" :gutter="10">
                                    <el-col class="ellipsis1" :span="8">
                                        [[ withdrawalItem.created_at ]]
                                    </el-col>
                                    <el-col :span="8">
                                        <div class="member-info ellipsis1" v-if="withdrawalItem.has_one_member">
                                            <img :src="withdrawalItem.has_one_member.avatar_image" />
                                            [[ withdrawalItem.has_one_member.nickname ]]
                                        </div>
                                    </el-col>
                                    <el-col class="ellipsis1" :span="8" style="text-align:right;color:#f05347;">
                                        [[ withdrawalItem.amounts ]]
                                    </el-col>
                                </el-row>
                            </div>
                        </el-carousel-item>
                    </el-carousel>
                    <div style="margin-top:24px;text-align: center;" v-if="withdrawalList.length>0">
                        <a :href="withdrawalMore" class="head-more">查看更多提现记录 <i class="iconfont icon-member_right"></i></a>
                    </div>
                </div>
                <div class="vue-head">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">访客播报</div>
                    </div>
                    <div v-if="visitorEnabled&&visitorData.length==0" style="text-align: center;transform:translateY(150px);">
                        <img src="{{ static_url('images/empty@2x.png') }}" width="300px" />
                        <p> 暂无数据~</p>
                    </div>
                    <el-carousel :interval="6000" arrow="always" direction="vertical" autoplay indicator-position="none" height="391" :gutter="10" v-if="visitorEnabled">
                        <el-carousel-item v-for="(dataItem,dataItemIndex) in visitorData" :key="dataItemIndex">
                            <div class="visitor-list">
                                <el-row class="visitor-item" v-for="(item,itemIndex) in dataItem" :key="itemIndex" type="flex" align="middle" justify="space-between">
                                    <el-col class="ellipsis1" :span="8">
                                        [[ item.access_time ]]
                                    </el-col>
                                    <el-col :span="8">
                                        <div class="member-info ellipsis1">
                                            <img :src="item.avatar_image" />
                                            [[ item.name ]]
                                        </div>
                                    </el-col>
                                    <el-col class="ellipsis1" :span="8" style="text-align:right;" :stlye="{ color:item.cookie_type==='new'?'#29ba9c':'#101010' }">
                                        [[ item.cookie_type === 'new'?'新访客':'老访客' ]]
                                    </el-col>
                                </el-row>
                            </div>
                        </el-carousel-item>
                    </el-carousel>
                    <div style="margin-top:24px;text-align: center;" v-if="visitorEnabled">
                        <a :href="visitorUrl" class="head-more">查看更多访问轨迹<i class="iconfont icon-member_right"></i></a>
                    </div>
                    <div v-if="!visitorEnabled" style="padding:20% 0;text-align: center;">请先安装浏览轨迹插件</div>
                </div>
                <div class="vue-head system-situation">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">系统运行情况</div>
                    </div>
                    <div class="system-situation_tips">
                        如果为红色图标，表示系统运营异常，10分钟左右刷新未恢复正常的，请第一时间联系客服处理！
                    </div>
                    <el-row type="flex" justify="center" :gutter="20" align="center">
                        <el-col :span="5">
                            <div class="" style="text-align: center;">
                                <p v-for="(item,index) in queue_color" :key="index">
                                    <el-button v-if="queue_hearteat.daemon.queue_status == item.color" :type="item.value" :title="queue_hearteat.daemon.title" icon="el-icon-s-opportunity" circle></el-button>
                                </p>
                                <p style="margin-top:32px;"> 守护进程 <span v-if="queue_hearteat.daemon.queue_status != 'green'" style="color: red;">[[queue_hearteat.daemon.msg]]</span></p>
                            </div>
                        </el-col>
                        <el-col :span="5">
                            <div class="" style="text-align: center;">
                                <p v-for="(item,index) in queue_color" :key="index">
                                    <el-button v-if="queue_hearteat.job.queue_status == item.color" :type="item.value" :title="item.label" icon="el-icon-s-opportunity" circle></el-button>
                                </p>
                                <p style="margin-top:32px;">队列 <span v-if="queue_hearteat.job.queue_status != 'green'" style="color: red">[[queue_hearteat.job.msg]]</span> <span v-if="queue_hearteat.job.is_repeat > 1" style="color: red">重复执行</span> </p>
                            </div>
                        </el-col>
                        <el-col :span="5">
                            <div class="" style="text-align: center;">
                                <p v-for="(item,index) in queue_color" :key="index">
                                    <el-button v-if="queue_hearteat.cron.queue_status == item.color" :type="item.value" :title="item.label" icon="el-icon-s-opportunity" circle></el-button>
                                </p>
                                <p style="margin-top:32px;"> 定时任务 <span v-if="queue_hearteat.cron.queue_status != 'green'" style="color: red">[[queue_hearteat.cron.msg]]</span> <span v-if="queue_hearteat.cron.is_repeat > 1" style="color: red">重复执行</span></p>
                            </div>
                        </el-col>
                        <el-col :span="5">
                            <div style="text-align: center;">
                                <p v-for="(item,index) in queue_color" :key="index">
                                    <el-button v-if="queue_hearteat.redis.queue_status == item.color" :type="item.value" :title="item.label" icon="el-icon-s-opportunity" circle></el-button>
                                </p>
                                <p style="margin-top:32px;"> Redis <span v-if="queue_hearteat.redis.queue_status != 'green'" style="color: red">[[queue_hearteat.redis.msg]]</span></p>
                            </div>
                        </el-col>
                    </el-row>
                </div>
            </el-col>
        </el-row>
        <div class="chart plugin" v-if="false">
            <div class="plugin-name plugin-title">常用功能</div>
            <div v-for="(item,index) in plugins" :key="index" class="plugin-box">
                <a :href="item.url" class="plugin-box-a">
                    <div class="plugin-icon">
                        <!-- <i class="fa" :class="item.icon" style="font-size:50px;margin-top:15px;"></i> -->
                        <img :src="item.icon_url" style="font-size:50px;width:80px;height:80px;">
                    </div>
                    <div class="plugin-name">
                        [[item.name]]
                    </div>
                </a>
=======
            </div>
            @if(YunShop::app()->role == 'founder')
                <div class="chart" v-if="status_show==1">
                    <div class="plugin-name plugin-title">状态</div>
                    <div>
                        <div class="status-li">
                            <div style="font-weight:600;line-height:36px;font-size:16px">负载状态</div>
                            <el-tooltip class="item" effect="dark">
                                <div slot="content" v-html="status_content"></div>
                                <el-progress type="circle" :percentage="status_rate"></el-progress>
                            </el-tooltip>
                        </div>
                        <div class="status-li">
                            <div style="font-weight:600;line-height:36px;font-size:16px">CPU使用率</div>
                            <el-tooltip class="item" effect="dark" :content="cpu?cpu.model:''">
                                <el-progress type="circle" :percentage="cpu?cpu.using:0"></el-progress>
                            </el-tooltip>
                            <div v-if="cpu" style="font-weight:600;line-height:36px;font-size:16px">[[cpu.num]]核心</div>
                        </div>
                        <div class="status-li">
                            <div style="font-weight:600;line-height:36px;font-size:16px">总内存使用率</div>
                            <el-progress type="circle" :percentage="ram?ram.memPercent:0"></el-progress>
                            <div v-if="ram" style="font-weight:600;line-height:36px;font-size:16px">[[ram.memUsed]]/[[ram.memTotal]]</div>
                        </div>
                        <div class="status-li">
                            <div style="font-weight:600;line-height:36px;font-size:16px">当前站点磁盘使用率</div>
                            <el-tooltip class="item" effect="dark">
                                <div slot="content" v-html="disk_content"></div>
                                <el-progress type="circle" :percentage="disk?disk.percent:0"></el-progress>
                            </el-tooltip>
                            <div v-if="disk" style="font-weight:600;line-height:36px;font-size:16px">[[disk.used]]G/[[disk.total]]G</div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="chart plugin">
                <div class="plugin-name plugin-title">常用功能</div>
                <div v-for="(item,index) in plugins" :key="index" class="plugin-box">
                    <a :href="item.url" class="plugin-box-a">
                        <div class="plugin-icon">
                            <!-- <i class="fa" :class="item.icon" style="font-size:50px;margin-top:15px;"></i> -->
                            <img :src="item.icon_url" style="font-size:50px;width:80px;height:80px;">
                        </div>
                        <div class="plugin-name">
                            [[item.name]]
                        </div>
                    </a>
                </div>
            </div>

            <div class="chart">
                <div class="plugin-name plugin-title">销量排行</div>
                <el-table :data="list" style="width: calc(100% - 30px);margin-left:15px;padding-top:30px;" v-loading="loading">
                    <el-table-column prop="title" label="商品名称" ></el-table-column>
                    <el-table-column prop="real_sales" label="销量" align="center"> </el-table-column>
                    <el-table-column prop="created_at" label="创建时间" align="center"> </el-table-column>
                </el-table>
            </div>
            <div class="example-item">
>>>>>>> tt-master
            </div>
        </div>
    </div>
</div>

<script>
    let orderChart = null;
    var app = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        name: 'test',
        data() {
            // let data = JSON.parse(`{!! $data ? : '{}'!!}`);

            return {
                queue_color: [{
                    color: 'green',
                    value: 'success',
                    label: '正常'
                }, {
                    color: 'yellow',
                    value: 'warning',
                    label: '延迟'
                }, {
                    color: 'red',
                    value: 'danger',
                    label: '阻塞'
                }, {
                    color: 'not_open',
                    value: '',
                    label: '无'
                }, {
                    color: 'unconnection',
                    value: 'danger',
                    label: '连接失败'
                }, {
                    color: 'unexecute',
                    value: 'danger',
                    label: '无法使用'
                }, {
                    color: 'uninstall',
                    value: 'danger',
                    label: '未安装'
                }, {
                    color: 'not_running',
                    value: 'danger',
                    label: '未运行'
                }],
                queue_hearteat: {
                    'daemon': {
                        queue_status: 'not_running',
                        is_repeat: 0,
                    },
                    'cron': {
                        queue_status: 'not_open',
                        is_repeat: 0,
                    },
                    'job': {
                        queue_status: 'not_open',
                        is_repeat: 0,
                    },
                    'redis': {
                        queue_status: 'not_open',
                        is_repeat: 0,
                    }
                },


                chart_data: [],
                plugins: [],
                list: [{}],
                today_order_money: 0, //今日交易额
                to_be_paid: 0, //待付款订单
                to_be_shipped: 0, //待发货订单
                today_order_count: 0, //今日订单数
                member_count: 0,
                loadAvg: [],
                status_content: '',
                status_rate: 0,
                cpu: {},
                ram: {},
                disk: {},
                disk_content: '',
                status_show: 0,
                loading: false,

                statistics: [{
                    title: "会员总数",
                    count: 0,
                    icon: "icon-fontclass-renshu",
                    color: "#2cc08d",
                    backgroundColor: "#e5f8f2"
                }, {
                    title: "今日订单数",
                    count: 0,
                    icon: "icon-fontclass-shangpindingdan",
                    color: "#f3766c",
                    backgroundColor: "#fdeeec"
                }, {
                    title: "待付款订单",
                    count: 0,
                    icon: "icon-ht_content_tixian",
                    color: " #ecb134",
                    backgroundColor: "#fcf9e9"
                }, {
                    title: "待发货订单",
                    count: 0,
                    icon: "icon-ht_content_order",
                    color: "#4d8bfc",
                    backgroundColor: "#f0f4ff"
                }, {
                    title: "今日交易额",
                    count: 0,
                    icon: "icon-fontclass-yue",
                    color: "#7e8bfa",
                    backgroundColor: "#eceefa"
                }],
                guide: [], //* 基础功能指引
                guideTabActiveIndex: "0",
                statisticsChartEnabled: false,
                entrance: {}, //* 入口链接
                withdrawalMore: "", //* 更多提现动态入口
                withdrawalList: [], //* 提现动态数据
                visitorEnabled: false, //* 是否开启浏览轨迹插件
                visitorUrl: "", //* 查看更多访问轨迹数据
                visitorData: [], //* 访问数据
            }
        },
        created() {

        },
        mounted() {
            this.getData();
            // let data = JSON.parse(`{!! $data ? : '{goods:[],order:{}}' !!}`);
            // this.setData(data);
            // let system_data = data.system || {};
            // this.status_show = (system_data && system_data.is_show) ? system_data.is_show : 0;
            // this.status_show = 1;
            // setTimeout(() => {
            //     this.status_content = `最近1分钟平均负载：${system_data.loadAvg?system_data.loadAvg[0]:''}<br>最近5分钟平均负载：${system_data.loadAvg?system_data.loadAvg[1]:''}<br>最近15分钟平均负载：${system_data.loadAvg?system_data.loadAvg[2]:''}`
            //     this.status_rate = system_data.loadAvg ? system_data.loadAvg[3] : 0;
            //     this.disk_content = `容量：${system_data.disk?system_data.disk.total:''}G<br>已用：${system_data.disk?system_data.disk.used:''}G<br>可用：${system_data.disk?system_data.disk.free:''}G<br>使用率：${system_data.disk?system_data.disk.percent:''}%`
            //     this.loadAvg = system_data.loadAvg;
            //     this.cpu = system_data.cpu;
            //     this.ram = system_data.RAM;
            //     this.disk = system_data.disk;
            // }, 500);

        },
        methods: {
            setData(data) {
                this.loading = true;

                this.queue_hearteat = data.queue_hearteat;
                this.chart_data = data.chart_data;
                this.plugins = data.plugins;
                this.list = data.goods;
                this.list.forEach((item, index) => {
                    item.title = this.escapeHTML(item.title)
                });
                this.today_order_money = Number(data.order.today_order_money);
                this.to_be_paid = data.order.to_be_paid;
                this.to_be_shipped = data.order.to_be_shipped;
                this.today_order_count = data.order.today_order_count;
                this.member_count = data.member_count;
                this.loading = false;

                this.guide = data.guide.list;
                this.statistics[0]['count'] = data.member_count;
                this.statistics[1]['count'] = data.order.today_order_count;
                this.statistics[2]['count'] = data.order.to_be_paid;
                this.statistics[3]['count'] = data.order.to_be_shipped;
                this.statistics[4]['count'] = data.order.today_order_money;

                this.statisticsChartEnabled = Boolean(data.is_enabled_statistics);
                if (this.statisticsChartEnabled) {
                    this.getRef();
                    window.addEventListener("resize", () => {
                        orderChart.resize();
                    })
                }

                this.entrance = data.entrance;

                this.withdrawalMore = data.withdrawal.url;
                let withdrawalList = [];
                for (let index = 0; index < data.withdrawal.list.length;) {
                    withdrawalList.push(data.withdrawal.list.slice(index, index + 6));
                    index += 6;
                }
                this.withdrawalList = withdrawalList;

                //* 访客播报
                this.visitorEnabled = data.visitor.is_enabled;
                let visitorData = [];
                for (let index = 0; index < data.visitor.list.length;) {
                    visitorData.push(data.visitor.list.slice(index, index + 6));
                    index += 6;
                }
                this.visitorData = visitorData;
                this.visitorUrl = data.visitor.url;
            },
            getData() {
                this.loading = true;
                this.$http.post("{!! yzWebFullUrl('survey.survey.survey') !!}").then(function(response) {
                    if (response.data.result) {
                        let data=response.data.data;
                        this.setData(data);
                        let system_data = data.system || {};
                        this.status_show = (system_data && system_data.is_show) ? system_data.is_show : 0;
                        this.status_show = 1;
                        setTimeout(() => {
                            this.status_content = `最近1分钟平均负载：${system_data.loadAvg?system_data.loadAvg[0]:''}<br>最近5分钟平均负载：${system_data.loadAvg?system_data.loadAvg[1]:''}<br>最近15分钟平均负载：${system_data.loadAvg?system_data.loadAvg[2]:''}`
                            this.status_rate = system_data.loadAvg ? system_data.loadAvg[3] : 0;
                            this.disk_content = `容量：${system_data.disk?system_data.disk.total:''}G<br>已用：${system_data.disk?system_data.disk.used:''}G<br>可用：${system_data.disk?system_data.disk.free:''}G<br>使用率：${system_data.disk?system_data.disk.percent:''}%`
                            this.loadAvg = system_data.loadAvg;
                            this.cpu = system_data.cpu;
                            this.ram = system_data.RAM;
                            this.disk = system_data.disk;
                        }, 500);
                    } else {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                    }
                    this.loading = false;
                }, function(response) {
                    this.$message({
                        message: response.data.msg,
                        type: 'error'
                    });
                    this.loading = false;
                });
            },
            getRef() {
                //指定图标的配置和数据 '总订单', '已完成', '已发货'

                var option = {
                    tooltip: {
                        trigger: 'axis',
                        formatter: "{b}<br/>{a0}：{c0}"
                    },
                    legend: {
                        right: 123,
                        data: ["总订单", "已完成", "已发货"]
                    },
                    xAxis: {
                        data: [this.chart_data[0].date, this.chart_data[1].date, this.chart_data[2].date, this.chart_data[3].date, this.chart_data[4].date, this.chart_data[5].date, this.chart_data[6].date],
                        axisPointer: {
                            show: true
                        },
                        axisLine: {
                            show: true,
                            lineStyle: {
                                width: 4,
                                color: "#ebeff3"
                            }
                        },
                        axisLabel: {
                            color: "#b1b5b8"
                        }
                    },
                    yAxis: {
                        splitLine: {
                            show: true,
                            lineStyle: {
                                type: 'dashed',
                                color: "#f0f3f6",
                                opacity: 1
                            }
                        }
                    },
                    series: [{
                            name: '总订单',
                            type: 'line',
                            data: [this.chart_data[0].total, this.chart_data[1].total, this.chart_data[2].total, this.chart_data[3].total, this.chart_data[4].total, this.chart_data[5].total, this.chart_data[6].total],
                            itemStyle: {
                                color: "rgba(239, 113, 113, 1)",
                                borderColor: "rgba(86, 86, 86, 1)",
                                borderWidth: 6
                            }
                        },
                        {
                            name: '已完成',
                            type: 'line',
                            data: [this.chart_data[0].complete, this.chart_data[1].complete, this.chart_data[2].complete, this.chart_data[3].complete, this.chart_data[4].complete, this.chart_data[5].complete, this.chart_data[6].complete],
                            itemStyle: {
                                color: "#f8a544"
                            }
                        },
                        {
                            name: '已发货',
                            type: 'line',
                            data: [this.chart_data[0].deliver_goods, this.chart_data[1].deliver_goods, this.chart_data[2].deliver_goods, this.chart_data[3].deliver_goods, this.chart_data[4].deliver_goods, this.chart_data[5].deliver_goods, this.chart_data[6].deliver_goods],
                            itemStyle: {
                                color: "#2cc08d"
                            }
                        },
                    ]
                };
                //初始化echarts实例
                // var myChart = echarts.init(document.getElementById('chartmain'));
                orderChart = echarts.init(this.$refs.chartmain);
                //使用制定的配置项和数据显示图表
                orderChart.setOption(option);
            },

            // 字符转义
            escapeHTML(a) {
                a = "" + a;
                return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");;
            },
            goToOrderStatisticsPage() {
                window.event.preventDefault();
                if (this.statisticsChartEnabled === false) {
                    this.$message.warning("请先安装并启用统计2.0插件，请联系管理员！");
                    return;
                }
                location.href = "{!! yzWebFullUrl('plugin.shop-statistics.backend.order.show') !!}";
            },
            goToGoodsStatisticsPage() {
                window.event.preventDefault();
                if (this.statisticsChartEnabled === false) {
                    this.$message.warning("请先安装并启用统计2.0插件，请联系管理员！");
                    return;
                }
                location.href = "{!! yzWebFullUrl('plugin.shop-statistics.backend.goods.show') !!}";
            },
            goToCustomerService() {
                if (this.entrance.is_enabled_customer_service == 0) {
                    this.$mesasge.warning("您未安装客服插件，请联系管理员");
                    window.event.preventDefault();
                }
            }
        },
        computed: {
            guideList() {
                if (this.guide[this.guideTabActiveIndex]) {
                    return this.guide[this.guideTabActiveIndex].list;
                }
                return [];
            }
        }
    })
</script>
@endsection
