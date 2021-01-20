@extends('layouts.base')
@section('title','系统通知')
@section('js')
    <link href="{{static_url('yunshop/css/order.css')}}" media="all" rel="stylesheet" type="text/css"/>
@stop

@section('content')
    <div class="w1200 m0a">

        <div class="rightlist">
            <!-- 新增加右侧顶部三级菜单 -->
            <div class="right-titpos">
                <ul class="add-snav">
                    <li class="active"><a href="#">系统消息通知 &nbsp; <i class="fa fa-angle-double-right"></i> &nbsp; 系统通知</a>
                    </li>
                </ul>
            </div>
            <!-- 新增加右侧顶部三级菜单结束 -->
            <div class="main" style="padding: 2% 3%;">
                {{$data['content']}}
            </div>

@endsection('content')

