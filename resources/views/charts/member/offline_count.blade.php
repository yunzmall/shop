@extends('layouts.base')
@section('content')
<link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
<div class="w1200 m0a">

    <div class="rightlist" id="member-blade">
        @include('layouts.tabs')

        <div class="panel panel-default">
            <div class="panel-body">
                <div class="form-group col-sm-12 col-lg-12 col-xs-12">
                    <div class="col-sm-6 col-lg-6 col-xs-12">
                        <p class="">注：每天凌晨1点执行数据统计，统计截止到前一天的数据；建议不要再同一时间设置数据自动备份、快照等计划任务！</p>
                    </div>
                    <div class="col-sm-1 col-lg-1 col-xs-12">
                        <div class="">
                            <input type="submit" class="btn btn-block btn-primary update" onclick="update()" value="刷新数据">
                        </div>
                    </div>
                </div>
                <form action="" method="post" class="form-horizontal" role="form" id="form1">

                    <div class="form-group col-xs-12 col-sm-2">
                        <input class="form-control" name="search[member_id]" id="" type="text"
                               value="{{$search['member_id']}}" placeholder="会员ID">
                    </div>
                    <div class="form-group col-xs-12 col-sm-2">
                        <input class="form-control" name="search[member_info]" id="" type="text"
                               value="{{$search['member_info']}}" placeholder="会员信息">
                    </div>

                    <div class="form-group col-xs-12 col-sm-2 ">
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
                        <th style='width:80px;'>排行</th>
                        <th>会员ID</th>
                        <th>昵称</th>
                        <th>一级下线人数</th>
                        <th>二级下线人数</th>
                        <th>三级下线人数</th>
                        <th>团队总人数</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($list as $key => $item)
                        <tr>
                            <td>
                                @if($key + 1 + ($this_page - 1)*$page_size <= 3)
                                    <labe class='label label-danger' style='padding:8px;'>&nbsp;{{ $key + 1 + ($this_page - 1)*$page_size }}&nbsp;</labe>
                                @else
                                    <labe class='label label-default'  style='padding:8px;'>&nbsp;{{ $key + 1 + ($this_page - 1)*$page_size }}&nbsp;</labe>
                                @endif
                            </td>
                            <td>{{ $item->uid?:$item->member_id }}</td>
                            <td>
                                @if(!empty($item->belongsToMember->avatar))
                                    <img src='{{ $item->belongsToMember->avatar }}' style='width:30px;height:30px;padding:1px;border:1px solid #ccc' /><br/>
                                @endif
                                @if(empty($item->belongsToMember->nickname))
                                    未更新
                                @else
                                    {{ $item->belongsToMember->nickname }}
                                @endif
                            </td>
                            <td>{{ $item->first_total }}</td>
                            <td>{{ $item->second_total }}</td>
                            <td>{{ $item->third_total }}</td>
                            <td>{{ $item->team_total }}</td>
                        </tr>
                    @endforeach

                </table>
                {!! $page !!}
            </div>
        </div>
    </div>
</div>
    <script>
        function update() {
            $('.update').val('刷新中...')
            $.ajax({
                url: "{!! yzWebUrl('charts.member.offline-count.update') !!}",
                type: "post",
                data: {},
                cache: false,
                success: function ($data) {
                    console.log($data);
                    $('.update').val('成功')
                    location.reload();
                }
            })
        }
    </script>
@endsection
