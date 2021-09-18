@extends('layouts.base')

@section('content')

@section('title', trans('应用中心'))
<style>
    .main-panel>.content{
        padding-left: 0px;
    }
    .el-button+.el-button{
        margin-left: 0px;
    }
    .plugin-span{
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        /* max-width: 150px; */
        /* line-height: 1 !important; */
        line-height: normal !important;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        width: 100%;
    }
    .description{
        font-size: 13px;
        margin: 3px 0;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        word-break: break-all;
    }
    .icon_detail{
        display: flex;
        justify-content: flex-end;
    }
    .application-center{
        position: relative;
        top: 0;
    }
    .left-box{
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
    .left-box div{
        margin: 15px 0;
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        cursor: pointer;
    }
    .left-box .nav-detail{
        display: flex;
        flex-direction: row;
        padding: 5px 20px;
    }
    .left-box .nav-detail i{
        margin-right: 5px;
    }
    .rigth-box{
        position: relative;
        float: right;
        width: calc(100% - 148px);
        background: #eff3f6;
        padding-top: 10px;
        min-height: 100%;
    }
    .selected{
        background-color: #eff3f6;
        color: #42bea4;
    }
    .panel .panel-body{
        background: #eff3f6;
        display: flex;
        flex-wrap: wrap;
    }
    .panel-heading{
        background: #eff3f6 !important;
        border-bottom: 1px solid #eff3f6 !important;
    }
    .panel-title{
        display: flex;
        align-items: center;
        margin-left: 20px;
    }
    .nav-line{
        width: 3px;
        height: 18px;
        background: #42bea4;
        margin-right: 5px;
    }
    .panel-default .panel-body a{
        width: 100%;
    }
    @media (max-width: 991px) {
        .left-box {
            display: none;
        }
        .rigth-box {
            width: 100%;
        }
    }
</style>
<div class="w1200 m0a application-center">
    <script language="javascript" src="{{static_url('js/dist/nestable/jquery.nestable.js')}}"></script>
    <link rel="stylesheet" type="text/css" href="{{static_url('js/dist/nestable/nestable.css')}}" />

    <!--应用列表样式-->
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/plugins/list-icon/css/list-icon.css')}}">
    
    <!-- 左侧导航栏 -->
    <section class="left-box" id="plugins_list_left">
        @foreach( $class as $key1 => $value)
        @if(is_array($data[$key1]))
        <div class="nav-detail" >
            <i class="fa"></i><span>{{ $value['name'] }}</span>
        </div>
        @endif
        @endforeach
    </section>
    <div class="rigth-box">
        <!-- 新增加右侧顶部三级菜单 -->

        <div style="position: fixed; right: 20px; top: 60px;z-index:999">
            <input id="key" type="text" class="el-input__inner" style="width: 150px;height: 32px;line-height: 32px;" placeholder="请输入关键字" />
            <input type="button" class="el-button el-button--small" value="下一个" onclick="next()" />
            <input type="button" class="el-button el-button--small" value="上一个" onclick="previous()" />
        </div>
        <div class="row">
            @foreach( $class as $key1 => $value)
            @if(is_array($data[$key1]))
            <div class="panel panel-default">
                <div class="panel-heading" style="background-color: #f6f6f6">
                    <h3 class="panel-title">
                        <div class="nav-line"></div>
                        {{ $value['name'] }}
                    </h3>
                </div>
                <div class="panel-body">
                    @foreach($data[$key1] as $key => $plugin)
                    @if(can($key))
                    <div class=" col-lg-3 col-md-4 col-sm-4" style="display:flex; align-items: center;">
                        <a href="{{yzWebFullUrl($plugin['url']).'&'.$plugin['url_params']}}" class="plugin-a col-md-10 col-sm-12" style="display:flex;">
                            <div class="plugin-i-div" style="flex: 0 0 50px;">
                                <i class="plugin-i" style="background-color: {{$value['color']}}; background-image: url({{ $plugin['icon_url'] }})"></i>
                            </div>
                            <div class="plugin-span">
                                <div style="display: flex;">
                                    <span style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;max-width: 150px; display:block;">{{$plugin['name']}}</span>
                                    <object style="height:0px" style="flex: 0 0 20px;">
                                        <a class="top_show" style="display: none;position:relative;top:3px;padding-left:5px;" href="{{yzWebUrl('plugins.setTopShow',['name'=>$key,'action'=>(app('plugins')->isTopShow($key) ? 1 : 0)])}}">
                                            <i class="fa fa-tags" @if(app('plugins')->isTopShow($key))style="color: red" @endif
                                                data-toggle="tooltip" data-placement="top"
                                                @if(app('plugins')->isTopShow($key))title="取消顶部显示?" @else title="选择顶部显示?"@endif></i>
                                        </a>
                                    </object>
                                </div>
                                <div class="description">{{$plugin['description']}}</div>
                                <!-- <span class="description">{{$plugin['description']}}</span> -->
                                <span class="icon_detail">
                                    @if(in_array('wechat',$plugin['terminal']))  <i class="iconfont icon-all_wechat_public" style="margin-left: 10px;color: #0cb287;"></i> @endif</i>
                                    @if(in_array('min',$plugin['terminal']))  <i class="iconfont icon-all_smallprogram" style="margin-left: 10px;color: #52b459;"></i> @endif</i>
                                    @if(in_array('wap',$plugin['terminal']))  <i class="iconfont icon-ht_btn_wap" style="margin-left: 10px;color: #5632f4;"></i> @endif</i>
                                </span> 
                            </div>
                            {{--<span class="plugin-span-down">{{$plugin['description']}}</span>--}}
                        </a>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
            <div>
            </div>
        </div>

        <!-- <script src="{{static_url('js/pluginslist.js')}}"></script> -->
        <script src="{{resource_get('resources/views/admin/pluginslist.js')}}"></script>

        @endsection
        <style type="text/css">
            .res {
                color: Red;
            }

            .result {
                background: yellow;
            }
        </style>
        <script type="text/javascript">
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
                    if ($(document).scrollTop() >= $(ele).offset().top - 60){
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
        </script>
        <!-- 左侧导航栏 -->
        <script type="text/javascript">
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
        </script>