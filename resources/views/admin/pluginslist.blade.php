@extends('layouts.base')

@section('content')

@section('title', trans('应用中心'))
<style>
    .main-panel {
        padding: 20px 0px 20px 30px;
        background-color: #EFF3F6;
    }

    .main-panel>.content {
        padding-left: 0px;
        padding-right: 32px;
    }

    .el-button+.el-button {
        margin-left: 0px;
    }

    .application-center {
        position: relative;
        top: 0;
    }

    .left-box {
        position: fixed;
        top: 30px;
        bottom: 0;
        left: 96px;
        width: 150px;
        padding-top: 10px;
        font-size: 14px;
        height: 100%;
        border-right: 1px solid #e0e0e0;
        color: #464646;
        font-weight: bold;
        z-index: 100;
        background: #fff;
    }

    .left-box div {
        margin: 15px 0;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
    }

    .left-box .nav-detail {
        display: flex;
        flex-direction: row;
        padding: 5px 20px;
    }

    .left-box .nav-detail i {
        margin-right: 5px;
    }

    .rigth-box {
        position: relative;
        float: right;
        width: calc(100% - 148px);
        background: #eff3f6;
        padding-top: 10px;
        min-height: 100%;
    }
    .panel .panel-body {
        background: #eff3f6;
        display: flex;
        flex-wrap: wrap;
    }
    @media (max-width: 991px) {
        .left-box {
            display: none;
        }

        .rigth-box {
            width: 100%;
        }
    }

    .top {
        display: flex;
        margin-bottom: 18px;
    }

    .item {
        color: #707070;
        width: 100px;
        height: 38px;
        line-height: 38px;
        text-align: center;
        cursor: pointer;
        font-size: 16px;
    }

    .activeItem {
        color: #353535;
        font-size: 18px;
        font-weight: bold;
        background: #DBE1E6;
        border-radius: 8px 8px 8px 8px;
    }

    .el-input__inner {
        height: 32px;
    }

    .el-input__prefix,
    .el-input__suffix {
        top: -3px;
    }

    .el-input {
        top: 2px;
    }

    .plugin-list {
        display: flex;
        flex-wrap: wrap;
        padding: 18px 0 6px 0
    }

    .plugin-list-item {
        width: calc((100% - 120px) / 6);
        height: 80px;
        border-radius: 12px;
        background-color: #ffffff;
        margin-right: 24px;
        margin-bottom: 24px;
        box-shadow: 0px 8px 16px 1px #DBE4EB;
        cursor: pointer;
    }
    .plugin-list-item:nth-child(6n){
        margin-right: 0;
    }

    .plugin-list-top {
        background-color: #F9F9F9;
        border-radius: 0px 12px 0px 12px;
        position: absolute;
        right: 0;
        display: flex;
        justify-content: space-around;
    }

    .plugin-list-item:hover {
        box-shadow: 0px 14px 26px -12px rgba(34, 133, 233, 0.42), 0px 4px 23px 0px rgba(0, 0, 0, 0.12), 0px 8px 10px -5px rgba(38, 130, 233, 0.2);

    }

    .topping {
        display: none;
    }

    .plugin-list-item:hover .topping {
        display: block;
    }
    ::-webkit-scrollbar {
        display: none;
    }
    a:hover  {
       color:#707070; 
       text-decoration:none; 
       cursor:pointer;  
    }
    a { color: inherit; } 

    .installed_list{
        margin:0 0 12px 0;
        max-height:220px;
    }
     .el-scrollbar .el-scrollbar__wrap{
		max-height: 220px; 
		overflow-x: hidden;
}
    .el-popover{
       top: 40px;
    }
    .plugin_class_name{
        font-weight: bold;
        font-size: 18px;
        color: #353535;
    }
</style>
<div class="w1200 m0a application-center">
    <script language="javascript" src="{{static_url('js/dist/nestable/jquery.nestable.js')}}"></script>
    <link rel="stylesheet" type="text/css" href="{{static_url('js/dist/nestable/nestable.css')}}" />

    <!--应用列表样式-->
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/plugins/list-icon/css/list-icon.css')}}">


    <script src="{{resource_get('resources/views/admin/pluginslist.js?time='.time())}}"></script>
    <div id="plugins">
        <!-- <template>
        </template> -->
        <div class="plugins_content">
            <div class="top">
                <div :class="{item:true,activeItem:active_item == -1}" @click="chooseCategory(-1)">全部应用</div>
                <div :class="{item:true,activeItem:active_item == index}" v-for="(item,index,keys) in item_show_list" :key="index" @click="chooseCategory(index)">[[item.name]]</div>
             <el-popover placement="bottom-end" width="300" trigger="manual"  v-model="visiblepop" >
                    <div style="font-size:12px;color:#8C8C8C" v-if="installed_list.length > 0">已安装应用  共[[installed_list.length]]个 点击进入插件!</div>
                <div  class="installed_list" v-if="installed_list.length > 0">
                    <el-scrollbar >
                     <div v-for="(item,index) in installed_list" :key="index" style="position: relative;cursor: pointer;margin-top:12px" @click="toPlugin(item.jump_url)">
                            <img :src="item.imageUrl" alt="" style="width: 32px;height:32px;border-radius: 8px ;">
                            <span style="margin-left: 6px;font-size:12px;font-weight: bold;color:#353535">[[item.title]]</span>
                         </div>
                    </el-scrollbar>
                </div>
                    <div style="font-size:12px;color:#8C8C8C" v-if="has_founder && not_installed_list.length > 0">更多应用</div>
                <div style="margin:12px 0px;position: relative;display:flex" v-for="(item,index) in not_installed_list" :key="index" v-if="has_founder">
                    <img :src="item.imageUrl" alt="" style="width: 32px;height:32px;border-radius: 8px ;">
                    <div style="margin-left: 6px;">
                    <span style="font-size:12px;font-weight: bold;color:#353535">[[item.title]]</span>
                    <div style="font-size: 11px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;word-break: break-all;width: 150px;">[[item.description]]</div>
                    </div>
                    <el-button style="border-radius:4px;background: #29BA9C;color:#ffffff;right: 0px;position: absolute;top: 5px;" size="mini" @click="install(item)">安装</el-button>
                </div>
                    <div style="color: #29BA94;font-size:13px;cursor: pointer;width:100px" @click="installApp" v-if="has_founder && not_installed_list.length > 0">搜索更多应用</div>
                    <el-input slot="reference" placeholder="输入关键词，按回车搜索 " style="width: 220px;height: 32px !important;margin-left:32px" v-model="search_word" @input="search" @blur="searchBlur" @focus="focus" ref="search" >
                        <i slot="prefix" class="el-input__icon el-icon-search"></i>
                    </el-input>
            </el-popover>
                <el-button style="background-color:#29ba9c;color:#ffffff;position: absolute;border-radius: 8px;right: 0px;" @click="installApp" v-if="has_founder">安装应用</el-button>
            </div>
                <div  v-for="(class_item,index,key) in plugin_show_list " :key="index" class="plugin" >
                     <div v-if="is_show_class" class="plugin_class_name">
                        [[  className(class_item)  ]]
                     </div>
             <div class="plugin-list">
                <div v-for="(item,index,keys) in class_item" :key="index" class="plugin-list-item" @click="toPlugin(item.url,item.url_params)">
                    <a  :href="item.url">
                    <div style="position: relative">
                        <div class="plugin-list-top" style="color:#6B7285">
                            <i v-if=" item.terminal?item.terminal.find( (item) => { return item == 'wechat' }  ):'' " class="iconfont icon-ht_btn_gongzhonghao" style="font-size: 13px;margin-right:5px"></i>
                            <i v-if=" item.terminal?item.terminal.find( (item) => { return item == 'wap' }  ):'' " class="iconfont icon-a-2" style="font-size: 13px;margin-right:5px"></i>
                            <i v-if=" item.terminal?item.terminal.find( (item) => { return item == 'min' }  ):'' " class="iconfont icon-ht_btn_smallprogram" style="font-size: 13px;margin-right:5px"></i>
                        </div>
                    </div>
                    <div style="padding: 20px 16px;display:flex">
                    <div><img :src="item.icon_url" alt="" style="width: 40px;height:40px;border-radius: 12px;position: relative" ></div>
                        <div style="margin-left: 10px;width: calc(100% - 48px);">
                            <div style="font-size: 15px;color:#353535;font-weight:bold">
                            <span style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;max-width: 150px;display: block;">[[item.name]]</span>
                            </div>
                            <div style="font-size:12px;white-space: nowrap;text-overflow: ellipsis;overflow: hidden;word-break: break-all;color:#999999">[[item.description]]</div>
                        </div>
                    </div>
                    </a>
                    <div style="position: relative" class="topping" >
                        <div style="position: absolute;padding: 2px 5px;background: rgb(41, 186, 156,0.1);border-radius: 12px 0px;color: #29BA94;line-height: 16px;right:0px;bottom:1px" @click.stop="setTopping(item,index,keys)">
                            <i class="iconfont icon-fontclass-zhiding" style="font-size:11px"></i>
                            <span style="font-size:11px" v-if="!item.top_show">置顶</span>
                            <span style="font-size:11px" v-else>取消置顶</span>
                        </div>
                    </div>
                </div>    
             </div>                
                </div> 
        </div>

    </div>
    <script>
        var vm = new Vue({
            el: "#plugins",
            delimiters: ['[[', ']]'],

            data() {
                return {
                    value: '',
                    item_list: {},
                    active_item: -1,
                    show_list: {},
                    item_show_list: {},
                    plugin_show_list: {},
                    search_word: '',
                    timer:null,
                    installed_list:[],
                    not_installed_list:[],
                    visiblepop:false,
                    has_founder:false,
                    is_show_class:true
                }
            },
            created() {
                this.getCategory()
            },
            mounted() {
            },
            methods: {
                //选择插件类型
                chooseCategory(index) {
                    this.active_item = index
                    if(index == -1){
                        this.is_show_class =true
                        this.plugin_show_list = this.show_list
                    }else{
                        this.is_show_class = false
                        this.plugin_show_list = {}
                        this.plugin_show_list[index] = this.show_list[index]
                    }
                },
                //获取插件列表
                getCategory() {
                    this.$http.post('{!! yzWebFullUrl('plugins.get-plugin-list') !!}', {}).then(function(response) {
                        if (response.data.result) {
                            this.item_list = response.data.data.class
                            this.show_list = response.data.data.data
                            this.plugin_show_list = response.data.data.data
                            this.has_founder = response.data.data.has_founder
                            for (const key in this.item_list) {
                                for (const keys in this.show_list) {
                                    if (key == keys) {
                                        this.$set(this.item_show_list, keys, this.item_list[key])
                                    }
                                }
                            }
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }
                    }, function(response) {
                        console.log(response);
                    });
                },
                //跳转相对应插件页面
                toPlugin(urls,url_params) {
                    if(urls){
                    window.location.href = urls
                    }else{
                        this.$message({
                                message: '该插件未开启',
                                type: 'error'
                            });
                    }
                },
                //插件置顶
                setTopping(item,keys) {                 
                    this.$http.post('{!! yzWebFullUrl('plugins.setTopShow') !!}', {
                            action: item.top_show,
                            name: keys
                        }).then(function(response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                            let url = "{!! yzWebUrl('plugins.get-plugin-list') !!}"
                            window.location.href = url
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }
                    }, function(response) {
                        console.log(response);
                    });
                },
                //跳转安装页面
                installApp() {
                    let url = "{!! yzWebUrl('plugins.jump') !!}"
                    window.location.href = url
                },
                searchApp() {
                    this.$http.post('{!! yzWebFullUrl('plugins.search-plugin-list') !!}', {
                            keyword: this.search_word
                        }).then(function(response) {
                        if (response.data.result) {
                              this.installed_list  = response.data.data.installed_list
                              this.not_installed_list = response.data.data.not_installed_list
                              if(!this.has_founder){
                                this.not_installed_list = []
                              }
                              if(this.installed_list.length !== 0 || this.not_installed_list.length !==0){
                                        this.visiblepop = true
                              }else{
                                       this.visiblepop = false
                              }
                              window.dispatchEvent(new Event('resize'))
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }
                    }, function(response) {
                        console.log(response);
                    });
                },
                //搜索防抖
                _debounce(fn, delay) {
                   var delay = delay || 200;
                  return function () {
                  var th = this;
                   var args = arguments;
                   if (this.timer) {
                    clearTimeout(timer);
                  }
                 this.timer = setTimeout(function () {
                  this.timer = null;
                    fn.apply(th, args);
                }, delay);
                };
                },
                //关键字搜索插件
                search(){
                 this._debounce(this.searchApp,500)()
                 },
                //安装应用
                install(item){
                    this.$refs.search.focus()
                    this.$http.post('{!! yzWebFullUrl('plugins.install-plugin') !!}', {
                            version: item.version,
                            name: item.name
                        }).then(function(response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }
                        this.search()
                        this.getCategory()
                    }, function(response) {
                        console.log(response);
                    });                    
                },
                searchBlur(){
                    this.visiblepop = false
                },
                //点击搜索聚焦 判断是否展示弹出框
                focus(){
                    if(this.installed_list.length !== 0 || this.not_installed_list.length !==0){
                                        this.visiblepop = true
                            }                    
                },
                className(class_item){
                   return this.item_list[Object.values(class_item)[0].type].name
                }
            },
            computed:{}
        });
    </script>
    @endsection
    <!-- <style type="text/css">
            .res {
                color: Red;
            }

            .result {
                background: yellow;
            }
        </style> -->
    <!-- <script type="text/javascript">
            var oldKey = "";
            var index = -1;
            var pos = new Array(); //用于记录每个关键词的位置，以方便跳转
            var oldCount = 0; //记录搜索到的所有关键词总数

            // 先获取全部再添类名
            function checkIcon(str) {
                switch (str) {
                    case '入口类' :
                        return 'fa-star';
                    case '行业类' :
                        return 'fa-sitemap';
                    case '营销类' :
                        return 'fa-money';
                    case '企业管理类' :
                        return 'fa-suitcase';
                    case '工具类' :
                        return 'fa-tags';
                    case '门店应用类' :
                        return 'fa-truck';
                    case '区块链类' :
                        return 'fa-chain';
                    default :
                        return 'fa-cubes';
                }
            }

            function checkScroll() {
                // 默认是第一个
                $('.nav-detail').eq(0).addClass('selected');
                // 监听滚动添加选中
                $('.panel-title').each(function (i,ele) {
                    if ($(document).scrollTop() >= $(ele).offset().top - 100){
                        $('.nav-detail').eq(i).addClass('selected').siblings().removeClass('selected')
                    }
                })
            }

            function previous() {
                index--;
                index = index < 0 ? oldCount - 1 : index;
                search();
            }

            function next() {
                ++index;
                //index = index == oldCount ? 0 : index;
                if (index == oldCount) {
                    index = 0;
                }
                search();
            }

            function search() {
                $(".result").removeClass("res"); //去除原本的res样式
                var key = $("#key").val(); //取key值
                if (!key) {
                    // console.log("key为空则退出");
                    $(".result").each(function() { //恢复原始数据
                        $(this).replaceWith($(this).html());
                    });
                    oldKey = "";
                    return; //key为空则退出
                }
                if (oldKey != key) {
                    // console.log("进入重置方法");
                    //重置
                    index = 0;
                    $(".result").each(function() {
                        $(this).replaceWith($(this).html());
                    });
                    pos = new Array();
                    var regExp = new RegExp(key + '(?!([^<]+)?>)', 'ig'); //正则表达式匹配
                    $(".row").html($(".row").html().replace(regExp, "<span id='result" + index + "' class='result'>" + key + "</span>")); // 高亮操作
                    $("#key").val(key);
                    oldKey = key;
                    $(".result").each(function() {
                        pos.push($(this).offset().top);
                    });
                    oldCount = $(".result").length;
                    // console.log("oldCount值：", oldCount);
                }

                $(".result:eq(" + index + ")").addClass("res"); //当前位置关键词改为红色字体

                // $("body").scrollTop(pos[index]);//跳转到指定位置
                window.scrollTo(0, pos[index] - 60);
                // console.log(pos[index]);
                // console.log(index);
            }
        </script> -->
    <!-- 左侧导航栏 -->
    <!-- <script type="text/javascript">
            window.onload = function() {
                // 循环添加图标
                for(let index=0; index<$('.nav-detail').length; index++) {
                    $('.nav-detail').eq(index).children("i").addClass(checkIcon($('.nav-detail').eq(index).children("span").text()));
                }
                checkScroll();
                let flag = true
                // 移动滚动条，使左侧导航有相应的变化
                $(document).scroll(function (e) {
                    if (flag){
                        checkScroll();
                    }
                });
                
                // 点击左侧导肮，滑到对应的元素上
                let {prevObject} = $(".nav-detail").find("*")
                let dataList = []
                for(let i = 0;i<prevObject.length;i++){
                    dataList.push(prevObject[i].innerText)
                }
                $(".nav-detail").bind("click",function(data){
                    flag = false
                    $(this).siblings('div').removeClass('selected');  // 删除其他兄弟元素的样式
                    $(this).addClass('selected'); 
                    let name = data.currentTarget.innerText //获取左侧导航的name
                    let index = dataList.indexOf(name)     //获取左侧导航的下标值
                    $('html,body').animate({ scrollTop: $(".panel-title").eq(index).offset().top - 60}, ()=>{
                        flag = true
                    })
                });
                
                // 点击回车
                $('#key').keyup(function(e){
                    if(e.keyCode===13){
                        next()
                    }
                })
            }
        </script> -->