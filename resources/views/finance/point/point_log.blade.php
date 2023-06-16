@extends('layouts.base')

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
                <form action=" " method="post" class="form-horizontal" role="form">


                    <div style="display: flex;flex-direction: row;flex-wrap: wrap">
                        <div style="width: 15%;margin-left: 2%">
                            <input class="form-control" name="search[realname]" type="text" value="{{ $search['realname'] ?? ''}}" placeholder="会员ID/会员姓名/昵称/手机号">
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
                            <select name="search[activity_id]" class="form-control">
                                <option value="" selected >业务类型</option>
                                @foreach($activityMode as $key=>$value)
                                    <option value="{{ $key }}" @if($search['activity_id'] == $key) selected @endif>{{ $value}}</option>
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
                                <select name='search[searchtime]' class='form-control'>
                                    <option value='' @if(empty($search['searchtime'])) selected @endif>不搜索</option>
                                    <option value='1' @if($search['searchtime']==1) selected @endif >搜索</option>
                                </select>
                            </div>
                            <div style="margin-left: 2%">
                                {!! tpl_form_field_daterange(
                             'search[time_range]',
                             array(
                                 'starttime'=>array_get($requestSearch,'time_range.start',$search['time_range']['start']?:date('Y-m-d H:i',strtotime('-1 month'))),
                                 'endtime'=>array_get($requestSearch,'time_range.end',$search['time_range']['end']?:date('Y-m-d H:i',time())),
                             ),
                            true
                         )!!}
                            </div>
                            <div style="width: 10%;position: relative;left: 10%">
                                <!--<label class="col-xs-12 col-sm-12 col-md-1 col-lg-1 control-label"></label>-->
                                <div class="btn-input">
                                    <input type="submit" class="btn btn-block btn-success" value="搜索">
                                    <!--<button type="submit" name="export" value="1" class="btn btn-primary">导出 Excel</button>-->
                                </div>
                            </div>
                        </div>



                    </div>

                </form>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">总数：{{ $list->total() }}</div>
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
                    @foreach($list as $log)
                        <tr style="text-align: center;">
                            <td>{{ $log->id }}</td>
                            <td>
                                <a href="{{ yzWebUrl('member.member.detail', array('id' => $log->hasOneMember->uid)) }}">
                                    <img src='{{ $log->hasOneMember->avatar}}' style='width:30px;height:30px;padding:1px;border:1px solid #ccc'/>
                                <br/>
                                {{ $log->hasOneMember->nickname}}
                                </a>
                            </td>
                            <td>
                                {{ $log->hasOneMember->realname }}
                                <br/>
                                {{ $log->hasOneMember->mobile }}
                            </td>

                            <td>{{ $log->created_at }}</td>
                            <td>
                                <span class='label label-info'>{{$log->mode_name}}</span>
                            </td>
                            <td>
                                {{--<span class='label label-success'>{{ $log->before_point }}</span><br />--}}
                                <span class='label label-danger'>积分：{{ $log->after_point }}</span><br />
                                {{--<span class='label label-danger'>{{ $log->point }}</span>--}}
                            </td>
                            <td><span class='label label-success'>{{ $log->point }}</span></td>
                            <td class="remark"><span class="span"  style="display:inline-block;width:80%;overflow: hidden;text-overflow: ellipsis;" >{{ $log->remark }}</span><a class="a" style="color:#333;"><i class="iconfont icon-down i" style="font-size:16px;" ></i></a></td>
                        </tr>
                    @endforeach
                </table>
                {!! $pager !!}
            </div>
        </div>
    </div>
    <script>
        $(function () {
            $("#ambiguous-field").on('change',function(){

                $(this).next('input').attr('placeholder',$(this).find(':selected').text().trim())
            });
        })
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
        $('#export').click(function () {
            $('#form_p').val("order.list.export");
            $('#form1').submit();
            $('#form_p').val("order.list");
        });
    </script>

@endsection