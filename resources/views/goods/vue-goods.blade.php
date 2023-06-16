@extends('layouts.base')

@section('content')
@section('title', trans('商品详情'))
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css?time='.time())}}"/>
<!-- 引入样式 -->
<!-- 引入组件库 -->
<script src="{{static_url('../resources/views/goods/assets/js/elementui@2.15.6.js?time='.time())}}"></script>
<link rel="stylesheet" href="{{static_url('../resources/views/goods/assets/css/common.css?time='.time())}}">
<link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
<style>
    .tabs {
        display: flex;
        flex-wrap: wrap;
        margin-top: 15px;
    }

    .tab-item {
        padding: 5px 20px;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        cursor: pointer;
    }

    .tab-item:hover {
        color: #29BA9C;
    }

    .tab-item.active {
        color: #29BA9C;
    }

    .tab-item::after {
        content: '';
        display: block;
        width: 100%;
        height: 2px;
        background-color: #ffffff;
        margin-top: 5px;
    }

    .tab-item.active::after {
        content: '';
        display: block;
        width: 100%;
        height: 2px;
        background-color: #29BA9C;
        margin-top: 5px;
    }
</style>

<div class="all">
    <div id="app">
        <div class="vue-head goods-page_header">
            <div class="goods-page_header-buttons">
                <el-button type="text" :class="currentShowPage==pageItem.key?'goods-page_header-current-button':''"
                           v-for="pageItem in pages" :key="pageItem.key" @click="currentShowPage=pageItem.key">[[
                    pageItem.title ]]
                </el-button>
            </div>
            <div class="tabs">
                <!-- 韦总说要换行显示 -->
                <div v-for="subPageItem in subPages" :key="subPageItem.name" class="tab-item"
                     @click.stop="chooseTab(subPageItem)"
                     :class="{'active': showComponentName == subPageItem.componentName}">
                    [[subPageItem.title]]
                </div>
            </div>
            <!-- <el-tabs v-model="showComponentName">
              <el-tab-pane :label="subPageItem.title" :name="subPageItem.componentName" v-for="subPageItem in subPages" ></el-tab-pane>
            </el-tabs> -->
        </div>
        <main class="goods-page_main vue-main">
            <ul v-if="componentLoaded">
                <li v-for="pageArray in pages" :key="pageArray.key">
                    <ul>
                        <li v-for="page in pageArray.childrens" :key="page.path">
                            <component :ref="page.componentName" :is="page.componentName" :http_url="http_url"
                                       :form="page['data']" :attr_hide="page['attr_hide']"
                                       v-if="$options.components[page.componentName]"
                                       v-show="showComponentName==page.componentName"></component>
                        </li>
                    </ul>
                </li>
            </ul>

            <div class="vue-page">
                <el-row>
                    <el-col align="center">
                        <el-button type="primary" @click="save">保存</el-button>
                    </el-col>
                </el-row>
            </div>
        </main>
    </div>
</div>

<script>
    let title_hide_dom = document.getElementsByClassName('vue-head');
    title_hide_dom[0].style.display = 'none';// 隐藏选择的元素
</script>
<script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>
<script src="{{resource_get('resources/views/goods/assets/js/vueDraggable/sortable.js')}}"></script>
<script src="{{resource_get('resources/views/goods/assets/js/vueDraggable/vuedraggable.js')}}"></script>
@include('public.admin.tinymceee')
@include('public.admin.uploadMultimediaImg')
@include('public.admin.pop')
@include('public.admin.program')
@include('public.admin.new-poster-introduce')
<script>
    const GoodsPageAssetsUrl = "{{static_url('../resources/views/goods/assets')}}"; //* 商品页静态地址
    const GetGoodsDataUrl = "{!! $widget_url !!}"; //* 获取商品数据地址
    const SaveGoodsDataUrl = "{!! $store_url !!}"; //* 保存数据地址
    const GoodsList = "{!! $success_url !!}"; //* 保存数据地址
    const httpUrl = "{!!  request()->getSchemeAndHttpHost().yzUrl('') !!}"; //* 保存数据地址
    const CktUrl = "{!!  $ckt_url !!}"; //* 创客贴url
    const IsDecorate = "{!!  $is_decorate !!}"; //* 装修插件开关状态
    const goods_id = "{{ request()-> id }}";
    const is_update_price = "{!! $is_update_price !!}"; //是否可以改价 2不可更改

    let readonly = false;
    if(is_update_price == 2){
        readonly = true
    }
</script>
<script src="{{static_url('../resources/views/goods/assets/js/main.js?time='.time())}}"></script>
@endsection
