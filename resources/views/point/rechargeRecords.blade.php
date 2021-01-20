@extends('layouts.base')

@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <div id="member-blade" class="rightlist">
        <!-- 新增加右侧顶部三级菜单 -->

        <div class="right-titpos">
            @include('layouts.tabs')
        </div>
        <!-- 新增加右侧顶部三级菜单结束 -->
        <div class="panel panel-info">
            <div class="panel-heading">筛选</div>
            <div class="panel-body">
                <form action="" method="get" class="form-horizontal" role="form" id="form1">
                    <input type="hidden" name="c" value="site" />
                    <input type="hidden" name="a" value="entry" />
                    <input type="hidden" name="m" value="yun_shop" />
                    <input type="hidden" name="do" value="5201" />
                    <input type="hidden" name="route" value="point.recharge-records.index" id="route" />
                    <div style="display: flex;flex-direction: row;flex-wrap: wrap">
                        <div style="width: 15%;margin-left: 2%">
                            <input class="form-control" name="search[order_sn]" type="text" value="{{ $search['order_sn'] or ''}}" placeholder="充值单号">
                        </div>
                        <div style="width: 15%;margin-left: 2%">
                            <input class="form-control" name="search[member]" type="text" value="{{ $search['member'] or ''}}" placeholder="会员ID/会员姓名/昵称/手机号">
                        </div>
                        <div style="width: 50%;margin-left: 2%;display: flex;flex-direction: row;">
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
        <div class="clearfix">
            <div class="panel panel-default">
                <div class="panel-heading">总数：{{ $memberList }}</div>
                <div class="panel-body">
                    <table class="table table-hover" style="overflow:visible;">
                        <thead class="navbar-inner">
                        <tr>
                            <th style='width:15%; text-align: center;'>充值单号</th>
                            <th style='width:10%; text-align: center;'>粉丝</th>
                            <th style='width:10%; text-align: center;'>会员信息<br/>手机号</th>
                            <th style='width:12%; text-align: center;'>充值时间</th>
                            <th style='width:10%; text-align: center;'>充值方式</th>
                            <th style='width:10%; text-align: center;'>充值金额<br/>状态</th>
                            <th style='width:13%; text-align: center;'>备注信息</th>
                            <th style='width:10%; text-align: center;'>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($pageList as $list)
                            <tr style="text-align: center;">
                                <td>{{ $list->order_sn }}</td>
                                <td>
                                    @if($list->member->avatar || $shopSet['headimg'])
                                        <img src='{{ $list->member->avatar ? tomedia($list->member->avatar) : tomedia($shopSet['headimg'])}}'
                                             style='width:30px;height:30px;padding:1px;border:1px solid #ccc'/>
                                        <br/>
                                    @endif
                                    {{ $list->member->nickname ? $list->member->nickname : '未更新' }}
                                </td>
                                <td>
                                    {{ $list->member->realname }}
                                    <br/>
                                    {{ $list->member->mobile }}
                                </td>
                                <td>{{ $list->created_at }}</td>
                                <td>
                                    @if($list->type == 0)
                                        <span class='label label-default'>{{ $list->type_name }}</span>
                                    @elseif($list->type ==1)
                                        <span class='label label-success'>{{ $list->type_name }}</span>
                                    @elseif($list->type == 2)
                                        <span class='label label-warning'>{{ $list->type_name }}</span>
                                    @elseif($list->type == 9 || $list->type == 10)
                                        <span class='label label-info'>{{ $list->type_name }}</span>
                                    @else
                                        <span class='label label-primary'>{{ $list->type_name }}</span>
                                    @endif

                                </td>
                                <td>
                                    {{ $list->money }}
                                    <br/>
                                    @if($list->status == 1)
                                        <span class='label label-success'>充值成功</span>
                                    @elseif($list->status == '-1')
                                        <span class='label label-warning'>充值失败</span>
                                    @else
                                        <span class='label label-default'>申请中</span>
                                    @endif

                                </td>
                                <td><a style="color: #0a0a0a" title="{{ $list->remark }}">{{ $list->remark }}</a></td>
                                <td>
                                    <a class='btn btn-default'
                                       href="{{ yzWebUrl('member.member.detail', array('id' => $list->member_id)) }}"
                                       style="margin-bottom: 2px">用户信息</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                    {!! $page !!}

                </div>
            </div>
        </div>
    </div>
    <script>
        $(function () {
            $('#export').click(function(){
                $('#route').val("point.recharge-export.index");
                $('#form1').submit();
                $('#route').val("point.recharge-records.index");
            });
        });
    </script>
@endsection

