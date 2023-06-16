<!-- sidebar: style can be found in sidebar.less -->

<section class="sidebar sidebar2" data-active-color="blue" data-background-color="white" data-image="../assets/img/sidebar-1.jpg">
    <div class="sidebar-wrapper" style="overflow-y: auto;overflow-x: hidden;">
        <ul class="nav">

            @foreach(\app\backend\modules\menu\Menu::current()->getItems()[\app\backend\modules\menu\Menu::current()->getCurrentItems()[0]]['child'] as $key=>$value)
                @if(isset($value['menu']) && $value['menu'] == 1 && can($key))
                    @if(isset($value['child']) && array_child_kv_exists($value['child'],'menu',1))
                        <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                            <a href="{{isset($value['url']) ? yzWebFullUrl($value['url']):''}}{{$value['url_params'] ?? ''}}">
                                <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                {{--<span class="pull-right-container">--}}
                                {{--<i class="fa fa-angle-left pull-right"></i>--}}
                                {{--</span>--}}
                                <p>{{$value['name']}}</p>
                            </a>
                            {{--@include('layouts.childMenu',['childs'=>$value['child'],'item'=>$key])--}}
                        </li>
                    @elseif($value['menu'] == 1)
                        <li class="{{in_array($key,\app\backend\modules\menu\Menu::current()->getCurrentItems()) ? 'active' : ''}}">
                            <a href="{{isset($value['url']) ? yzWebFullUrl($value['url']):''}}{{$value['url_params'] ?? ''}}">
                                <i class="fa {{array_get($value,'icon','fa-circle-o') ?: 'fa-circle-o'}}"></i>
                                <p>{{$value['name'] ?? ''}}
                                    @if(isset($value['bubble']) && $value['bubble'] >= 1)
                                    <span style="
                                      display: inline-block;
                                      background: #ff485d;
                                      color: #fff;
                                      border-radius: 8px 8px 8px 1px;
                                      position: relative;
                                      top: -10px;
                                      height: 15px;
                                      line-height: 15px;
                                      padding: 0 5px;
                                      font-size: 12px;
                                      font-weight: 500;
                                      text-overflow: ellipsis;
                                      overflow: hidden;
                                      white-space: nowrap;
                                      /*width: 47px;*/
                                    ">{{$value['bubble']}}</span>
                                    @endif
                                </p>
                            </a>
                        </li>
                    @endif
                @endif
            @endforeach
        </ul>
        {{--菜单结束--}}
    </div>
    <!-- Sidebar Menu -->
    <!-- /.sidebar-menu -->
</section>
