@extends('layouts.base')
@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <div class="w1200 m0a">
        <div class="rightlist" id="member-blade">
            <div class="panel panel-info">
                <ul class="add-shopnav">
                    <li class="active">
                        <a href="{{yzWebFullUrl('member.merge-log.authMerge')}}">
                            自动合并记录
                        </a>
                    </li>
                    <li class="active">
                        <a href="{{yzWebFullUrl('member.merge-log.bindTel')}}">
                            绑定手机合并记录
                        </a>
                    </li>
                    <li class="active">
                        <a href="{{yzWebFullUrl('member.merge-log.clickMerge')}}">
                            点击合并记录
                        </a>
                    </li>
                </ul>
            </div>
            <div class="panel panel-default">
                <div class="panel-body">
                    <form action="" method="post" class="form-horizontal" role="form" id="form1">
                        <div class="form-group col-sm-11 col-lg-11 col-xs-12">
                            <div class="">
                                <div class='input-group'>
                                    <div class='form-input'>
                                        <p class="input-group-addon" >合并前会员ID</p>
                                        <input class="form-control price" style="width: 45%;" type="text" name="search[member_id]" value="{{ $search['member_id'] ?? ''}}">
                                    </div>
                                    <div class='form-input'>
                                        <p class="input-group-addon" >合并后会员ID</p>
                                        <input class="form-control price" style="width: 30%;" type="text" name="search[mark_member_id]" value="{{ $search['mark_member_id'] ?? ''}}">
                                    </div>
                                    {{--<div class=''>--}}
                                    {{--<p class="" align="center">注：每天凌晨1点执行数据统计，统计截止到前一天的数据；建议不要再同一时间设置数据自动备份、快照等计划任务！</p>--}}
                                    {{--</div>--}}
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-sm-1 col-lg-1 col-xs-12">
                            <div class="">
                                <input type="submit" class="btn btn-block btn-success" value="搜索">
                            </div>
                        </div>
                    </form>
                </div>
                <div class='panel-body'>
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th>id</th>
                            <th>合并前会员id</th>
                            <th>合并后会员id</th>
                            <th>合并时间</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($list['data'] as $key => $item)
                            <tr>
                                <td>
                                    {{ $item['id'] }}
                                </td>
                                <td>
                                    {{ $item['member_id'] }}
                                </td>
                                <td>
                                    {{ $item['mark_member_id'] }}
                                </td>
                                <td>
                                    {{ $item['created_at'] }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    {!! $page !!}
                </div>
            </div>
        </div>
    </div>
@endsection
