@extends('layouts.base')
@section('title', '推广中心设置')
@section('content')

<link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
<style>
    .text-color {
        color: #999999;
        font-size: 12px;
    }

    .nav {
        cursor: default;
        user-select: none;
    }

    .new {
        display: flex;
        width: 1000px;
    }

    .nav-btn {
        color: black;
        font-family: "微软雅黑";
        padding: 9px 13px 9px 13px;
        border-radius: 10px;
        margin-right: 10px;
        background-color: white;
        font-family: SourceHanSansCN-Regular;
        font-size: 14px;
        font-weight: normal;
        font-stretch: normal;
        letter-spacing: 2px;
        color: #333;
    }

    .nav-btn-back {
        color: #FFF;
        background-color: #29BA9C;
    }

    /* 颜色选项部分 */

    .el-color-picker__trigger {
        margin-top: 2px;
    }

    /* 表单加粗 */

    .el-form-item__label {
        font-weight: 600;
    }

    /* p文字标签通用 */
    .text-p {
        color: #999999;
        font-size: 12px;
        line-height: 5px;
        font-family: SourceHanSansCN-Regular;
        font-weight: normal;
        font-stretch: normal;
        letter-spacing: 0px;
    }

    .promotion_center {
        margin-top: 5px;
    }

    .h1_title {
        width: calc(100% - 400px);
        font-size: 15px;
        font-weight: 600;
        padding-bottom: 15px;
        border-bottom: 1px solid #ccc;
    }

    .industry_class {
        width: calc(100% - 350px)
    }

    .p-text-box {
        width: 90%;
        line-height: 18px;
        margin-top: 10px;
    }

    .p-text {
        font-size: 12px;
        font-weight: 600;
        opacity: .5;
    }

    /* 单选框列表 */

    .money-show-box {
        margin-left: 100px;
    }

    .money-show-box .el-form-item {
        padding-left: 20px;
        text-align: left;
    }

    [v-cloak] {
        display: none;
    }

    .amount_displayed_txt {
        margin-left: 92px;
        font-family: SourceHanSansCN-Regular;
        font-size: 15px;
        font-weight: normal;
        font-stretch: normal;
        letter-spacing: 1px;
        color: #29ba9c;
    }

    .mar_left_10 {
        margin-left: 10px;
    }
</style>

<div class="all">
    <div id="app" v-loading="loading" v-cloak>
        <!-- 头部导航 -->
        <div class="nav total-head">
            <ul class="new">
                <li @click="btnEvent(i)" class="nav-btn " :class="{'nav-btn-back': isIndex===i}" v-for='(item,i) in navlist' :key="i">[[item]]</li>
            </ul>
        </div>
        <div class="total-sect">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">基础设置</div>
            </div>
            <el-form label-width="200px" style="margin-top:25px;">
{{--                <!-- 颜色选项 -->--}}
{{--                <el-form-item label="主题色">--}}
{{--                    <div class="mar_left_10">--}}
{{--                        <el-color-picker v-model="active_color"></el-color-picker>--}}
{{--                        <p class="text-p">只支持推广中心模板1</p>--}}
{{--                    </div>--}}
{{--                </el-form-item>--}}
                <!-- 不显示插件 -->
                <el-form-item label="不显示插件">
                    <div class="promotion_center mar_left_10">
                        <el-checkbox-group v-model="promotion_center_list">
                            <el-checkbox label="extension">推广中心</el-checkbox>
                        </el-checkbox-group>
                    </div>
                    <!-- 插件列表多选框 -->
                    <div v-for="(title,i) in plugClassData " :key="i" class="industry_class_box mar_left_10">
                        <h1 class="h1_title">[[title.name]]</h1>
                        <div v-if="item!==null" v-for="(item,j) in plugcheckbox" :key="j" class="industry_class">
                            <el-checkbox-group v-model="plugcheckboxData">
                                <el-checkbox v-if="items.type==title.type" v-for="(items,k) in item" :key="k" :label="items.name"></el-checkbox>
                            </el-checkbox-group>
                        </div>
                    </div>
                    <div class="p-text-box mar_left_10">
                        <p class="p-text">勾选推广中心，则不显示也无法访问推广中心页面，会员中心不显示我的推广。 其他插件如果勾选，则推广不显示插件前端页面入口。</p>
                        <p class="p-text" style="line-height:5px;">勾选端口访问不显示的页面，将强制跳转到跳转页面。若是没有设置跳转链接，则默认跳到商城首页。</p>
                    </div>
                </el-form-item>
                <el-form-item label="H5跳转页面">
                    <el-input class="mar_left_10" clearable v-model="h5_link" style="width: 80%;" placeholder="请输入https开头链接"></el-input>
                </el-form-item>
                <!-- 小程序链接微信微信小程序才打开 -->
                <el-form-item v-if="this.isIndex===1" label="小程序链接">
                    <el-input class="mar_left_10" clearable v-model="applet_link" style="width: 80%;">
                        <template slot="append">
                            <el-button @click="showLink('mini','min_link_two')">选择链接</el-button>
                        </template>
                    </el-input>

                </el-form-item>
                <el-form-item label="更多权限显示">
                    <el-switch class="mar_left_10" active-value="1" inactive-value="0" v-model="more_switch" active-color="#29BA9C" inactive-color="#ccc">
                    </el-switch>
                </el-form-item>
                <el-form-item label="隐藏会员ID">
                    <el-switch class="mar_left_10" active-value="1" inactive-value="0" v-model="memberId_switch" active-color="#29BA9C" inactive-color="#ccc">
                    </el-switch>
                </el-form-item>
                <program :pro="pro" @replacepro="changeprogram" @addpro="parpro"></program>
            </el-form>
        </div>
        <div class="total-floo">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">插件显示设置</div>
            </div>
            <el-form label-width="10px" style="margin-top:45px">
                <p class="amount_displayed_txt">插件金额显示</p>
                <el-form-item>
                    <div class="money-show-box">
                        <el-form label-width="200px" style="margin-left:-130px;">
                            <el-form-item v-for="(item,i) in plugRadio" :label="item.name">
                                <div style="margin-left:10px">
                                    <el-radio-group v-model="item.status">
                                        <el-radio :label="0">累计金额</el-radio>
                                        <el-radio :label="1">剩余可提现金额</el-radio>
                                    </el-radio-group>
                                </div>
                            </el-form-item>
                        </el-form>
                    </div>
                </el-form-item>
            </el-form>
        </div>
        <div class="fixed total-floo">
            <div class="fixed_box">
                <el-form>
                    <el-form-item>
                        <el-button type="primary" @click="submit">提交</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
    <!-- 上传链接 -->
    @include('public.admin.program')
    <script>
        let vm = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data() {
                return {
                    // //选中的颜色
                    // active_color: '',
                    //推广中心
                    promotion_center_list: [],
                    //插件类
                    plugClassData: [],
                    //插件多选框
                    plugcheckbox: [],
                    plugcheckboxData: [],
                    //多选框插件集合
                    vue_route: [],
                    //插件单选框
                    plugRadio: [],
                    plugRadioDate: {},
                    //mark
                    plugin_mark: [],
                    //导航头部标签
                    navlist: ["微信公众号", "微信小程序", "WAP", "APP", "支付宝"],
                    //切换选项卡标识
                    isIndex: 0,
                    //h5_链接
                    h5_link: "",
                    //小程序链接
                    applet_link: "",
                    //开关按钮
                    more_switch: "0",
                    memberId_switch: "0",
                    //是否开启小程序弹窗
                    pro: false,
                    isCount: 0,
                    loading: true,
                    isplug: false,
                }
            },
            created() {
                //优化在不同设备固定定位挡住的现象设置父元素的内边距
                window.onload = function() {
                    let all = document.querySelector(".all");
                    let h = window.innerHeight * 0.03;
                    all.style.paddingBottom = h + "px";
                }
                let i = window.sessionStorage.getItem("i", this.isIndex);
                this.isIndex = Number(i);
                if (i == 1) {
                    this.postWeChatApplet();
                } else if (i == 2) {
                    this.postWap();
                } else if (i == 3) {
                    this.postApp()
                } else if (i == 4) {
                    this.postAlipayt()
                } else {
                    this.postWeChat();
                }
            },
            watch: {
                //推广中心
                "promotion_center_list": {
                    handler(val) {
                        this.multiple();
                    },
                    deep: true,
                    immediate: true
                },
                // 多选插件
                "plugcheckboxData": {
                    handler(val) {
                        this.multiple();
                    },
                    deep: true,
                    immediate: true
                },
                //单选
                "plugRadio": {
                    handler(val) {
                        val.forEach(item => {
                            this.plugRadioDate[item.value] = Number(item.status);
                        })
                    },
                    deep: true,
                    immediate: true
                }
            },
            methods: {
                //点击选项卡函数
                btnEvent(i) {
                    this.isIndex = i;
                    window.sessionStorage.setItem("i", this.isIndex);
                    if (this.isIndex == 0) {
                        this.postWeChat();
                    } else if (this.isIndex == 1) {
                        this.postWeChatApplet();
                    } else if (this.isIndex == 2) {
                        this.postWap();
                    } else if (this.isIndex == 3) {
                        this.postApp()
                    } else {
                        this.postAlipayt()
                    }
                },
                //请求微信公众号回显数据
                postWeChat(count) {
                    this.$http.post("{!!yzWebFullUrl('member.popularize-page-show.wechatSet')!!}", {
                        set: this.isCount == count ? this.argumentObj() : ""
                    }).then(res => {
                        this.loadingIcon(res.body, count);
                    })
                },
                //请求微信小程序回显数据
                postWeChatApplet(count) {
                    this.$http.post("{!!yzWebFullUrl('member.popularize-page-show.miniSet')!!}", {
                        set: this.isCount == count ? this.argumentObj() : ""
                    }).then(res => {
                        this.loadingIcon(res.body, count);
                    })
                },
                //请求wap回显数据
                postWap(count) {
                    this.$http.post("{!!yzWebFullUrl('member.popularize-page-show.wapSet')!!}", {
                        set: this.isCount == count ? this.argumentObj() : ""
                    }).then(res => {
                        this.loadingIcon(res.body, count);
                    })
                },

                //请求app回显数据
                postApp(count) {
                    this.$http.post("{!!yzWebFullUrl('member.popularize-page-show.appSet')!!}", {
                        set: this.isCount == count ? this.argumentObj() : ""
                    }).then(res => {
                        this.loadingIcon(res.body, count);
                    })
                },
                //请求支付宝回显数据
                postAlipayt(count) {
                    this.$http.post("{!!yzWebFullUrl('member.popularize-page-show.alipaySet')!!}", {
                        set: this.isCount == count ? this.argumentObj() : ""
                    }).then(res => {
                        this.loadingIcon(res.body, count);
                    })
                },
                // 参数
                argumentObj() {
                    return {
                        // background_color: this.active_color,
                        vue_route: this.vue_route,
                        plugin_mark: this.plugin_mark,
                        callback_url: this.h5_link,
                        small_extension_link: this.applet_link,
                        is_show_unable: this.more_switch,
                        show_member_id: this.memberId_switch,
                        plugin: this.plugRadioDate
                    }
                },
                //列表数据
                showinfo(res, count) {
                    console.log(res, 46545);
                    if (count) {
                        // 提交的时候
                        if (res.result == 1) {
                            this.$message.success('保存成功');
                            history.go(0);
                        } else {
                            this.$message.error(res.msg);
                        }
                        return;
                    }
                    if (this.isCount == 0) {
                        //初始化清空
                        this.promotion_center_list = [];
                        let {
                            // background_color,
                            extension,
                            plugin_type,
                            not_plugin,
                            callback_url,
                            small_extension_link,
                            is_show_unable,
                            show_member_id
                        } = res.data.basics;
                        if (res.data.basics !== null && JSON.stringify(res.data.basics) !== "{}") {
                            //主题颜色
                            // this.active_color = background_color
                            // 推广中心选中
                            if (extension == 1) {
                                this.promotion_center_list.push("extension")
                            }
                            //插件类名
                            //初始化清空
                            this.plugClassData = [];
                            this.plugcheckboxData = [];
                            if (not_plugin !== null && JSON.stringify(not_plugin) !== "{}") {
                                for (let i in not_plugin) {
                                    if (not_plugin[i] !== null) {
                                        //插件类
                                        this.plugClassData.push({
                                            type: i,
                                            name: plugin_type[i]
                                        })
                                        //插件多选框回显
                                        not_plugin[i].forEach(item => {
                                            if (item.status == 1) {
                                                this.plugcheckboxData.push(item.name)
                                            }
                                        })
                                    }
                                }
                            }
                            //插件多选框
                            this.plugcheckbox = not_plugin;
                            //H5跳转页面
                            this.h5_link = callback_url;
                            if (this.isIndex == 1) {
                                //小程序链接
                                this.applet_link = small_extension_link
                            }
                            //更多权限显示
                            this.more_switch = is_show_unable;
                            //会员ID
                            this.memberId_switch = show_member_id;
                        }
                        let {
                            plugin
                        } = res.data;
                        this.plugRadio=[];
                        if (plugin !== null && plugin.length !== 0) {
                            //插件单选
                            console.log(plugin);
                            for (let i in plugin) {
                                this.plugRadio.push({
                                    name: plugin[i].name,
                                    status: Number(plugin[i].status),
                                    value: plugin[i].value
                                });
                            }
                        }
                    }
                },
                //拼接多选框数据
                multiple() {
                    this.vue_route = [];
                    let itemObj = [];
                    for (let i in this.plugcheckbox) {
                        if (this.plugcheckbox[i] !== null) {
                            this.plugcheckbox[i].forEach(item => {
                                itemObj.push(item);
                            })
                        }
                    }
                    let itemName = [];
                    let plugin_markArr = [];
                    itemObj.forEach(item => {
                        //  console.log(item);
                        this.plugcheckboxData.forEach(name => {
                            if (item.name == name) {
                                plugin_markArr.push(item.mark)
                                itemName.push(item.url)
                            }
                        })
                    })
                    //拼接数据
                    this.vue_route = [...itemName, ...this.promotion_center_list];
                    this.plugin_mark = [...plugin_markArr];
                    // console.log(this.vue_route);
                },
                //请求响应反应
                loadingIcon(res, count) {
                    this.loading = true;
                    if (res.result == 1) {
                        setTimeout(() => {
                            this.loading = false;
                            this.showinfo(res, count);
                        }, 300)
                    }
                },
                //上传微信小程序链接
                showLink(type, name) {
                    // console.log(type, name);
                    if (type == "link") {
                        this.chooseLink = name;
                        this.show = true;
                    } else {
                        this.chooseMiniLink = name;
                        this.pro = true;
                    }
                },
                //小程序链接
                changeprogram(item) {
                    this.pro = item;
                },
                //小程序插件弹窗显示与隐藏的控制
                changeLink(item) {
                    this.show = item;
                },
                //绑定小程序链接
                parpro(child, confirm) {
                    this.pro = confirm;
                    this.applet_link = child;
                },
                //提交
                submit() {
                    //判断对应保存数据
                    if (this.isIndex == 0) {
                        this.isCount = 1;
                        this.postWeChat(1);
                    } else if (this.isIndex == 1) {
                        this.isCount = 2;
                        this.postWeChatApplet(2)
                    } else if (this.isIndex == 2) {
                        this.isCount = 3;
                        this.postWap(3);
                    } else if (this.isIndex == 3) {
                        this.isCount = 4;
                        this.postApp(4);
                    } else {
                        this.isCount = 5;
                        this.postAlipayt(5);
                    }
                }
            }
        })
    </script>
    @endsection
