<!--
{{--<el-tabs id='re_tab'  v-model="activeName" style="padding-left: 10px;margin-bottom:10px;" @tab-click="">--}}
    {{--@foreach(\app\backend\modules\menu\Menu::current()->getItems()[\app\backend\modules\menu\Menu::current()->getCurrentItems()[0]]['child'][\app\backend\modules\menu\Menu::current()->getCurrentItems()[1]]['child'] as $key=>$value)--}}
        {{--@if($value['menu'] == 1)--}}
       {{--// <li @if(\YunShop::request()->route == $value['url'] || \YunShop::request()->route . '.index' == $value['url']) class="active" @endif>--}}
            {{--<el-tab-pane label="{{$value['name']}}" name="{{$value['url']}}"></el-tab-pane>--}}
        {{--</li>--}}
        {{--@endif--}}
    {{--@endforeach--}}
{{--</el-tabs>--}}

<el-tabs  v-model="activeName" style="padding-left: 10px;margin-bottom:10px;background-color:#fff;border-radius:8px;" >
    @foreach(\app\backend\modules\menu\Menu::current()->getItems()[\app\backend\modules\menu\Menu::current()->getCurrentItems()[0]]['child'][\app\backend\modules\menu\Menu::current()->getCurrentItems()[1]]['child'] as $key=>$value)
        @if($value['menu'] == 1)
            <el-tab-pane label="{{$value['name']}}" name="{{$value['url']}}"></el-tab-pane>
            {{--<el-tab-pane label="{{$value['name']}}" name="{{$value['url']}}"></el-tab-pane>--}}
        @endif
    @endforeach
</el-tabs>

{{--<el-tabs v-model="activeName" style="padding-left:10px;margin-bottom:10px;background-color:#fff;border-radius:8px; ">
    <el-tab-pane label="商城设置" name="one"><a></a></el-tab-pane>
    <el-tab-pane label="会员设置" name="two"><a></a></el-tab-pane>
    <el-tab-pane label="会员资料" name="three"><a></a></el-tab-pane>
    <el-tab-pane label="注册设置" name="four"><a></a></el-tab-pane>
    <el-tab-pane label="订单设置" name="five"><a></a></el-tab-pane>
    <el-tab-pane label="物流查询" name="six"><a></a></el-tab-pane>
    <el-tab-pane label="短信" name="seven"><a></a></el-tab-pane>
    <el-tab-pane label="联系方式" name="eight"><a></a></el-tab-pane>
    <el-tab-pane label="分类" name="nine"><a></a></el-tab-pane>
    <el-tab-pane label="优惠券" name="ten"><a></a></el-tab-pane>
    <el-tab-pane label="分享" name="eleven"><a></a></el-tab-pane>
    <el-tab-pane label="幻灯片" name="twelve"><a></a></el-tab-pane>
    <el-tab-pane label="广告位" name="thirdteen"><a></a></el-tab-pane>
</el-tabs>--}} -->
<div class="panel panel-info">
    <ul class="add-shopnav">

        @foreach(\app\backend\modules\menu\Menu::current()->getItems()[\app\backend\modules\menu\Menu::current()->getCurrentItems()[0]]['child'][\app\backend\modules\menu\Menu::current()->getCurrentItems()[1]]['child'] as $key=>$value)
            @if(isset($value['menu']) && $value['menu'] == 1 && $value['can'])
                @if(isset($value['child']) && array_child_kv_exists($value['child'],'menu',1))
                    <li @if(\YunShop::request()->route == $value['url'] || \YunShop::request()->route . '.index' == $value['url']) class="active" @endif>
                        <a href="{{isset($value['url']) ? yzWebFullUrl($value['url']):''}}{{$value['url_params'] ?? ''}}">
                            {{$value['name']}}
                        </a>
                    </li>
                @elseif($value['menu'] == 1)
                    <li @if(\YunShop::request()->route == $value['url'] || \YunShop::request()->route . '.index' == $value['url']) class="active" @endif>
                        <a href="{{isset($value['url']) ? yzWebFullUrl($value['url']):''}}{{$value['url_params'] ?? ''}}">
                            {{$value['name']}}
                        </a>
                    </li>
                @endif
            @endif
        @endforeach
    </ul>
</div>

