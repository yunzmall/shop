@extends('layouts.base')

@section('content')
@section('title', trans('充值记录'))

<div class="w1200 ">
    @include('layouts.tabs')
    <div class=" rightlist ">
        <form action="" method="post" class="form-horizontal" id="form1">
            <div class="right-addbox">
                <div class="panel panel-info">
                    <div class="panel-body">
                        <div class="form-group col-xs-12 col-sm-2">
                            <input class="form-control" name="search[coupon_name]" id="" type="text"
                                   value="{{$search['coupon_name']}}" placeholder="优惠券名称">
                        </div>
                        <div class="form-group col-xs-12 col-sm-2">
                            <input class="form-control" name="search[member]" id="" type="text"
                                   value="{{$search['member']}}" placeholder="会员ID/会员昵称/姓名/手机">
                        </div>
                        <div class="form-group col-xs-12 col-sm-3">
                            <select name='search[use_type]' class='form-control'>
                                <option value='0'>使用类型</option>
                                @foreach($use_type as $name => $row)
                                    <option value={{$name}} @if($search['use_type'] == $name) selected @endif>{{$row}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group  col-xs-12 col-sm-8">
                            <div class="col-sm-2">
                                <label class='radio-inline'>
                                    <input type='checkbox' value='1' name='search[is_time]'
                                           @if($search['is_time'] == '1') checked @endif>是否搜索时间
                                </label>
                            </div>
                        {!! app\common\helpers\DateRange::tplFormFieldDateRange('search[time]', [
                                                        'starttime'=>$search['time']['start'],
                                                        'endtime'=>$search['time']['end'],
                                                        'start'=>$search['time']['start'],
                                                        'end'=>$search['time']['end']
                                                        ], true) !!}
                        <!--</div>-->
                        </div>

                        <div class="form-group  col-xs-12 col-sm-7 col-lg-4">
                            <div class="">
                                <button type="button" id="search" class="btn btn-success "><i class="fa fa-search"></i>搜索</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
        <div class="clearfix">

            <div class='panel panel-default'>
                <div class='panel-body  table-responsive'>
                    <table class="table table-hover" style="overflow:visible;">
                        <thead>
                        <tr>
                            <th style='width:4%;text-align: center;'>ID</th>
                            <th style='width:8%;text-align: center;'>使用时间</th>
                            <th style='width:6%;text-align: center;'>优惠券名称</th>
                            <th style='width:5%;text-align: center;'>会员</th>
                            <th style='width:6%;text-align: center;'>使用类型</th>
                            <th style='width:15%;text-align: center;'>详情</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if (!empty($list['data']))
                            @foreach($list['data'] as $row)
                                <tr>
                                    <td style='text-align: center;'>{{$row['id']}}</td>
                                    <td style='text-align: center;'>{{$row['created_at']}}</td>
                                    <td style='text-align: center;'>{{$row['has_one_coupon']['name']}}</td>
                                    <td style='text-align: center;'>
                                        <a href="{!! yzWebUrl('member.member.detail', ['id'=>$row['belongs_to_member']['uid']]) !!}"><img src="{{tomedia($row['belongs_to_member']['avatar'])}}" style="width:30px;height:30px;padding:1px;border:1px solid #ccc"><BR>{{$row['belongs_to_member']['nickname']}}</a>
                                    </td>
                                    <td style='text-align: center;'>
                                        {{$row['type_name']}}
                                    </td>
                                    <td style='text-align: center;'>
                                        {{$row['detail']}}
                                    </td>
                                </tr>

                            @endforeach
                        @else
                            <td colspan="6" style="color: red">没有更多数据了！</td>
                        @endif

                        </tbody>
                    </table>

                    {!! $pager !!}
                </div>
            </div>
        </div>
    </div>
</div>

<script language="javascript">
    $(function () {
        $('#search').click(function () {
            $('#form1').attr('action', '{!! yzWebUrl('coupon.coupon-use.log') !!}');
            $('#form1').submit();
        });
    });
</script>
@endsection