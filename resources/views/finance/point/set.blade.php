@extends('layouts.base')

@section('content')

    <div class="main rightlist">
        <div class="right-titpos">
            @include('layouts.tabs')
        </div>
        <form action="" method="post" class="form-horizontal form" enctype="multipart/form-data">
            <div class="panel panel-default">
                <div class="panel-heading">
                    积分抵扣设置
                </div>
                <div class='panel-body'>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">积分转让</label>
                        <div class="col-sm-9 col-xs-12">
                            <label class="radio-inline">
                                <input type="radio"
                                       name="set[point_transfer]"
                                       value='1'
                                       @if ($set['point_transfer'] == 1) checked @endif
                                />
                                开启
                            </label>
                            <label class="radio-inline">
                                <input type="radio"
                                       name="set[point_transfer]"
                                       value='0'
                                       @if (empty($set['point_transfer'])) checked @endif
                                />
                                关闭
                            </label>
                            <span class='help-block'>积分转让： 会员之间可以进行积分转让</span>
                        </div>
                        @if($set['point_transfer'] == 1)
                            <div id='point_transfer_poundage' class="form-group">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                <div class="col-sm-3">
                                    <div class='input-group'>
                                        <span class='input-group-addon'>手续费</span>
                                        <input type="text" name="set[point_transfer_poundage]"
                                               value="{{$set['point_transfer_poundage']}}"
                                               class="form-control"/>
                                        <span class='input-group-addon'>%</span>
                                    </div>
                                    <span class='help-block'></span>
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">积分明细显示受让人</label>
                        <div class="col-sm-9 col-xs-12">
                            <label class="radio-inline">
                                <input type="radio" name="set[show_transferor]" value='1'
                                       @if ($set['show_transferor'] == 1) checked @endif /> 是
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="set[show_transferor]" value='0'
                                       @if (empty($set['show_transferor'])) checked @endif /> 否
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">积分抵扣</label>
                        <div class="col-sm-9 col-xs-12">
                            <label class="radio-inline">
                                <input type="radio" name="set[point_deduct]" value='1'
                                       @if ($set['point_deduct'] == 1) checked @endif /> 开启
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="set[point_deduct]" value='0'
                                       @if (empty($set['point_deduct'])) checked @endif /> 关闭
                            </label>
                            <span class='help-block'>开启积分抵扣, 商品最多抵扣的数目需要在商品【营销设置】中单独设置, 否则统一设置</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">开启默认积分抵扣</label>
                        <div class="col-sm-9 col-xs-12">
                            <label class="radio-inline">
                                <input type="radio" name="set[default_deduction]" value='1'
                                       @if ($set['default_deduction'] == 1) checked @endif /> 是
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="set[default_deduction]" value='0'
                                       @if (empty($set['default_deduction'])) checked @endif /> 否
                            </label>
                            <span class='help-block'>开启默认积分抵扣提交订单页面会默认开启积分抵扣按钮</span>
                            <span class='help-block'>注:仅支持平台自营和供应商订单</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">积分返还</label>
                        <div class="col-sm-9 col-xs-12">
                            <label class="radio-inline">
                                <input type="radio" name="set[point_rollback]" value='1'
                                       @if ($set['point_rollback'] == 1) checked @endif /> 开启
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="set[point_rollback]" value='0'
                                       @if (empty($set['point_rollback'])) checked @endif /> 关闭
                            </label>
                            <span class='help-block'>开启积分返还： 未付款订单、退款订单关闭订单后，用于抵扣的积分返还到会员积分账户</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">积分抵扣运费</label>
                        <div class="col-sm-9 col-xs-12">
                            <label class="radio-inline">
                                <input type="radio" name="set[point_freight]" value='1'
                                       @if ($set['point_freight'] == 1) checked @endif /> 开启
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="set[point_freight]" value='0'
                                       @if (empty($set['point_freight'])) checked @endif /> 关闭
                            </label>
                            <span class='help-block'>开启积分抵扣运费： 积分可用于抵扣运费</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">积分抵扣比例</label>
                        <div class="col-sm-5">
                            <div class='input-group'>
                                <span class='input-group-addon'>1个积分 抵扣</span>
                                <input type="text" name="set[money]" value="{{$set['money']}}" class="form-control"/>
                                <span class='input-group-addon'>元</span>
                                <input type="hidden" name="set[point]" value="1" class="form-control"/>
                            </div>
                            <span class='help-block'>积分抵扣比例设置</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">商品抵扣</label>
                        <div class="col-sm-3">
                            <div class='input-group'>
                                <span class='input-group-addon'>最多可抵扣</span>
                                <input type="text" name="set[money_max]" value="{{$set['money_max']}}"
                                       class="form-control"/>
                                <span class='input-group-addon'>%</span>
                            </div>
                            <span class='help-block'>商品最高抵扣比例</span>
                        </div>
                        <div class="col-sm-3">
                            <div class='input-group'>
                                <span class='input-group-addon'>最少需抵扣</span>
                                <input type="text" name="set[money_min]" value="{{$set['money_min']}}"
                                       class="form-control"/>
                                <span class='input-group-addon'>%</span>
                            </div>
                            <span class='help-block'>商品最少抵扣比例</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">计算方式</label>
                        <div class="col-sm-9 col-xs-9">
                            <label class="radio-inline">
                                <input type="radio" name="set[deduction_amount_type]" value='0'
                                       @if ($set['deduction_amount_type'] == 0) checked @endif /> 订单价格:(不包括运费及抵扣金额)
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="set[deduction_amount_type]" value='1'
                                       @if ($set['deduction_amount_type'] == 1) checked @endif /> 利润:(订单商品最终价格-商品成本,负数取0。不支持门店、收银台、酒店订单)
                            </label>
                        </div>
                    </div>
                </div>

                @if(YunShop::plugin()->get('love'))
                    <div class="panel-heading">
                        自动转出{{ $set['love_name'] }}
                    </div>
                    {{--                    <div class="form-group">--}}
                    {{--                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">测试按钮</label>--}}
                    {{--                        <div class="col-sm-4 col-xs-6">--}}
                    {{--                            <label class="radio-inline">--}}
                    {{--                                <button><a href="{{ yzWebUrl('finance.point-set.test1') }}">手动转入</a></button>--}}
                    {{--                            </label>--}}
                    {{--                            <label class="radio-inline">--}}
                    {{--                                <button><a href="{{ yzWebUrl('finance.point-set.test2') }}">重置转入时间</a></button>--}}
                    {{--                            </label>--}}
                    {{--                        </div>--}}
                    {{--                    </div>--}}
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">自动转入{{ $set['love_name'] }}</label>
                        <div class="col-sm-4 col-xs-6">
                            <label class="radio-inline">
                                <input type="radio"
                                       name="set[transfer_love]"
                                       value="1"
                                       @if ($set['transfer_love'] == 1) checked="checked" @endif
                                />
                                开启
                            </label>
                            <label class="radio-inline">
                                <input type="radio"
                                       name="set[transfer_love]"
                                       value="0"
                                       @if ($set['transfer_love'] == 0) checked="checked" @endif
                                />
                                关闭
                            </label>
                            <span class='help-block'>会员积分自动转入可用{{ $set['love_name'] }}</span>
                        </div>
                    </div>
                    <div id='transfer_love' @if(empty($set['transfer_love']))style="display:none"@endif>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label">转入周期</label>
                            <div class="col-sm-4 col-xs-6">
                                <label class="radio-inline">
                                    <input type="radio"
                                           name="set[transfer_cycle]"
                                           value="0"
                                           @if (empty($set['transfer_cycle'])) checked="checked" @endif
                                    />
                                    <span>每天</span>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio"
                                           name="set[transfer_cycle]"
                                           value="1"
                                           @if ($set['transfer_cycle'] == '1') checked="checked" @endif
                                    />
                                    <span>每周</span>
                                </label>
                            </div>
                        </div>
                        <div id='activation_time_week' @if($set['transfer_cycle'] != 1)style="display:none"@endif>
                            <div class="form-group">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label">每周激活</label>
                                <div class="col-sm-6 col-xs-6">
                                    <div class='input-group'>
                                        <select name='set[transfer_time_week]' class='form-control'
                                                style="width: 188px;">
                                            @foreach($week_data as $key => $week)
                                                <option value='{{ $key }}'
                                                        @if($key == $set['transfer_time_week']) selected @endif>
                                                    {{ $week }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label">转入时间</label>
                            <div class="col-sm-6 col-xs-6">
                                <div class='input-group'>
                                    <select name='set[transfer_time_hour]' class='form-control' style="width: 188px;">
                                        <option value='0' @if(empty($set['transfer_time_hour'])) selected @endif>
                                            关闭转入
                                        </option>
                                        @foreach($day_data as $key => $day)
                                            <option value='{{ $key }}'
                                                    @if($key == $set['transfer_time_hour']) selected @endif>{{ $day }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label">转入类型</label>
                            <div class="col-sm-4 col-xs-6">
                                <label class="radio-inline">
                                    <input type="radio"
                                           name="set[transfer_compute_mode]"
                                           value="0"
                                           @if (empty($set['transfer_compute_mode'])) checked="checked" @endif
                                    />
                                    <span>固定值</span>
                                </label>
                                <label class="radio-inline">
                                    <input type="radio"
                                           name="set[transfer_compute_mode]"
                                           value="1"
                                           @if ($set['transfer_compute_mode'] == '1') checked="checked" @endif
                                    />
                                    <span>营业额</span>
                                </label>
                                <div class="help-block">
                                    固定值：会员积分 * N% = 转出的积分
                                    <br>
                                    营业额：周期订单总和 * N% / 会员积分持有总量 * 会员持有积分 = 转出的积分
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                            <div class="col-sm-4 col-lg-3">
                                <div class='recharge-items'>
                                    <div class="input-group">
                                        <div class="input-group-addon">自动转入比例</div>
                                        <input type="text" name="set[transfer_love_rate]" class="form-control"
                                               value="{{ $set['transfer_love_rate'] }}" placeholder=""/>
                                        <div class="input-group-addon">%</div>
                                    </div>
                                </div>
                                <div class="help-block">
                                    可以在会员积分页面设置会员独立的转入比例，优先使用独立转入比例
                                    <br>
                                    转入类型为营业额时：会员独立的转入比例失效，使用全局比例设置
                                </div>
                                <div class="help-block">
                                    如果自动转入比例为空、为零，同时会员设置了独立比例，则只自操作有设置比例的会员积分
                                </div>
                                <div class='input-group recharge-item'>
                                    <span class="input-group-addon">积分转入爱心值比例设置</span>
                                    <input type="text"
                                           name="set[transfer_integral]"
                                           value="{{$set['transfer_integral']}}"
                                           class="form-control wid100"
                                    />
                                    <span class='input-group-addon'>:</span>
                                    <input type="text"
                                           name="set[transfer_integral_love]"
                                           value="{{$set['transfer_integral_love']}}"
                                           class="form-control wid100"
                                    />
                                </div>
                                <div class="help-block">
                                    如果积分转入爱心值比例设置为空、为零，则默认为1：1
                                </div>
                            </div>
                        </div>
                    </div>
                @endif


                <div class="panel-heading">
                    积分赠送设置
                </div>
                <div class='panel-body'>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">购买商品赠送规则</label>
                        <div class="col-sm-9 col-xs-12">
                            <label class="radio-inline">
                                <input type="radio" name="set[give_type]" value='0' @if (empty($set['give_type'])) checked @endif />
                                <span>实付金额</span>
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="set[give_type]" value='1' @if ($set['give_type'] == 1) checked @endif />
                                <span>商品利润</span>
                            </label>
                            <span class='help-block'>
                                实付金额:订单商品实际支付金额
                                <br>
                                商品利润:订单商品实际支付金额-商品成本,负数取0。门店和收银台利润按平台提成计算
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">购买商品赠送积分</label>
                        <div class="col-sm-5">
                            <div class='input-group'>
                                <span class='input-group-addon'>购买商品赠送</span>
                                <input type="text" name="set[give_point]" value="{{$set['give_point']}}" class="form-control"/>
                                <span class='input-group-addon'>积分</span>
                            </div>
                            <span class='help-block'>
                                例: 购买2件，设置10 积分, 不管成交价格是多少， 赠送积分 = 20积分
                                <br>
                                例: 购买2件，设置10%积分, 赠送积分 = 实付金额 * 2 * 10% 或 商品利润 * 2 * 10%
                            </span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">消费赠送类型</label>
                        <div class="col-sm-9 col-xs-12">
                            <label class="radio-inline">
                                <input type="radio" name="set[point_award_type]" value='1'
                                       @if ($set['point_award_type'] == 1) checked @endif /> 百分比
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="set[point_award_type]" value='0'
                                       @if (empty($set['point_award_type'])) checked @endif /> 固定数值
                            </label>
                            <span class='help-block'>
                                百分比:单笔订单满200元, 设置10积分, 成交价格200元, 则购买后获得 20 积分（200*10%）
                                <br>
                                固定数值:单笔订单满200元, 设置10积分, 成交价格200元, 则购买后获得 10 积分
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">消费赠送</label>
                        <div class="col-sm-4 col-lg-3">
                        <span class="help-block">两项都填写才能生效 <span
                                    style="color:green; font-weight:bold">且阶梯优先级最大</span></span>
                            <div class='input-group'>
                                <span class="input-group-addon">单笔订单满</span>
                                <input type="text" name="set[enough_money]" value="{{$set['enough_money']}}"
                                       class="form-control wid100"/>
                                <span class='input-group-addon'>元 赠送</span>
                                <input type="text" name="set[enough_point]" value="{{$set['enough_point']}}"
                                       class="form-control wid100"/>
                                <span class='input-group-addon'>积分</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-4 col-lg-3">
                            <div class='recharge-items'>
                                @foreach ($set['enoughs'] as $item)
                                    <div class="input-group recharge-item" style="margin-top:5px">
                                        <span class="input-group-addon">单笔订单满</span>
                                        <input type="text" class="form-control  wid100" name='enough[]'
                                               value='{{$item['enough']}}'/>
                                        <span class="input-group-addon">元 赠送</span>
                                        <input type="text" class="form-control wid100" name='give[]'
                                               value='{{$item['give']}}'/>
                                        <span class="input-group-addon">积分</span>
                                        <div class='input-group-btn'>
                                            <button class='btn btn-danger' type='button'
                                                    onclick="removeConsumeItem(this)"><i class='fa fa-remove'></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div style="margin-top:5px">
                                <button type='button' class="btn btn-default" onclick='addConsumeItem()'
                                        style="margin-bottom:5px"><i class='fa fa-plus'></i> 增加项
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-heading">
                    绑定手机号奖励设置
                </div>
                <div class='panel-body'>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">
                            绑定手机号奖励设置
                        </label>
                        <div class="col-sm-4 col-xs-6">
                            <label class="radio-inline">
                                <input type="radio"
                                       name="set[bind_mobile_award]"
                                       value="1"
                                       @if ($set['bind_mobile_award'] == 1) checked="checked" @endif />
                                <span>开启</span>
                            </label>
                            <label class="radio-inline">
                                <input type="radio"
                                       name="set[bind_mobile_award]"
                                       value="0"
                                       @if (empty($set['bind_mobile_award'])) checked="checked" @endif />
                                <span>关闭</span>
                            </label>
                            <span class='help-block'>
                                开启该功能后，用户绑定手机号可获得一次性奖励（包含手机号注册会员）
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-2">
                            <div class='input-group'>
                                <input type="text"
                                       class="form-control"
                                       name="set[bind_mobile_award_point]"
                                       value="{{$set['bind_mobile_award_point']}}" />
                                <span class='input-group-addon'>积分</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-heading">
                    <span>积分奖励设置</span>
                </div>
                <div class='panel-body'>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">收入提现赠送：</label>
                        <div class="col-sm-4 col-xs-6">
                            <label class="radio-inline">
                                <input type="radio"
                                       name="set[income_withdraw_award]"
                                       value="1"
                                       @if ($set['income_withdraw_award'] == 1) checked="checked" @endif
                                />
                                <span>开启</span>
                            </label>
                            <label class="radio-inline">
                                <input type="radio"
                                       name="set[income_withdraw_award]"
                                       value="0"
                                       @if (empty($set['income_withdraw_award'])) checked="checked" @endif
                                />
                                <span>关闭</span>
                            </label>
                            <span class='help-block'>
                                收入提现：收入提现奖励提现手续费等值积分【比例1：1】
                            </span>
                        </div>
                    </div>
                </div>

                <div class='panel-body'>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">收入提现赠送比例：</label>
                        <div class="col-sm-4 col-xs-6">
                            <label class="radio-inline">
                                <input type="radio"
                                       name="set[income_withdraw_award_scale]"
                                       value="1"
                                       @if ($set['income_withdraw_award_scale'] == 1) checked="checked" @endif
                                />
                                <span>开启</span>
                            </label>
                            <label class="radio-inline">
                                <input type="radio"
                                       name="set[income_withdraw_award_scale]"
                                       value="0"
                                       @if (empty($set['income_withdraw_award_scale'])) checked="checked" @endif
                                />
                                <span>关闭</span>
                            </label>
                            <span class='help-block'>
                                奖励积分=收入提现审核金额x设置比例
                            </span>
                        </div>
                    </div>
                    <div class="form-group" id="income_withdraw_award_scale_point">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-2">
                            <div class='input-group'>
                                <input type="text"
                                       class="form-control"
                                       name="set[income_withdraw_award_scale_point]"
                                       value="{{$set['income_withdraw_award_scale_point']}}" />
                                <span class='input-group-addon'>%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel-heading">
                    积分不足消息提醒设置
                </div>
                <div class='panel-body'>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">积分不足消息通知开关</label>
                        <div class="col-sm-4 col-xs-6">
                            <label class="radio-inline">
                                <input type="radio" name="set[point_floor_on]" value="1"
                                       @if ($set['point_floor_on'] == 1) checked="checked" @endif />
                                开启
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="set[point_floor_on]" value="0"
                                       @if ($set['point_floor_on'] == 0) checked="checked" @endif />
                                关闭
                            </label>
                        </div>
                    </div>

                    <div id="point_floor_on" @if($set['point_floor_on'] !=1 ) style="display:none" @endif>
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label">积分不足</label>
                            <div class="col-sm-2">
                                <div class='input-group'>
                                    <input type="text" name="set[point_floor]" value="{{$set['point_floor']}}"
                                           class="form-control"/>
                                    <span class='input-group-addon'>积分</span>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label">消息通知类型</label>
                            <div class="col-sm-9 col-xs-12">
                                <label class="radio-inline">
                                    <input type="radio" name="set[point_message_type]" value='1' onclick='showtype(1)'
                                           @if ($set['point_message_type'] == 1) checked @endif /> 指定会员
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="set[point_message_type]" onclick='showtype(2)' value='2'
                                           @if ($set['point_message_type'] == 2) checked @endif /> 指定会员等级
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="set[point_message_type]" onclick='showtype(3)' value='3'
                                           @if ($set['point_message_type'] == 3) checked @endif /> 指定会员分组
                                </label>
                            </div>
                        </div>

                        <div class="form-group showtype showtype2"
                             @if($set['point_message_type'] != 2) style="display: none" @endif>
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label">使用条件 - 会员等级</label>
                            <div class="col-sm-2">
                                <select name="set[level_limit]" class="form-control">
                                    @foreach($memberLevels as $v)
                                        <option value="{{$v['id']}}"
                                                @if($set['level_limit']==$v['id']) selected @endif>{{$v['level_name']}}
                                            (发送消息通知)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group showtype showtype3"
                             @if($set['point_message_type'] != 3) style="display: none" @endif>
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label">使用条件 - 会员等级</label>
                            <div class="col-sm-2">
                                <select name="set[group_type]" class="form-control">
                                    @foreach($memberGroups as $v)
                                        <option value="{{$v['id']}}"
                                                @if($set['group_type']==$v['id']) selected @endif>{{$v['group_name']}}
                                            (发送消息通知)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group showtype showtype1"
                             @if($set['point_message_type'] != 1) style="display: none" @endif>
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label">选择指定会员：</label>
                            <div class='input-group' style="width: 50%">
                                <input type="text" name="set[uids]" value="{{$set['uids']}}"
                                       class="form-control"/>
                            </div>
                            <span style="margin-left: 17%" class='help-block'>请填写会员id，会员id之间用英文逗号隔开</span>

                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                        <div class="col-sm-9 col-xs-12">
                            <input type="submit" name="submit" value="提交" class="btn btn-primary col-lg-1"/>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script language='javascript'>
        function search_members() {
            if ($('#search-kwd-notice').val() == '') {
                Tip.focus('#search-kwd-notice', '请输入关键词');
                return;
            }
            $("#module-menus-notice").html("正在搜索....");
            $.get("{!! yzWebUrl('member.member.get-search-member') !!}", {
                keyword: $.trim($('#search-kwd-notice').val())
            }, function (dat) {
                $('#module-menus-notice').html(dat);
            });
        }

        function select_member(o) {
            $("#uid").val(o.uid);
            $("#saleravatar").show();
            $("#saleravatar").find('img').attr('src', o.avatar);
            $("#saler").val(o.nickname + "/" + o.realname + "/" + o.mobile);
            $("#modal-module-menus-notice .close").click();
        }

        function showtype(type) {
            $('.showtype').hide();
            $('.showtype' + type).show();
        }

        function addConsumeItem() {
            var html = '<div class="input-group recharge-item"  style="margin-top:5px">';
            html += '<span class="input-group-addon">单笔订单满</span>';
            html += '<input type="text" class="form-control wid100" name="enough[]"  />';
            html += '<span class="input-group-addon">元 赠送</span>';
            html += '<input type="text" class="form-control wid100"  name="give[]"  />';
            html += '<span class="input-group-addon">积分</span>';
            html += '<div class="input-group-btn"><button class="btn btn-danger" onclick="removeConsumeItem(this)"><i class="fa fa-remove"></i></button></div>';
            html += '</div>';
            $('.recharge-items').append(html);
        }

        function removeConsumeItem(obj) {
            $(obj).closest('.recharge-item').remove();
        }


    </script>
    <script language="javascript">
        $(function () {
            $(":radio[name='set[recharge]']").click(function () {
                if ($(this).val() == 1) {
                    $("#recharge").show();
                } else {
                    $("#recharge").hide();
                }
            });

            $(":radio[name='set[point_floor_on]']").click(function () {

                if ($(this).val() == 1) {
                    $("#point_floor_on").show();
                } else {
                    $("#point_floor_on").hide();
                }
            });

            $(":radio[name='set[withdraw][status]']").click(function () {
                if ($(this).val() == 1) {
                    $("#withdraw").show();
                } else {
                    $("#withdraw").hide();
                }
            });
            $(":radio[name='set[transfer_balance]']").click(function () {
                if ($(this).val() == 1) {
                    $("#transfer_balance").show();
                } else {
                    $("#transfer_balance").hide();
                }
            });
            $(":radio[name='set[transfer_balance_cycle]']").click(function () {
                if ($(this).val() == 1) {
                    $("#transfer_balance_week").show();
                } else {
                    $("#transfer_balance_week").hide();
                }
            });
            $(":radio[name='set[transfer_love]']").click(function () {
                if ($(this).val() == 1) {
                    $("#transfer_love").show();
                } else {
                    $("#transfer_love").hide();
                }
            });
            $(":radio[name='set[transfer_cycle]']").click(function () {
                if ($(this).val() == 1) {
                    $("#activation_time_week").show();
                } else {
                    $("#activation_time_week").hide();
                }
            });
            $(":radio[name='set[point_transfer]']").click(function () {
                if ($(this).val() == 1) {
                    $("#point_transfer_poundage").show();
                } else {
                    $("#point_transfer_poundage").hide();
                }
            });

            
        })
    </script>


@endsection