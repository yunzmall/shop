@extends('layouts.base')
@section('title', '积分明细')
@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <style>
        .toggle{
            white-space:initial;
            overflow:visible;
        }
        #text{
            width:100%;
        }
    </style>
    <div id="recharge-blade" class="rightlist">
        <div class="panel panel-info">

            <div class="right-titpos">
                @include('layouts.tabs')
            </div>

            <div class="panel-heading">筛选</div>
            <div class="panel-body">
                <form action="" method="get" class="form-horizontal" role="form" id="form1">
                    <input type="hidden" name="c" value="site" />
                    <input type="hidden" name="a" value="entry" />
                    <input type="hidden" name="m" value="yun_shop" />
                    <input type="hidden" name="do" value="5201" />
                    <input type="hidden" name="route" value="point.records.index" id="route" />
                    <div style="display: flex;flex-direction: row;flex-wrap: wrap">
                        <div style="width: 15%;margin-left: 2%">
                            <input class="form-control" name="search[member]" type="text" value="{{ $search['member'] or ''}}" placeholder="会员ID/会员姓名/昵称/手机号">
                        </div>
                        <div  style="width: 15%;margin-left: 2%">
                            <select name="search[level_id]" class="form-control">
                                <option value="" selected>会员等级</option>
                                @foreach($memberLevel as $level)
                                    <option value="{{ $level['id'] }}" @if($search['level_id'] == $level['id']) selected @endif>{{ $level['level_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="width: 15%;margin-left: 2%">
                            <select name="search[group_id]" class="form-control">
                                <option value="" selected >会员分组</option>
                                @foreach($memberGroup as $group)
                                    <option value="{{ $group['id'] }}" @if($search['group_id'] == $group['id']) selected @endif>{{ $group['group_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="width: 15%;margin-left: 2%">
                            <select name="search[source]" class="form-control">
                                <option value="" selected >业务类型</option>
                                @foreach($sourceComment as $key => $value)
                                    <option value="{{ $key }}" @if($search['source'] == $key) selected @endif>{{ $value}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="width: 15%;margin-left: 2%">
                            <select name="search[income_type]" class="form-control">
                                <option value="" selected >收入/支出</option>
                                <option value="1" @if($search['income_type'] == 1) selected @endif>收入</option>
                                <option value="-1" @if($search['income_type'] == -1) selected @endif>支出</option>
                            </select>
                        </div>
                        <div style="width: 50%;margin-left: 2%;display: flex;flex-direction: row;margin-top: 1%">
                            <div style="width: 20%;">
                                <select name='search[search_time]' class='form-control'>
                                    <option value='' @if(empty($search['search_time'])) selected @endif>不搜索</option>
                                    <option value='1' @if($search['search_time']==1) selected @endif >搜索</option>
                                </select>
                            </div>
                            <div style="margin-left: 2%">
                                {!! app\common\helpers\DateRange::tplFormFieldDateRange('search[time]', [
                                    'starttime'=>date('Y-m-d H:i', strtotime($search['time']['start']) ?: strtotime('-1 month')),
                                    'endtime'=>date('Y-m-d H:i',strtotime($search['time']['end']) ?: time()),
                                    'start'=>0,
                                    'end'=>0
                                ], true) !!}
                            </div>
                            <div class="form-group  col-xs-12 col-sm-7 col-lg-4">
                                <div class="">
                                    <input type="hidden" name="token" value="{{$var['token']}}" />
                                    <button class="btn btn-success ">
                                        <i class="fa fa-search"></i>
                                        搜索
                                    </button>
                                    <button type="button" name="export" value="1" id="export" class="btn btn-primary excel back ">
                                        导出 EXCEL
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">总数：{{ $pageList->total() }}</div>
            <div class="panel-body ">
                <table class="table table-hover">
                    <thead class="navbar-inner">
                    <tr>
                        <th style='width:5%; text-align: center;'>ID</th>
                        <th style='width:8%; text-align: center;'>粉丝</th>
                        <th style='width:8%; text-align: center;'>会员信息<br/>手机号</th>
                        <th style='width:10%; text-align: center;'>时间</th>
                        <th style='width:12%; text-align: center;'>业务类型</th>
                        <th style='width:10%; text-align: center;'>积分</th>
                        <th style='width:10%; text-align: center;'>收入/支出</th>
                        <th style='width:12%; text-align: center;'>备注</th>
                    </tr>
                    </thead>
                    @foreach($pageList as $log)
                        <tr style="text-align: center;">
                            <td>{{ $log->id }}</td>
                            <td>
                                <a href="{{ yzWebUrl('member.member.detail', array('id' => $log->member->uid)) }}">
                                    <img src='{{ $log->member->avatar}}' style='width:30px;height:30px;padding:1px;border:1px solid #ccc'/>
                                    <br/>
                                    {{ $log->member->nickname}}
                                </a>
                            </td>
                            <td>
                                {{ $log->member->realname }}
                                <br/>
                                {{ $log->member->mobile }}
                            </td>

                            <td>{{ $log->created_at }}</td>
                            <td>
                                <span class='label label-info'>{{$log->source_name}}</span>
                            </td>
                            <td>
                                <span class='label label-danger'>积分：{{ $log->after_point }}</span><br />
                            </td>
                            <td><span class='label label-success'>{{ $log->point }}</span></td>
                            <td class="remark"><span class="span"  style="display:inline-block;width:80%;overflow: hidden;text-overflow: ellipsis;" >{{ $log->remark }}</span><a class="a" style="color:#333;"><i class="iconfont icon-down i" style="font-size:16px;" ></i></a></td>
                        </tr>
                    @endforeach
                </table>
                {!! $page !!}
            </div>
        </div>
    </div>
    <script>
        $('.remark').each(function(i){
            $(this).click(function(){
                $(this).children('.span').toggleClass('toggle')
                let a=$(this).children('.a').children('.i').attr('class').split(' ')

                if(a.includes('icon-down')){
                    $(this).children('.a').children('.i').removeClass('icon-down').addClass('icon-up')
                }else if(a.includes('icon-up')){
                    $(this).children('.a').children('.i').removeClass('icon-up').addClass('icon-down')
                }
            })
        })
        $(function () {
            $('#export').click(function(){
                $('#route').val("point.export.index");
                $('#form1').submit();
                $('#route').val("point.records.index");
            });
        });
    </script>

@endsection
