<!-- sidebar: style can be found in sidebar.less -->
<!-- <section  class="sidebar" data-active-color="blue" style="background:#23232f" data-image="../assets/img/sidebar-1.jpg"> -->
<style>
    .sidebar-wrapper {
        position: relative;
    }
    .first-nav::-webkit-scrollbar {
        width:4px;
        height:4px;
        background-color:transparent;
    }
    .first-nav::-webkit-scrollbar-thumb {
        background-color:#111;
    }
    .second-nav {
        margin-top:60px!important;
    }
    .last-nav {
        position:absolute;
        bottom:0;
        left:0;
        width:100%;
    }
</style>
<section class="sidebar" data-image="../assets/img/sidebar-1.jpg">
    <div class="sidebar-wrapper">
        <div style="width:96px;text-align:center;height:80px;">
            <img src="{{\Setting::get('shop.shop.logo') ? yz_tomedia(\Setting::get('shop.shop.logo')) : resource_absolute('static/assets/img/default-avatar.png')}}"
                 style="border-radius:50%;width:62px;height:62px;margin:10px auto;background-color:white;border-radius:50%;"
                 alt="">
        </div>
        {{--upload_image_local().\Setting::get('shop.shop.logo')--}}
        <ul class="nav first-nav">
            @if(in_array(\YunShop::app()->role,['founder','manager','owner']))
                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                    <a href="{{yzWebUrl('survey.survey.index')}}">
                        <i class="fa fa-archive"></i>
                        <span style=" margin-top: -5px;font-size:14px !important">概况</span>
                    </a>
                </li>
            @else
                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                    <a href="{{yzWebUrl('index.index')}}">
                        <i class="fa fa-home"></i>
                        <span style=" margin-top: -5px;font-size:14px !important">商城</span>
                    </a>
                </li>
            @endif
            @php
                $menu = \app\backend\modules\menu\Menu::current()->getItems();
                $sort_menu = \app\common\helpers\ArrayHelper::arraySort($menu, 'sort');
                $top = [];
                $middle = [];
                $bottom = [];
                $top_name = ['index','Goods','Member','Order','finance','system','system_msg'];
                foreach ($sort_menu as $k => $v){
                    if(in_array($k,$top_name)){
                        $top[$k] = $v;
                        continue;
                    }
                    if($k == 'system_msg'){
                        $bottom = $v;
                        continue;
                    }
                    $middle[$k] = $v;
                }
                unset($sort_menu,$menu);
            @endphp

            {{--顶部主要菜单--}}
            @foreach($top as $key=>$value)
                @if(isset($value['menu']) && $value['menu'] == 1 && can($key) && $value['left_first_show'] == 1)
                    @if(isset($value['child']) && array_child_kv_exists($value['child'],'menu',1))
                        <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                            <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                {{--<a href="{{isset($value['url']) ? yzWebFullUrl($value['url']):''}}{{$value['url_params'] or ''}}">--}}
                                <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                {{--<span class="pull-right-container">--}}
                                {{--<i class="fa fa-angle-left pull-right"></i>--}}
                                {{--</span>--}}
                                <span style=" margin-top: -5px;font-size:14px !important">{{$value['name']}}</span>
                                @if($value['name'] == '消息')
                                    @if(app\common\models\systemMsg\SysMsgLog::getLogCount() > 0)
                                        <span class="badge"
                                              style="background-color:#F15353;position: absolute">{{app\common\models\systemMsg\SysMsgLog::getLogCount()<99?app\common\models\systemMsg\SysMsgLog::getLogCount():'...'}}</span>
                                    @endif
                                @endif
                            </a>
                            {{--@include('layouts.childMenu',['childs'=>$value['child'],'item'=>$key])--}}
                        </li>
                    @elseif($value['menu'] == 1)
                        <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                            <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                <span style=" margin-top: -5px;font-size:14px !important">{{$value['name'] or ''}}</span>
                            </a>
                        </li>
                    @endif
                @endif
            @endforeach

        </ul>
        <ul class="nav second-nav">
        {{--中间插件--}}
            @foreach($middle as $key=>$value)
                @if(isset($value['menu']) && $value['menu'] == 1 && can($key) && $value['left_first_show'] == 1)
                    @switch($key)
                        @case('install_plugins')
                            @if(\YunShop::app()->role === 'founder')
                                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                                    <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                        <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                        <span style=" margin-top: -5px;font-size:14px !important">{{$value['name'] or ''}}</span>
                                    </a>
                                </li>
                            @endif
                            @break
                        @case('charts')
                            @if(app('plugins')->isEnabled('shop-statistics'))
                                @if(!can('shop-statistics'))
                                    <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                                        <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                            <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                            <span style=" margin-top: -5px;font-size:14px !important">{{$value['name']}}</span>
                                        </a>
                                    </li>
                                @endif
                            @else
                                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                                    <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                        <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                        <span style=" margin-top: -5px;font-size:14px !important">{{$value['name']}}</span>
                                    </a>
                                </li>
                            @endif
                            @break
                        @default
                            @if(isset($value['child']) && array_child_kv_exists($value['child'],'menu',1))
                                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                                    <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                        {{--<a href="{{isset($value['url']) ? yzWebFullUrl($value['url']):''}}{{$value['url_params'] or ''}}">--}}
                                        <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                        {{--<span class="pull-right-container">--}}
                                        {{--<i class="fa fa-angle-left pull-right"></i>--}}
                                        {{--</span>--}}
                                        <span style=" margin-top: -5px;font-size:14px !important">{{$value['name']}}</span>
                                    </a>
                                    {{--@include('layouts.childMenu',['childs'=>$value['child'],'item'=>$key])--}}
                                </li>
                            @elseif($value['menu'] == 1)
                                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                                    <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                        <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                        <span style=" margin-top: -5px;font-size:14px !important">{{$value['name'] or ''}}</span>
                                    </a>
                                </li>
                            @endif
                    @endswitch
                @endif
            @endforeach
        </ul>
{{--        <ul class="nav last-nav" >--}}
{{--        --}}{{--底部消息--}}
{{--            @if(isset($bottom['menu']) && $bottom['menu'] == 1 && can('system_msg') && $bottom['left_first_show'] == 1)--}}
{{--                <li class="{{in_array('system_msg',\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">--}}
{{--                    <a href="{{ \app\common\services\MenuService::canAccess('system_msg') }}">--}}
{{--                        <i class="fa {{array_get($bottom,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>--}}
{{--                        <span style=" margin-top: -5px;font-size:14px !important">{{$bottom['name']}}</span>--}}
{{--                        @if(app\common\models\systemMsg\SysMsgLog::getLogCount() > 0)--}}
{{--                            <span class="badge"--}}
{{--                                  style="background-color:#F15353;position: absolute">{{app\common\models\systemMsg\SysMsgLog::getLogCount()<99?app\common\models\systemMsg\SysMsgLog::getLogCount():'...'}}</span>--}}
{{--                        @endif--}}
{{--                    </a>--}}
{{--                    --}}{{--@include('layouts.childMenu',['childs'=>$value['child'],'item'=>$key])--}}
{{--                </li>--}}
{{--            @endif--}}
{{--        </ul>--}}
        {{--菜单结束--}}
    </div>
    <!-- Sidebar Menu -->

    <!-- /.sidebar-menu -->
</section>

