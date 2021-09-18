@extends('layouts.base')
@section('title', '修改记录')
@section('content')
    <link href="{{static_url('yunshop/css/member.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <!-- <div class="w1200 m0a"  style="padding-bottom:80px"> -->
        <div class="rightlist" style="padding-bottom:100px">
            <!-- 新增加右侧顶部三级菜单 -->
            <div class="right-titpos">
                <ul class="add-snav">
                    <li class="active">修改记录</li>
                </ul>
            </div>
            <!-- 新增加右侧顶部三级菜单结束 -->
            <div class="panel panel-info"><!--<div class="panel-heading">筛选</div>-->
                <div class="panel-body">
                    <form action="" method="get" class="form-horizontal" role="form" id="form1">
                    </form>
                </div>
            </div>
            <div class="clearfix">
                <div class="panel panel-default">
                    <div class="panel-body" style="margin-bottom:200px">
                        <table class="table table-hover" style="overflow:visible">
                            <thead class="navbar-inner">
                            <tr>
                                <th style='width:20%;text-align: center;'>ID</th>
                                <th style='width:20%;text-align: center;'>会员id</th>
                                <th style='width:20%;text-align: center;'>修改前上级id</th>
                                <th style='width:20%;text-align: center;'>修改后上级id</th>
                                <th style='width:20%;text-align: center;'>状态</th>
                                <th style='width:20%;text-align: center;'>修改时间</th>
                            </tr>
                            </thead>
                            <tbody>
                        @foreach($list['data'] as $row)
                            <tr>
                                <td style="text-align: center; width: 20%;">{{$row['id']}}</td>
                                <td style="text-align: center; width: 20%;">
                                    {{$row['uid']}}
                                </td>
                                <td style="text-align: center; width: 20%;">
                                    {{$row['parent_id']}}

                                </td>
                                <td style="text-align: center; width: 20%;">
                                    {{$row['after_parent_id']}}
                                </td>
                                <td style="text-align: center; width: 20%;">{{$row['status_value']}}</td>
                                <td style="text-align: center; width: 20%;">{{$row['created_at']}}</td>
                            </tr>
                        @endforeach
                            </tbody>
                        </table>
                           {!!$pager!!}
                    </div>
                </div>
            </div>
        </div>
    <!-- </div> -->
    <script type="text/javascript">
        $(function () {
            $('#export').click(function () {
                $('#route').val("member.member_invited.export");
                $('#form1').submit();
                $('#route').val("member.member.index");
            });
        });
    </script>
@endsection

