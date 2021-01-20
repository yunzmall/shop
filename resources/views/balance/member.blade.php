@extends('layouts.base')
@section('title', '余额管理')
@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <div id="member-blade" class="rightlist">
        <div class="panel panel-info">
            <div class="panel-heading">筛选</div>
            <div class="panel-body">
                <form action="" method="get" class="form-horizontal" role="form" id="form1">
                    <input type="hidden" name="c" value="site" />
                    <input type="hidden" name="a" value="entry" />
                    <input type="hidden" name="m" value="yun_shop" />
                    <input type="hidden" name="do" value="5201" />
                    <input type="hidden" name="route" value="balance.member.index" id="route" />

                    <div class="form-group col-sm-11 col-lg-11 col-xs-12">
                        <div class="">
                            <div class='input-group'>

                                <input class="form-control" name="search[realname]" type="text"
                                       value="{{ $search['realname'] or ''}}" placeholder="会员ID／姓名／昵称／手机号">

                                <div class='form-input'>
                                    <p class="input-group-addon">会员等级</p>
                                    <select name="search[level]" class="form-control">
                                        <option value="" selected>不限</option>
                                        @foreach($memberLevel as $level)
                                            <option value="{{ $level['id'] }}"
                                                    @if($search['level'] == $level['id']) selected @endif>{{ $level['level_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class='form-input'>
                                    <p class="input-group-addon">会员分组</p>
                                    <select name="search[groupid]" class="form-control">
                                        <option value="" selected>不限</option>
                                        @foreach($memberGroup as $group)
                                            <option value="{{ $group['id'] }}"
                                                    @if($search['groupid'] == $group['id']) selected @endif>{{ $group['group_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class='form-input'>
                                    <p class="input-group-addon price">余额区间</p>
                                    <input class="form-control price" name="search[min_credit2]" type="text"
                                           value="{{ $search['min_credit2'] or ''}}" placeholder="最小">
                                    <p class="line">—</p>
                                    <input class="form-control price" name="search[max_credit2]" type="text"
                                           value="{{ $search['max_credit2'] or ''}}" placeholder="最大">
                                </div>

                            </div>
                        </div>
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
                </form>
            </div>
        </div>
        <div class="clearfix">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <span>总数：{{ $pageList->total() }}</span>
                    &nbsp&nbsp&nbsp&nbsp
                    <span>余额总合计：{{ $amount }}</span>
                </div>
                <div class="panel-body">
                    <table class="table table-hover" style="overflow:visible;">
                        <thead class="navbar-inner">
                        <tr>
                            <th style='width:8%;text-align: center;'>会员ID</th>
                            <th style='width:8%;text-align: center;'>粉丝</th>
                            <th style='width:12%;'>姓名<br/>手机号码</th>
                            <th style='width:8%;'>等级/分组</th>
                            <th style='width:15%;'>余额</th>
                            <th style='width:8%'>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($pageList as $list)
                            <tr>
                                <td style="text-align: center;">{{ $list->uid }}</td>
                                <td style="text-align: center;">

                                    @if($list->avatar || $shopSet['headimg'])
                                        <img src='{{ $list->avatar ? tomedia($list->avatar) : tomedia($shopSet['headimg']) }}'
                                             style='width:30px;height:30px;padding:1px;border:1px solid #ccc'/><br/>
                                    @endif

                                    {{ $list->nickname ?: '未更新'}}

                                </td>
                                <td>{{ $list->realname }}<br/>{{ $list->mobile }}</td>
                                <td>
                                    {{ isset($list->yzMember->level) ? $list->yzMember->level->level_name : $shopSet['level_name'] }}
                                    <br/>
                                    {{ $list->yzMember->group->group_name or '' }}
                                </td>
                                <td>
                                    <label class="label label-danger">余额：{{ $list->credit2 }}</label>
                                </td>
                                <td style="overflow:visible;">
                                    <a class='btn btn-default'
                                       href="{{ yzWebUrl('balance.recharge.index', array('member_id' => $list->uid)) }}"
                                       style="margin-bottom: 2px">充值余额</a>
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
                $('#route').val("balance.member-export.index");
                $('#form1').submit();
                $('#route').val("balance.member.index");
            });
        });
    </script>
@endsection
