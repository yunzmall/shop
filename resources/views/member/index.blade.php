@extends('layouts.base')
@section('title', '会员列表')
@section('content')

    <link href="{{static_url('yunshop/css/member.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <style>
        .memeber-tag-tr td{
            border: 1px solid transparent;
        }

        .line_del{
            position: relative;
        }
        .tip_del{
            position: absolute;
            left: 100%;
            top: -8px;
            display: none;
            color: #d9534f;
        }
    </style>
    <div class="w1200 m0a" >
        <div class="rightlist" style="padding-bottom:0px;">
            <!-- 新增加右侧顶部三级菜单 -->
            <div class="right-titpos">
                <ul class="add-snav">
                    <li class="active"><a href="{{yzWebUrl('member.member.index')}}">会员管理</a></li>
                    <li><a href="javascript:void"> &nbsp;<i class="fa fa-angle-double-right"></i> &nbsp;全部会员</a></li>
                </ul>
            </div>
            <!-- 新增加右侧顶部三级菜单结束 -->
            <div class="panel panel-info"><!--
                <div class="panel-heading">筛选</div>-->
                <div class="panel-body">
                    <form action="" method="get" class="form-horizontal" role="form" id="form1">
                        <input type="hidden" name="c" value="site"/>
                        <input type="hidden" name="a" value="entry"/>
                        <input type="hidden" name="m" value="yun_shop"/>
                        <input type="hidden" name="do" value="member" id="form_do"/>
                        <input type="hidden" name="route" value="member.member.index" id="route"/>
                        <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2 ">
                            <!--<label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">ID</label>-->
                            <div class="">
                                <input type="text" placeholder="会员ID" class="form-control" name="search[mid]"
                                       value="{{$request['search']['mid']}}"/>
                            </div>
                        </div>
                        <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <!-- <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">会员信息</label>-->

                            <select name='search[name_type]' class='form-control' style="width: 35% ; float: left">
                                <option value='0'
                                        @if($request['search']['name_type']=='0')
                                        selected
                                        @endif
                                >昵称
                                </option>
                                <option value='1'
                                                @if($request['search']['name_type']=='1')
                                                selected
                                                @endif
                                        >姓名
                                </option>
                                <option value='2'
                                        @if($request['search']['name_type']=='2')
                                        selected
                                        @endif
                                >手机号
                                </option>
                            </select>
                            <input type="text" class="form-control"  style="width: 60%" name="search[realname]"
                               value="{{$request['search']['realname']}}" placeholder="可搜索昵称/姓名/手机号"/>
                        </div>
                        <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <div class="">
                                <input type="text" class="form-control" name="search[first_count]"
                                       value="{{$request['search']['first_count']}}" placeholder="一级人数"/>
                            </div>
                        </div>
                        <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <div class="">
                                <input type="text" class="form-control" name="search[second_count]"
                                       value="{{$request['search']['second_count']}}" placeholder="二级人数"/>
                            </div>
                        </div>
                        <!-- <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <div class="">
                                <input type="text" class="form-control" name="search[third_count]"
                                       value="{{$request['search']['third_count']}}" placeholder="三级人数"/>
                            </div>
                        </div> -->
                        <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <div class="">
                                <input type="text" class="form-control" name="search[team_count]"
                                       value="{{$request['search']['team_count']}}" placeholder="团队人数"/>
                            </div>
                        </div>
                        <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <div class="">
                                <input type="text" class="form-control" name="search[custom_value]"
                                       value="{{$request['search']['custom_value']}}" placeholder="自定义字段"/>
                            </div>
                        </div>

                        <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <!-- <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">会员等级</label>-->
                            <div class="">
                                <select name='search[level]' class='form-control'>
                                    <option value=''>会员等级不限</option>
                                    <option value='0'
                                        @if($request['search']['level']=='0')
                                        selected
                                        @endif
                                    >普通会员</option>

                                    @foreach($levels as $level)
                                        <option value='{{$level['id']}}'
                                                @if($request['search']['level']==$level['id'])
                                                selected
                                                @endif
                                        >{{$level['level_name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <!--  <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">会员分组</label>-->
                            <div class="">
                                <select name='search[groupid]' class='form-control'>
                                    <option value=''>会员分组不限</option>
                                    @foreach($groups as $group)
                                        <option value='{{$group['id']}}'
                                                @if($request['search']['groupid']==$group['id'])
                                                selected
                                                @endif
                                        >{{$group['group_name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <div class="">
                                <select name='search[isagent]' class='form-control'>
                                    <option value=''>推广员不限</option>
                                    <option value='0'
                                            @if($request['search']['isagent']=='0')
                                            selected
                                            @endif>否
                                    </option>
                                    <option value='1'
                                            @if($request['search']['isagent']=='1')
                                            selected
                                            @endif>是
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <!--      <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">是否关注</label>-->
                            <div class="">
                                <select name='search[followed]' class='form-control'>
                                    <option value=''>不限关注</option>
                                    </option>
                                    <option value='1'
                                            @if($request['search']['followed']=='1')
                                            selected
                                            @endif
                                    >已关注
                                    </option>
                                    <option value='0'
                                            @if($request['search']['followed']=='0')
                                            selected
                                            @endif
                                    >未关注
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group col-xs-12 col-sm-2 col-md-2 col-lg-2">
                            <!--        <label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label">黑名单</label>-->
                            <div class="">
                                <select name='search[isblack]' class='form-control'>
                                    <option value=''>不限黑名单</option>
                                    <option value='0'
                                            @if($request['search']['isblack']=='0')
                                            selected
                                            @endif>否
                                    </option>
                                    <option value='1'
                                            @if($request['search']['isblack']=='1')
                                            selected
                                            @endif>是
                                    </option>
                                </select>
                            </div>
                        </div>


                        <div class="form-group col-xs-12 col-sm-12 col-md-4 col-lg">

                            <div class="time">

                                <select name='search[searchtime]' class='form-control'>
                                    <option value='0'
                                            @if($request['search']['searchtime']=='0')
                                            selected
                                            @endif>注册时间不限
                                    </option>
                                    <option value='1'
                                            @if($request['search']['searchtime']=='1')
                                            selected
                                            @endif>搜索注册时间
                                    </option>
                                </select>
                            </div>
                            <div class="search-select">
                                {!! app\common\helpers\DateRange::tplFormFieldDateRange('search[times]', [
                                'starttime'=>date('Y-m-d H:i', $starttime),
                                'endtime'=>date('Y-m-d H:i',$endtime),
                                'start'=>0,
                                'end'=>0
                                ], true) !!}
                            </div>
                        </div>

                        <div class="form-group  col-xs-12 col-md-12 col-lg-6">
                            <!--<label class="col-xs-12 col-sm-2 col-md-2 col-lg-2 control-label"></label>-->
                            <div class="">
                                <button class="btn btn-success "><i class="fa fa-search"></i> 搜索</button>
                                <button type="button" name="export" value="1" id="export" class="btn btn-default">导出
                                    Excel
                                </button>


                            </div>
                        </div>

                    </form>

                </div>
            </div>
            <div class="clearfix panel-heading">
                <a class="btn btn-info dropdown-toggle" style="height: 35px;margin-top: 5px;color: white;" href="{{yzWebUrl('member.member.add-member')}}">添加会员</a>
                <a class="btn btn-info dropdown-toggle" style="height: 35px;margin-top: 5px;color: white;" href="{{yzWebUrl('member.member.import')}}">会员excel导入</a>
            </div>
            <div class="clearfix" style="min-height: 600px">
                <div class="panel panel-default">
                    <div class="panel-heading">总数：{{$total}};
                    微信公众号会员：<span id="wechat" style="cursor: pointer" onclick="getMemberCount('wechat',2)">...</span> ；
                    微信小程序会员：<span id="mini-app" style="cursor: pointer" onclick="getMemberCount('mini-app',3)">...</span> ；
                    APP会员：<span id="app" style="cursor: pointer" onclick="getMemberCount('app',4)">...</span> ；
                    微信开放平台会员：<span id="wechat-open" style="cursor: pointer" onclick="getMemberCount('wechat-open',5)">...</span> ；
                    手机号绑定会员：<span id="mobile" style="cursor: pointer" onclick="getMemberCount('mobile',1)">...</span> ；
                    @if($is_customers)企业微信会员：<span id="customers" style="cursor: pointer" onclick="getMemberCount('customers',6)">...</span> ；@endif
                </div>
                    <div class="panel-body" style="margin-bottom:20px">
                        <table class="table table-hover " style="overflow:visible">
                            <thead class="navbar-inner">
                            <tr>
                                <th style='width:6%;text-align: center;'>会员ID</th>
                                @if($opencommission)
                                    <th style='width:7%;text-align: center;'>推荐人</th>
                                @endif
                                <th style='width:8%;text-align: center;'>粉丝</th>
                                <th style='width:10%;'>姓名<br/>手机号码</th>
                                <th style='width:8%;'>等级/分组</th>
                                <th style='width:9%;'>注册时间</th>
                                <th style='width:14%;'>积分/余额</th>
                                <th style='width:14%;'>已完成订单</th>
                                <th style='width:17%;'>类型</th>
                                {{--<th style='width:6%'>关注</th>--}}
                                <th style='width:13%'>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list['data'] as $row)
                                <!-- 加个判断，会员标签开启class才为memeber-tag-tr -->
                                <tr  @if(isset($row['has_many_tag']))class="memeber-tag-tr"@endif>
                                    <td style="text-align: center;">   {{$row['uid']}}</td>

                                    @if($opencommission)
                                        <td style="text-align: center;"
                                            @if(!empty($row['yz_member']['parent_id']))
                                            title='ID: {{$row['yz_member']['parent_id']}}'
                                                @endif
                                        >
                                                @if(empty($row['yz_member']['parent_id']))
                                                    <label class='label label-primary'>总店</label>
                                                @else
                                                    @if(!empty($row['yz_member']['agent']['avatar']))
                                                        <img src='{{yz_tomedia($row['yz_member']['agent']['avatar'])}}'
                                                             style='width:30px;height:30px;padding:1px;border:1px solid #ccc'/>
                                                        <br/>
                                                    @endif
                                                    @if(empty($row['yz_member']['inviter']))
                                                            (暂定)
                                                    @endif
                                                    @if(empty($row['yz_member']['agent']['nickname']))
                                                            未更新
                                                    @else
                                                            {{$row['yz_member']['agent']['nickname']}}
                                                    @endif
                                                @endif
                                        </td>
                                    @endif
                                    <td style="text-align: center;">
                                        @if(!empty($row['avatar']))
                                            <img src='{{yz_tomedia($row['avatar'])}}'
                                                 style='width:30px;height:30px;padding:1px;border:1px solid #ccc'/><br/>
                                        @endif
                                        @if(empty($row['nickname']))
                                            未更新
                                        @else
                                            {{$row['nickname']}}
                                        @endif
                                    </td>
                                    <td>{{$row['realname']}}<br/>{{$row['mobile']}}</td>
                                    <td>
                                        @if(empty($row['yz_member']['level']['level_name']))
                                            {{$set['level_name']}}
                                        @else
                                            {{$row['yz_member']['level']['level_name']}}
                                        @endif
                                        <br/>
                                        @if(empty($row['yz_member']['group']['group_name']))
                                            无分组
                                        @else
                                            {{$row['yz_member']['group']['group_name']}}
                                        @endif
                                    </td>
                                    <td>
                                        {{date('Y-m-d',$row['createtime'])}}<br/>
                                        {{date('H:i',$row['createtime'])}}</td>
                                    <td><label class="label label-info">积分：{{$row['credit1']}}</label><br/><label
                                                class="label label-danger">余额：{{$row['credit2']}}</label></td>
                                    <td><label class="label label-info">订单：
                                            @if(!empty($row['has_one_order']['total']))
                                                {{$row['has_one_order']['total']}}
                                            @else
                                                0
                                            @endif</label><br/>
                                        <label class="label label-danger">金额：@if(!empty($row['has_one_order']['sum']))
                                                {{$row['has_one_order']['sum']}}
                                            @else
                                                0
                                            @endif</label></td>
                                    <td>
                                        <!--已关注  -->
                                        @if(!empty($row['has_one_fans']) && $row['has_one_fans']['followed'] == 1)
                                            <i class="iconfont icon-all_wechat_public" title="已关注" style="color:#04af82;font-size:30px"></i>
                                        @elseif(!empty($row['has_one_fans']) && $row['has_one_fans']['followed'] == 0)
                                            <!--未关注  -->
                                            <i class="iconfont icon-all_wechat_public" title="未关注" style="color:#999999;font-size:30px"></i>
                                        @else
                                            {{--<i class="iconfont icon-all_wechat_public" title="未关注" style="color:#999999;font-size:30px"></i>--}}
                                        @endif
                                        @if(!empty($row['has_one_mini_app']))
                                            <i class="iconfont icon-all_smallprogram" title="小程序" style="color:#00b84b;font-size:30px"></i>
                                        @endif
                                        @if(!empty($row['has_one_wechat']))
                                            <i class="iconfont icon-all_app" title="APP" style="color:#0068ff;font-size:30px"></i>
                                        @endif
                                        @if(!empty($row['has_one_unique']))
                                            <i class="iconfont icon-all_wechat" title="微信开放平台" style="color:#0ad76d;font-size:30px"></i>
                                        @endif
                                        @if(!empty($row['has_one_alipay']))
                                            <i class="iconfont icon-all_alipay" title="支付宝" style="color:#069eff;font-size:30px"></i>
                                        @endif
                                        @if(!empty($row['has_one_douyin']))
                                            <i class="iconfont icon-all_trill" title="抖音" style="color:#23042b;font-size:30px"></i>
                                        @endif
                                        @if(!empty($row['has_one_customers']))
                                             <i class="iconfont icon-qiyeweixin01" title="企业微信" style="color:#0082ef;font-size:30px"></i>
                                        @endif

                                    </td>
                                    {{--<td>
                                        @if($row['yz_member']['is_black']==1)
                                            <span class="label label-default"
                                                  style='color:#fff;background:black'>黑名单</span>
                                        @else
                                            @if(empty($row['has_one_fans']['followed']))
                                                <label class='label label-default'>未关注</label>
                                            @else
                                                <label class='label label-success'>已关注</label>
                                            @endif
                                        @endif
                                    </td>--}}


                                    <td  style="overflow:visible;">
                                        <div class="btn-group btn-group-sm">
                                            <a class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="false" href="javascript:;">操作 <span class="caret"></span></a>
                                            <ul id="dropdown-menu-action" class="dropdown-menu dropdown-menu-left" role="menu" style='z-index: 9999'>
                                                <li><a href="{{yzWebUrl('member.member.detail', ['id' => $row['uid']])}}" title="会员详情"><i class='fa fa-edit'></i> 会员详情</a></li>
                                                <li><a href="{{yzWebUrl('member.member-income.index', ['id' => $row['uid']])}}" title="收入记录"><i class='fa fa-edit'></i> 收入详情</a></li>
                                                <li><a href="{{yzWebUrl('order.order-list.index', ['member_id' => $row['uid']])}}" title='会员订单'><i class='fa fa-list'></i> 会员订单</a></li>
                                                <li><a href="{{yzWebUrl('point.recharge.index',['id'=>$row['uid']])}}" title='充值积分'><i class='fa fa-credit-card'></i> 充值积分</a></li>
                                                <li><a href="{{yzWebUrl('balance.recharge.index', ['member_id'=>$row['uid']])}}" title='充值余额'><i class='fa fa-money'></i> 充值余额 </a></li>
                                                <li><a href="{{yzWebUrl('password.page.index', ['member_id'=>$row['uid']])}}" title='支付密码'><i class='fa fa-money'></i> 支付密码 </a></li>
                                                <li><a href="{{yzWebUrl('member.member.agent-old', ['id'=>$row['uid']])}}" title='直推客户'><i class='fa fa-exchange'></i> 直推客户 </a></li>
                                                <li><a href="{{yzWebUrl('member.member.agent', ['id'=>$row['uid']])}}" title='团队客户'><i class='fa fa-exchange'></i> 团队客户 </a></li>
                                                <li><a href="{{yzWebUrl('member.member.agent-parent', ['id'=>$row['uid']])}}" title='上级会员'><i class='fa fa-exchange'></i> 上级会员 </a></li>
                                                @if($row['yz_member']['is_black']==1)
                                                    <li><a href="{{yzWebUrl('member.member.black', ['id' => $row['uid'],'black'=>0])}}" title='取消黑名单'><i class='fa fa-minus-square'></i> 取消黑名单</a></li>
                                                @else
                                                    <li><a href="{{yzWebUrl('member.member.black', ['id' => $row['uid'],'black'=>1])}}" title='设置黑名单'><i class='fa fa-minus-circle'></i> 设置黑名单</a></li>
                                                @endif

                                                <li>
                                                    <a href="{{yzWebUrl('member.bank-card.edit', ['member_id' => $row['uid']])}}"
                                                       title='银行卡管理'><i class='fa fa-credit-card'></i>银行卡管理</a>
                                                </li>
                                                <li>
                                                    <a href="{{yzWebUrl('member.member-address.index', ['member_id' => $row['uid']])}}"
                                                       title='收货地址管理'><i class='fa fa-truck'></i>收货地址管理</a>
                                                </li>
                                                <li>
                                                    {{--<a href="{{yzWebUrl('member.member.delete', ['id'=>$row['uid']])}}"--}}
                                                       {{--onclick="return confirm('确认删除该用户吗？此操作是不可逆的');return false;" title='删除会员'><i class='fa fa-delicious'></i>删除（危险）</a>--}}

                                                    <a href="javascript:return false;" title='禁用中' style="cursor: default;opacity: 0.5"><i class='fa fa-delicious'></i>删除（危险）</a>
                                                </li>
                                            </ul>
                                            @if($row['yz_member']['is_old']==1)
                                                <a class="btn btn-danger" onClick="merge({{$row['uid']}}, {{ $row['mark_member_id'] }})">合并</a>
                                            @endif
                                        </div>
                                    </td>
                                    </td>
                                </tr>
                                @if(isset($row['has_many_tag']))
                                <tr>
                                    <td colspan="10">
                                        <!-- 标签 -->
                                        <div style="display:flex;cursor: pointer;align-items: center;flex-wrap: wrap;" >
                                            @foreach($row['has_many_tag'] as $tag)
                                            <div style="margin-right:10px" class="line_del">
                                                <a href="{{yzWebUrl('member.member.index',['tag_id'=>$tag['tag_id']])}}" style="color:{{$tag['tag']['color']}}">{{$tag['tag']['title']}}</a>
                                                <a class="el-icon-close tip_del" onClick="btnDel({{$tag['tag_id']}},{{$tag['member_id']}})"></a>
                                            </div>
                                            @endforeach
                                            <div style="margin:0 10px 0 5px">
                                                <a class="btn btn-default btn-sm" onclick="openLabel('{{$row['uid']}}')">添加标签</a>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                        {!!$pager!!}

                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="mergeModal" tabindex="-1" role="dialog" aria-labelledby="mergeModalLabel" aria-hidden="true">
        <div class="modal-dialog" style='width: 350px;'>
            <div class="modal-content">
                <div class="modal-header" style="text-align:center">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="mergeModalLabel">合并提示</h4>
                </div>
                <div class="modal-body">
                    该过程不可逆，点击合并后，该会员其他登录凭证(会员ID【<span id="mark_member_id"></span>】)将会合并到会员ID【<span id="member_id"></span>】上，其他会员ID上的订单、余额、积分、佣金、下线等数据将不会被合并，并无法再次访问！
                    <div style="color:red;padding-top:10px">点击确定合并代表您已熟知上述风险，执行会员合并，并且自行承担责任！</div>
                </div>
                <div class="modal-footer" style="text-align:center">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消合并</button>
                    <button type="button" class="btn btn-primary" onClick="confirmMerge()">确认合并</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>
    <div id="re_content">
        <el-dialog title="选择标签" :visible.sync="label_show">
            <el-table :data="label_list" style="width: 100%;height:500px;overflow:auto">
                <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                <el-table-column label="标签名字">
                    <template slot-scope="scope">
                        <div style="display:flex;align-items: center;">
                            <div v-if="scope.row.thumb" style="width:40px;">
                                <el-image :src="scope.row.thumb" alt="" style="width:40px;height:40px;border-radius:50%"></el-image>
                            </div>
                            <div style="flex:1;">[[scope.row.title]]</div>
                        </div>

                    </template>
                </el-table-column>

                <el-table-column prop="refund_time" label="操作" align="center" width="320">
                    <template slot-scope="scope">
                        <el-button @click="chooseLabel(scope.row)">
                            选择
                        </el-button>

                    </template>
                </el-table-column>
            </el-table>
            <span slot="footer" class="dialog-footer">
                <el-button @click="label_show = false">取 消</el-button>
            </span>

        </el-dialog>
    </div>
    <script language='javascript'>
        // 下拉菜单根据屏幕位置向下或向上展开
        $(function(){
            $(".dropdown-toggle").click(function(){
                var myheight = $("#dropdown-menu-action").height()　+　window.event.screenY;
                var screen_heignt = $(document.body).outerHeight(true);
                $('.btn-group').removeClass('dropup dropdown')
                if(myheight > screen_heignt){
                    $(this).parent().addClass('dropup')
                }else{
                    $(this).parent().addClass('dropdown')
                }
            })
        })
        $(function () {
            $('#export').click(function () {
                $('#route').val("member.member.export");
                $('#form1').submit();
                $('#route').val("member.member.index");
            });
        });
        let member_id = 0;
        function merge(id, mark_mid) {
            console.log(id)
            console.log(mark_mid)
            member_id = 0;
            member_id = id;
            $("#member_id").text(member_id)
            $("#mark_member_id").text(mark_mid)
            $('#mergeModal').modal();
        }
        function confirmMerge() {
            // 合并
            var url_open = "{!! yzWebUrl('member.member.memberMerge') !!}"
            $.post(url_open,{uid:member_id},function(data){
                if (data.result == 1) {
                    alert('合并成功');
                    $('#mergeModal').modal('hide')

                } else {
                    alert(data.msg);
                    $('#mergeModal').modal('hide')

                }
                $('#mergeModal').modal('hide')

            }, "json");
        }
        function getMemberCount(id_name,type) {
            let url = "{!! yzWebUrl('member.member.memberChart') !!}"
            $.post(url,{chart_type:type},function(data){
                if (data.result == 1) {
                    $("#"+id_name).text(data.data.count)
                } else {
                    alert(data.msg);

                }

            }, "json");
        }
        function openLabel(uid) {
            console.log(uid);
            vm.label_show = true;
            vm.uid = uid;
        }
        // 点击删除-(添加的标签)
        function btnDel(tag_id,member_id){
            vm.getDel(tag_id,member_id)
        }
        // 鼠标移入移出事件-(是否显示删除标签)
        window.onload = function(){
            let line_del =  document.querySelector('.line_del')
            let all_line_del =  document.querySelectorAll('.line_del')
            // let tip_del = document.querySelector('.tip_del')
            let all_tip_del = document.querySelectorAll('.tip_del')
            for(i = 0;i<all_tip_del.length;i++){
                console.log(i,all_line_del[i],123);
                all_line_del[i].onmouseover = function(){
                    let tip_del = this.querySelector('.tip_del')
                    tip_del.style.display = "block"
                }
                all_line_del[i].onmouseout = function(){
                    let tip_del = this.querySelector('.tip_del')
                    tip_del.style.display = "none"
                }
            }
        }
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    uid:0,
                    label_show:false,
                    label_list:[],
                    total:0,
                    per_page:1,
                    current_page:1,
                }
            },
            watch: {
                label_show(val) {
                    if(val = true) {
                        this.getLabel(1);
                    }
                }
            },
            created () {

            },
            methods: {
                getDel(tag_id,member_id){
                    this.$http.post("{!! yzWebUrl('plugin.member-tags.Backend.controllers.tag.delMemberTags') !!}",{member_id:member_id,tag_id:tag_id}).then(response => {
                        if (response.data.result) {
                            this.$message({type: 'success',message: response.data.msg});
                            window.location.reload();
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    }, response => {
                        this.$message({type: 'error',message: response.data.msg});
                        console.log(response);
                    });
                },
                getLabel(page) {
                    this.$http.post("{!! yzWebUrl('plugin.member-tags.Backend.controllers.tag.handleTagsList') !!}",{page:page}).then(response => {
                        if (response.data.result) {
                            this.label_list=response.data.data.list
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    }, response => {
                        this.$message({type: 'error',message: response.data.msg});
                        console.log(response);
                    });
                },
                chooseLabel(row) {
                    let that = this;
                    this.$http.post("{!! yzWebUrl('plugin.member-tags.Backend.controllers.tag.makeMemberTags') !!}",{tag_id:row.id,member_id:this.uid}).then(response => {
                        if (response.data.result) {
                            // this.label_list=response.data.data.list
                            this.$message({type: 'success',message: '操作成功！'});
                            window.location.reload();
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    }, response => {
                        this.$message({type: 'error',message: response.data.msg});
                        console.log(response);
                    });
                },
            },
        });
    </script>
@endsection