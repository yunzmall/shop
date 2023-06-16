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
    .icon-img{
        width: 18px;
        height: 18px;
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
                        <!-- <i class="iconfont icon-daohang-gaikuang "></i> -->
                        <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/overview_active.png') : resource_absolute('static/images/overview.png')}}" alt="">
                        <span style=" margin-top: -5px;font-size:14px !important">概况</span>
                    </a>
                </li>
            @else
                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                    <a href="{{yzWebUrl('index.index')}}">
                        <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/shopping_mall_active.png') : resource_absolute('static/images/shopping_mall.png')}}" alt="">
                        <span style=" margin-top: -5px;font-size:14px !important">商城</span>
                    </a>
                </li>
            @endif
            @php
                $first = \app\backend\modules\menu\Menu::current()->getFirst();
            @endphp

            {{--顶部主要菜单--}}
            @foreach($first['top'] as $key=>$value)
                @if(isset($value['menu']) && $value['menu'] == 1 && can($key) && $value['left_first_show'] == 1)
                    @if(isset($value['child']) && array_child_kv_exists($value['child'],'menu',1))
                        <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                            <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                @switch($value['name'])
                                    @case('商品')
                                    <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/shop_active.png') : resource_absolute('static/images/shop.png')}}" alt="">
                                    @break
                                    @case('会员')
                                    <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/member_active.png') : resource_absolute('static/images/member.png')}}" alt="">
                                    @break
                                    @case('订单')
                                    <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/order_active.png') : resource_absolute('static/images/order.png')}}" alt="">
                                    @break
                                    @case('财务')
                                    <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/finance_active.png') : resource_absolute('static/images/finance.png')}}" alt="">
                                    @break
                                    @case('系统')
                                    <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/system_active.png') : resource_absolute('static/images/system.png')}}" alt="">
                                    @break
                                    @case('消息')
                                    <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/message_active.png') : resource_absolute('static/images/message.png')}}" alt="">
                                    @break
                                @endswitch
                                <span style=" margin-top: -5px;font-size:14px !important">{{$value['name']}}</span>
                                @if($value['name'] == '消息')
                                    @if(app\common\models\systemMsg\SysMsgLog::getLogCount() > 0)
                                        <span class="badge"
                                              style="background-color:#F15353;position: absolute">{{app\common\models\systemMsg\SysMsgLog::getLogCount()<99?app\common\models\systemMsg\SysMsgLog::getLogCount():'...'}}</span>
                                    @endif
                                @endif
                            </a>
                        </li>
                    @elseif($value['menu'] == 1)
                        <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                            <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                <span style=" margin-top: -5px;font-size:14px !important">{{$value['name'] ?? ''}}</span>
                            </a>
                        </li>
                    @endif
                @endif
            @endforeach

        </ul>
        <ul class="nav second-nav">
        {{--中间插件--}}
            @foreach($first['bottom'] as $key=>$value)
                @if(isset($value['menu']) && $value['menu'] == 1 && can($key) && $value['left_first_show'] == 1)
                    @switch($key)
                        @case('install_plugins')
                            @if(\YunShop::app()->role === 'founder')
                                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                                    <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                        <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/install_active.png') : resource_absolute('static/images/install.png')}}" alt="">
                                        <span style=" margin-top: -5px;font-size:14px !important">{{$value['name'] ?? ''}}</span>
                                    </a>
                                </li>
                            @endif
                            @break
                        @case('charts')
                            @if(app('plugins')->isEnabled('shop-statistics'))
                                @if(!can('shop-statistics'))
                                    <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                                        <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/statistics_active.png') : resource_absolute('static/images/statistics.png')}}" alt="">
                                            <span style=" margin-top: -5px;font-size:14px !important">{{$value['name']}}</span>
                                        </a>
                                    </li>
                                @endif
                            @else
                                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                                    <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                        <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/statistics_active.png') : resource_absolute('static/images/statistics.png')}}" alt="">
                                        <span style=" margin-top: -5px;font-size:14px !important">{{$value['name']}}</span>
                                    </a>
                                </li>
                            @endif
                            @break
                        @default
                            @if(isset($value['child']) && array_child_kv_exists($value['child'],'menu',1))
                                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                                    <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                        @switch($value['name'])
                                            @case('门店')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/store_active.png') : resource_absolute('static/images/store.png')}}" alt="">
                                            @break
                                            @case('供应商')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/supplier_active.png') : resource_absolute('static/images/supplier.png')}}" alt="">
                                            @break
                                            @case(app('plugins')->isEnabled('package-deliver')? PackageDeliver : '自提点')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/package_deliver_active.png') : resource_absolute('static/images/package_deliver.png')}}" alt="">
                                            @break
                                            @case('招商')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/merchant_active.png') : resource_absolute('static/images/merchant.png')}}" alt="">
                                            @break
                                            @case('酒店')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/hotel_active.png') : resource_absolute('static/images/hotel.png')}}" alt="">
                                            @break
                                            @case('区域')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/region_active.png') : resource_absolute('static/images/region.png')}}" alt="">
                                            @break
                                            @case('消费券')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/consumer_coupon_active.png') : resource_absolute('static/images/consumer_coupon.png')}}" alt="">
                                            @break
                                            @case('商品')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/shop_active.png') : resource_absolute('static/images/shop.png')}}" alt="">
                                            @break
                                            @case('会员')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/member_active.png') : resource_absolute('static/images/member.png')}}" alt="">
                                            @break
                                            @case('订单')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/order_active.png') : resource_absolute('static/images/order.png')}}" alt="">
                                            @break
                                            @case('应用')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/app.png') : resource_absolute('static/images/app.png')}}" alt="">
                                            @break
                                            @case('统计')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/statistics_active.png') : resource_absolute('static/images/statistics.png')}}" alt="">
                                            @break
                                            @case('装修DIY')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/renovation_active.png') : resource_absolute('static/images/renovation.png')}}" alt="">
                                            @break
                                            @case('装修')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/renovation_active.png') : resource_absolute('static/images/renovation.png')}}" alt="">
                                            @break
                                        @endswitch
                                        <span style=" margin-top: -5px;font-size:14px !important">{{$value['name']}}</span>
                                    </a>
                                </li>
                            @elseif($value['menu'] == 1)
                                <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                                    <a href="{{ \app\common\services\MenuService::canAccess($key) }}">
                                        @if($value['name'] == '统计')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/statistics_active.png') : resource_absolute('static/images/statistics.png')}}" alt="">
                                        @elseif($value['name'] == '应用')
                                            <img class="icon-img" src="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? resource_absolute('static/images/app.png') : resource_absolute('static/images/app.png')}}" alt="">
                                        @else
                                            <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                        @endif
                                        <span style=" margin-top: -5px;font-size:14px !important">{{$value['name'] ?? ''}}</span>
                                    </a>
                                </li>
                            @endif
                    @endswitch
                @endif
            @endforeach
        </ul>
        {{--菜单结束--}}
    </div>
    <!-- Sidebar Menu -->

    <!-- /.sidebar-menu -->
</section>

