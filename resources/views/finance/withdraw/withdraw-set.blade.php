@extends('layouts.base')

@section('content')
@section('title', trans('提现设置'))
<script>
    $(function () {
        $("#myTab li.active>a").css("background", "#ccc");
    })
    window.optionchanged = false;
    require(['bootstrap'], function () {
        $('#myTab a').click(function (e) {
            e.preventDefault();
            $(this).tab('show');
            $(this).css("background", "#ccc").parent().siblings().children().css("background", "none")
        })
    });
</script>
<style> .add-snav > li > a {
        height: 46px !important
    }</style>
<link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
<div class="main rightlist">
    <div>
        <ul class="add-shopnav" id="myTab">
            <li class="active"><a href="#tab_balance">余额提现</a></li>

            @foreach(\app\common\modules\widget\Widget::current()->getItem('withdraw') as $key=>$value)
                <li><a href="#{{$key}}">{{$value['title']}}</a></li>
            @endforeach

        </ul>
    </div>
    <form action="" method="post" class="form-horizontal form" enctype="multipart/form-data">
        <div class="panel panel-default">
            <div class='panel-body'></div>
            <div class='panel-body'>
                <div class="tab-content">
                    <div class="tab-pane  active" id="tab_balance">
                        {{--余额提现 start--}}
                        <div class="form-group">
                            <label class="col-xs-12 col-sm-3 col-md-2 control-label">开启余额提现</label>
                            <div class="col-sm-9 col-xs-12">
                                <label class='radio-inline'>
                                    <input type='radio' name='withdraw[balance][status]' value='1'
                                           @if($set['status'] == 1) checked @endif />
                                    <span>开启</span>
                                </label>
                                <label class='radio-inline'>
                                    <input type='radio' name='withdraw[balance][status]' value='0'
                                           @if($set['status'] == 0) checked @endif />
                                    <span>关闭</span>
                                </label>
                                <span class='help-block'>是否允许用户将余额提出</span>
                            </div>
                        </div>

                        <div id='withdraw' @if(empty($set['status']))style="display:none"@endif>
                            <div class="form-group">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                <div class="col-sm-9 col-xs-12">
                                    <div class="wechat">
                                        <label class='radio-inline' style="padding-left:0px">提现到微信</label>
                                    </div>
                                    <div class="switch">
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][wechat]' value='1'
                                                   @if($set['wechat'] == 1) checked @endif />
                                            <span>开启</span>
                                        </label>
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][wechat]' value='0'
                                                   @if($set['wechat'] == 0) checked @endif />
                                            <span>关闭</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div id='withdraw_balance_wechat' @if(empty($set['wechat']))style="display:none"@endif>
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'></label>
                                        </div>
                                        <div class="cost">
                                            <label class='radio-inline'>
                                                <div class="input-group">
                                                    <div class="input-group-addon" style="width: 120px;">
                                                        <span>单笔最低金额</span>
                                                    </div>
                                                    <input type="text"
                                                           name="withdraw[balance][wechat_min]"
                                                           class="form-control"
                                                           value="{{ $set['wechat_min'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                    <div class="input-group-addon">
                                                        <span>单笔最高金额</span>
                                                    </div>
                                                    <input type="text"
                                                           name="withdraw[balance][wechat_max]"
                                                           class="form-control"
                                                           value="{{ $set['wechat_max'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline' style="padding-left:0px"></label>
                                        </div>
                                        <div class="cost">
                                            <label class='radio-inline'>
                                                <div class="input-group">
                                                    <div class="input-group-addon" style="width: 120px;">每日提现次数</div>
                                                    <input type="text"
                                                           name="withdraw[balance][wechat_frequency]"
                                                           class="form-control"
                                                           value="{{ $set['wechat_frequency'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                    <div class="input-group-addon">次</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                <div class="col-sm-9 col-xs-12">
                                    <div class="alipay">
                                        <label class='radio-inline'>提现到支付宝</label>
                                    </div>
                                    <div class="switch">
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][alipay]' value='1'
                                                   @if($set['alipay'] == 1) checked @endif />
                                            开启
                                        </label>
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][alipay]' value='0'
                                                   @if($set['alipay'] == 0) checked @endif />
                                            关闭
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div id='withdraw_balance_alipay' @if(empty($set['alipay']))style="display:none"@endif>
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'></label>
                                        </div>
                                        <div class="cost">
                                            <label class='radio-inline'>
                                                <div class="input-group">
                                                    <div class="input-group-addon" style="width: 120px;">
                                                        <span>单笔最低金额</span>
                                                    </div>
                                                    <input type="text"
                                                           name="withdraw[balance][alipay_min]"
                                                           class="form-control"
                                                           value="{{ $set['alipay_min'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                    <div class="input-group-addon">
                                                        <span>单笔最高金额</span>
                                                    </div>
                                                    <input type="text"
                                                           name="withdraw[balance][alipay_max]"
                                                           class="form-control"
                                                           value="{{ $set['alipay_max'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline' style="padding-left:0px"></label>
                                        </div>
                                        <div class="cost">
                                            <label class='radio-inline'>
                                                <div class="input-group">
                                                    <div class="input-group-addon" style="width: 120px;">每日提现次数</div>
                                                    <input type="text"
                                                           name="withdraw[balance][alipay_frequency]"
                                                           class="form-control"
                                                           value="{{ $set['alipay_frequency'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                    <div class="input-group-addon">次</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(app('plugins')->isEnabled('huanxun'))
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'>提现到环迅支付</label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][huanxun]' value='1'
                                                       @if($set['huanxun'] == 1) checked @endif />
                                                开启
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][huanxun]' value='0'
                                                       @if($set['huanxun'] == 0) checked @endif />
                                                关闭
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(app('plugins')->isEnabled('eup-pay'))
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'>提现到EUP</label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][eup_pay]' value='1'
                                                       @if($set['eup_pay'] == 1) checked @endif />
                                                开启
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][eup_pay]' value='0'
                                                       @if($set['eup_pay'] == 0) checked @endif />
                                                关闭
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(app('plugins')->isEnabled('converge_pay'))
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'>提现到汇聚支付</label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][converge_pay]' value='1'
                                                       @if($set['converge_pay'] == 1) checked @endif />
                                                开启
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][converge_pay]' value='0'
                                                       @if($set['converge_pay'] == 0) checked @endif />
                                                关闭
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div id='withdraw_balance_converge_pay' @if(empty($set['converge_pay']))style="display:none"@endif>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline'></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">
                                                            <span>单笔最低金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][converge_pay_min]"
                                                               class="form-control"
                                                               value="{{ $set['converge_pay_min'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">
                                                            <span>单笔最高金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][converge_pay_max]"
                                                               class="form-control"
                                                               value="{{ $set['converge_pay_max'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline' style="padding-left:0px"></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">每日提现次数</div>
                                                        <input type="text"
                                                               name="withdraw[balance][converge_pay_frequency]"
                                                               class="form-control"
                                                               value="{{ $set['converge_pay_frequency'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">次</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(app('plugins')->isEnabled('high-light') && \Yunshop\HighLight\services\SetService::getStatus())
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'>提现到微信-高灯</label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][high_light_wechat]' value='1' @if($set['high_light_wechat'] == 1) checked @endif />
                                                开启
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][high_light_wechat]' value='0' @if($set['high_light_wechat'] == 0) checked @endif />
                                                关闭
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'>提现到支付宝-高灯</label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][high_light_alipay]' value='1' @if($set['high_light_alipay'] == 1) checked @endif />
                                                开启
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][high_light_alipay]' value='0' @if($set['high_light_alipay'] == 0) checked @endif />
                                                关闭
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'>提现到银行卡-高灯</label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][high_light_bank]' value='1' @if($set['high_light_bank'] == 1) checked @endif />
                                                开启
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][high_light_bank]' value='0' @if($set['high_light_bank'] == 0) checked @endif />
                                                关闭
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(app('plugins')->isEnabled('eplus-pay') && \Yunshop\EplusPay\services\SettingService::usable())
                            <div class="form-group">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                <div class="col-sm-9 col-xs-12">
                                    <div class="alipay">
                                        <label class='radio-inline'>提现到银行卡-智E+</label>
                                    </div>
                                    <div class="switch">
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][eplus_withdraw_bank]' value='1' @if($set['eplus_withdraw_bank'] == 1) checked @endif />
                                            开启
                                        </label>
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][eplus_withdraw_bank]' value='0' @if(empty($set['eplus_withdraw_bank'])) checked @endif />
                                            关闭
                                        </label>
                                    </div>
                                </div>
                            </div>
                                <div id='withdraw_balance_eplus_withdraw_bank' @if(empty($set['eplus_withdraw_bank']))style="display:none"@endif>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline'></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">
                                                            <span>单笔最低金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][eplus_withdraw_bank_min]"
                                                               class="form-control"
                                                               value="{{ $set['eplus_withdraw_bank_min'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">
                                                            <span>单笔最高金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][eplus_withdraw_bank_max]"
                                                               class="form-control"
                                                               value="{{ $set['eplus_withdraw_bank_max'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline' style="padding-left:0px"></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">每日提现次数</div>
                                                        <input type="text"
                                                               name="withdraw[balance][eplus_withdraw_bank_frequency]"
                                                               class="form-control"
                                                               value="{{ $set['eplus_withdraw_bank_frequency'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">次</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(\app\common\services\finance\IncomeService::workerWithdrawEnable(2))
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'>提现到微信-好灵工</label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][worker_withdraw_wechat]' value='1' @if($set['worker_withdraw_wechat'] == 1) checked @endif />
                                                开启
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][worker_withdraw_wechat]' value='0' @if($set['worker_withdraw_wechat'] == 0) checked @endif />
                                                关闭
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div id='withdraw_balance_worker_withdraw_wechat' @if(empty($set['worker_withdraw_wechat']))style="display:none"@endif>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline'></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">
                                                            <span>单笔最低金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][worker_withdraw_wechat_min]"
                                                               class="form-control"
                                                               value="{{ $set['worker_withdraw_wechat_min'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">
                                                            <span>单笔最高金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][worker_withdraw_wechat_max]"
                                                               class="form-control"
                                                               value="{{ $set['worker_withdraw_wechat_max'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline' style="padding-left:0px"></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">每日提现次数</div>
                                                        <input type="text"
                                                               name="withdraw[balance][worker_withdraw_wechat_frequency]"
                                                               class="form-control"
                                                               value="{{ $set['worker_withdraw_wechat_frequency'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">次</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if(\app\common\services\finance\IncomeService::workerWithdrawEnable(1))
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'>提现到支付宝-好灵工</label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][worker_withdraw_alipay]' value='1' @if($set['worker_withdraw_alipay'] == 1) checked @endif />
                                                开启
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][worker_withdraw_alipay]' value='0' @if($set['worker_withdraw_alipay'] == 0) checked @endif />
                                                关闭
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div id='withdraw_balance_worker_withdraw_alipay' @if(empty($set['worker_withdraw_alipay']))style="display:none"@endif>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline'></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">
                                                            <span>单笔最低金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][worker_withdraw_alipay_min]"
                                                               class="form-control"
                                                               value="{{ $set['worker_withdraw_alipay_min'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">
                                                            <span>单笔最高金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][worker_withdraw_alipay_max]"
                                                               class="form-control"
                                                               value="{{ $set['worker_withdraw_alipay_max'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline' style="padding-left:0px"></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">每日提现次数</div>
                                                        <input type="text"
                                                               name="withdraw[balance][worker_withdraw_alipay_frequency]"
                                                               class="form-control"
                                                               value="{{ $set['worker_withdraw_alipay_frequency'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">次</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'>提现到银行卡-好灵工</label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][worker_withdraw_bank]' value='1' @if($set['worker_withdraw_bank'] == 1) checked @endif />
                                                开启
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][worker_withdraw_bank]' value='0' @if($set['worker_withdraw_bank'] == 0) checked @endif />
                                                关闭
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div id='withdraw_balance_worker_withdraw_bank' @if(empty($set['worker_withdraw_bank']))style="display:none"@endif>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline'></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">
                                                            <span>单笔最低金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][worker_withdraw_bank_min]"
                                                               class="form-control"
                                                               value="{{ $set['worker_withdraw_bank_min'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">
                                                            <span>单笔最高金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][worker_withdraw_bank_max]"
                                                               class="form-control"
                                                               value="{{ $set['worker_withdraw_bank_max'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline' style="padding-left:0px"></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">每日提现次数</div>
                                                        <input type="text"
                                                               name="withdraw[balance][worker_withdraw_bank_frequency]"
                                                               class="form-control"
                                                               value="{{ $set['worker_withdraw_bank_frequency'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">次</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endif

                            <div class="form-group" style="margin-bottom: 30px;">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                <div class="col-sm-9 col-xs-12">
                                    <div class="alipay">
                                        <label class='radio-inline'>{{\Setting::get('shop.lang.zh_cn.income.manual_withdrawal')?:'手动提现'}}</label>
                                    </div>
                                    <div class="switch">
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][balance_manual]' value='1'
                                                   @if($set['balance_manual'] == 1) checked @endif />
                                            <span>开启</span>
                                        </label>
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][balance_manual]' value='0'
                                                   @if($set['balance_manual'] == 0) checked @endif />
                                            <span>关闭</span>
                                        </label>
                                        <span class='help-block'>手动提现包含 银行卡、微信号、支付宝等三种类型，会员需要完善对应资料才可以提现</span>
                                    </div>
                                </div>
                            </div>
                            <div id='balance_manual' @if(empty($set['balance_manual']))style="display:none"@endif>
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'></label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][balance_manual_type]'
                                                       value='1'
                                                       @if(empty($set['balance_manual_type']) || $set['balance_manual_type'] == 1) checked @endif />
                                                银行卡
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][balance_manual_type]'
                                                       value='2' @if($set['balance_manual_type'] == 2) checked @endif />
                                                微信
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][balance_manual_type]'
                                                       value='3' @if($set['balance_manual_type'] == 3) checked @endif />
                                                支付宝
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'></label>
                                        </div>
                                        <div class="cost">
                                            <label class='radio-inline'>
                                                <div class="input-group">
                                                    <div class="input-group-addon" style="width: 120px;">
                                                        <span>单笔最低金额</span>
                                                    </div>
                                                    <input type="text"
                                                           name="withdraw[balance][manual_min]"
                                                           class="form-control"
                                                           value="{{ $set['manual_min'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                    <div class="input-group-addon">
                                                        <span>单笔最高金额</span>
                                                    </div>
                                                    <input type="text"
                                                           name="withdraw[balance][manual_max]"
                                                           class="form-control"
                                                           value="{{ $set['manual_max'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline' style="padding-left:0px"></label>
                                        </div>
                                        <div class="cost">
                                            <label class='radio-inline'>
                                                <div class="input-group">
                                                    <div class="input-group-addon" style="width: 120px;">每日提现次数</div>
                                                    <input type="text"
                                                           name="withdraw[balance][manual_frequency]"
                                                           class="form-control"
                                                           value="{{ $set['manual_frequency'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                    <div class="input-group-addon">次</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            @if(app('plugins')->isEnabled('silver-point-pay'))
                            <!-- 提现到银典支付 start -->
                            <div class="form-group">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                <div class="col-sm-9 col-xs-12">
                                    <div class="wechat">
                                        <label class='radio-inline' style="padding-left:0px">提现到银典支付</label>
                                    </div>
                                    <div class="switch">
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][silver_point]' value='1' @if($set['silver_point'] == 1) checked @endif />
                                            <span>开启</span>
                                        </label>
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][silver_point]' value='0' @if($set['silver_point'] == 0) checked @endif />
                                            <span>关闭</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div id='silver_point' @if(empty($set['silver_point']))style="display:none"@endif>
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline'></label>
                                        </div>
                                        <div class="cost">
                                            <label class='radio-inline'>
                                                <div class="input-group">
                                                    <div class="input-group-addon" style="width: 120px;">
                                                        <span>单笔最低金额</span>
                                                    </div>
                                                    <input type="text"
                                                           name="withdraw[balance][silver_point_min]"
                                                           class="form-control"
                                                           value="{{ $set['silver_point_min'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                    <div class="input-group-addon">
                                                        <span>单笔最高金额</span>
                                                    </div>
                                                    <input type="text"
                                                           name="withdraw[balance][silver_point_max]"
                                                           class="form-control"
                                                           value="{{ $set['silver_point_max'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="alipay">
                                            <label class='radio-inline' style="padding-left:0px"></label>
                                        </div>
                                        <div class="cost">
                                            <label class='radio-inline'>
                                                <div class="input-group">
                                                    <div class="input-group-addon" style="width: 120px;">每日提现次数</div>
                                                    <input type="text"
                                                           name="withdraw[balance][silver_point_frequency]"
                                                           class="form-control"
                                                           value="{{ $set['silver_point_frequency'] ?? ''}}"
                                                           placeholder="为0为空则不限制"
                                                    />
                                                    <div class="input-group-addon">次</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 提现到银典支付 end -->
                            @endif

                            @if(app('plugins')->isEnabled('jianzhimao-withdraw'))
                                <!-- 提现到兼职猫-银行卡 start -->
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="wechat">
                                            <label class='radio-inline' style="padding-left:0px">提现到兼职猫-银行卡</label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][jianzhimao_bank]' value='1' @if($set['jianzhimao_bank'] == 1) checked @endif />
                                                <span>开启</span>
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][jianzhimao_bank]' value='0' @if($set['jianzhimao_bank'] == 0) checked @endif />
                                                <span>关闭</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div id='jianzhimao_bank' @if(empty($set['jianzhimao_bank']))style="display:none"@endif>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline'></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">
                                                            <span>单笔最低金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][jianzhimao_bank_min]"
                                                               class="form-control"
                                                               value="{{ $set['jianzhimao_bank_min'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">
                                                            <span>单笔最高金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][jianzhimao_bank_max]"
                                                               class="form-control"
                                                               value="{{ $set['jianzhimao_bank_max'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline' style="padding-left:0px"></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">每日提现次数</div>
                                                        <input type="text"
                                                               name="withdraw[balance][jianzhimao_bank_frequency]"
                                                               class="form-control"
                                                               value="{{ $set['jianzhimao_bank_frequency'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">次</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- 提现到兼职猫-银行卡 end -->
                            @endif

                            @if(app('plugins')->isEnabled('tax-withdraw'))
                                <!-- 提现到税筹提现-银行卡 start -->
                                <div class="form-group">
                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                    <div class="col-sm-9 col-xs-12">
                                        <div class="wechat">
                                            <label class='radio-inline' style="padding-left:0px">提现到@php echo TAX_WITHDRAW_DIY_NAME; @endphp -银行卡</label>
                                        </div>
                                        <div class="switch">
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][tax_withdraw_bank]' value='1' @if($set['tax_withdraw_bank'] == 1) checked @endif />
                                                <span>开启</span>
                                            </label>
                                            <label class='radio-inline'>
                                                <input type='radio' name='withdraw[balance][tax_withdraw_bank]' value='0' @if($set['tax_withdraw_bank'] == 0) checked @endif />
                                                <span>关闭</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div id='tax_withdraw_bank' @if(empty($set['tax_withdraw_bank']))style="display:none"@endif>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline'></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">
                                                            <span>单笔最低金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][tax_withdraw_bank_min]"
                                                               class="form-control"
                                                               value="{{ $set['tax_withdraw_bank_min'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">
                                                            <span>单笔最高金额</span>
                                                        </div>
                                                        <input type="text"
                                                               name="withdraw[balance][tax_withdraw_bank_max]"
                                                               class="form-control"
                                                               value="{{ $set['tax_withdraw_bank_max'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                        <div class="col-sm-9 col-xs-12">
                                            <div class="alipay">
                                                <label class='radio-inline' style="padding-left:0px"></label>
                                            </div>
                                            <div class="cost">
                                                <label class='radio-inline'>
                                                    <div class="input-group">
                                                        <div class="input-group-addon" style="width: 120px;">每日提现次数</div>
                                                        <input type="text"
                                                               name="withdraw[balance][tax_withdraw_bank_frequency]"
                                                               class="form-control"
                                                               value="{{ $set['tax_withdraw_bank_frequency'] ?? ''}}"
                                                               placeholder="为0为空则不限制"
                                                        />
                                                        <div class="input-group-addon">次</div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- 提现到税筹提现-银行卡 end -->
                            @endif

                            <div class="form-group">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                <div class="col-sm-9 col-xs-12">
                                    <div class="alipay">
                                        <label class='radio-inline'>提现手续费</label>
                                    </div>
                                    <div class="switch">
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][poundage_type]' value='1'
                                                   @if($set['poundage_type'] == 1) checked @endif />
                                            固定金额
                                        </label>
                                        <label class='radio-inline'>
                                            <input type='radio' name='withdraw[balance][poundage_type]' value='0'
                                                   @if(empty($set['poundage_type'])) checked @endif />
                                            手续费比例
                                        </label>

                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                <div class="col-sm-9 col-xs-12">
                                    <div class="alipay">
                                        <label class='radio-inline'></label>
                                    </div>
                                    <div class="cost">
                                        <label class='radio-inline'>
                                            <div class="input-group">
                                                <div class="input-group-addon" id="poundage_hint"
                                                     style="width: 120px;">@if($set['poundage_type'] == 1) 固定金额 @else
                                                        手续费比例 @endif</div>
                                                <input type="text" name="withdraw[balance][poundage]"
                                                       class="form-control" value="{{ $set['poundage'] ?? ''}}"
                                                       placeholder="请输入提现手续费计算值"/>
                                                <div class="input-group-addon"
                                                     id="poundage_unit">@if($set['poundage_type'] == 1) 元 @else
                                                        % @endif</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                <div class="col-sm-9 col-xs-12">
                                    <div class="alipay">
                                        <label class='radio-inline'></label>
                                    </div>
                                    <div class="cost">
                                        <label class='radio-inline'>
                                            <div class="input-group">
                                                <div class="input-group-addon" style="width: 120px;">满额减免手续费</div>
                                                <input type="text" name="withdraw[balance][poundage_full_cut]"
                                                       class="form-control"
                                                       value="{{ $set['poundage_full_cut'] ?? ''}}"
                                                       placeholder="提现金额达到 N元 减免手续费"/>
                                                <div class="input-group-addon">元</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                <div class="col-sm-9 col-xs-12">
                                    <div class="alipay">
                                        <label class='radio-inline' style="padding-left:0px">提现金额限制</label>
                                    </div>
                                    <div class="cost">
                                        <label class='radio-inline'>
                                            <div class="input-group">
                                                <input type="text" name="withdraw[balance][withdrawmoney]"
                                                       class="form-control" value="{{ $set['withdrawmoney'] ?? ''}}"
                                                       placeholder="余额提现最小金额值"/>
                                                <div class="input-group-addon">元</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                                <div class="col-sm-9 col-xs-12">
                                    <div class="alipay">
                                        <label class='radio-inline' style="padding-left:0px">提现倍数限制</label>
                                    </div>
                                    <div class="cost">
                                        <label class='radio-inline'>
                                            <div class="input-group">
                                                <input type="text" name="withdraw[balance][withdraw_multiple]"
                                                       class="form-control"
                                                       value="{{ $set['withdraw_multiple'] ?? ''}}"/>
                                                <span class='help-block'>倍数限制：为空则不限制，两位小数计算，建议使用正整数保证预算精确。</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            {{--                            <div class="tab-pane  active">--}}
                            {{--                                <div class="form-group">--}}
                            {{--                                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">余额提现免审核</label>--}}
                            {{--                                    <div class="col-sm-9 col-xs-12">--}}
                            {{--                                        <label class='radio-inline'>--}}
                            {{--                                            <input type='radio' name='withdraw[balance][audit_free]' value='1' @if($set['audit_free'] == 1) checked @endif />--}}
                            {{--                                            开启--}}
                            {{--                                        </label>--}}
                            {{--                                        <label class='radio-inline'>--}}
                            {{--                                            <input type='radio' name='withdraw[balance][audit_free]' value='0' @if($set['audit_free'] == 0) checked @endif />--}}
                            {{--                                            关闭--}}
                            {{--                                        </label>--}}
                            {{--                                        <span class='help-block'>余额提现自动审核、自动打款（自动打款只支持提现到提现到汇聚支付一种方式！）</span>--}}
                            {{--                                    </div>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}


                        </div>
                        {{--余额提现 end--}}

                    </div>

                    @foreach(\app\common\modules\widget\Widget::current()->getItem('withdraw') as $key=>$value)
                        <div class="tab-pane" id="{{$key}}">{!! widget($value['class'])!!}</div>
                    @endforeach

                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="success-btn col-sm-9 col-xs-12">
                        <input type="submit" name="submit" value="提交" class="btn btn-success"
                               onclick='return formcheck()'/>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
<script language="javascript">


    $('.diy-notice').select2();

    $(function () {
        $(":radio[name='withdraw[balance][status]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw").show();
            } else {
                $("#withdraw").hide();
            }
        });

        $(":radio[name='withdraw[balance][wechat]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw_balance_wechat").show();
            } else {
                $("#withdraw_balance_wechat").hide();
            }
        });
        $(":radio[name='withdraw[balance][alipay]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw_balance_alipay").show();
            } else {
                $("#withdraw_balance_alipay").hide();
            }
        });

        $(":radio[name='withdraw[balance][worker_withdraw_wechat]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw_balance_worker_withdraw_wechat").show();
            } else {
                $("#withdraw_balance_worker_withdraw_wechat").hide();
            }
        });

        $(":radio[name='withdraw[balance][worker_withdraw_bank]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw_balance_worker_withdraw_bank").show();
            } else {
                $("#withdraw_balance_worker_withdraw_bank").hide();
            }
        });

        $(":radio[name='withdraw[balance][worker_withdraw_alipay]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw_balance_worker_withdraw_alipay").show();
            } else {
                $("#withdraw_balance_worker_withdraw_alipay").hide();
            }
        });

        $(":radio[name='withdraw[balance][eplus_withdraw_bank]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw_balance_eplus_withdraw_bank").show();
            } else {
                $("#withdraw_balance_eplus_withdraw_bank").hide();
            }
        });

        $(":radio[name='withdraw[balance][converge_pay]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw_balance_converge_pay").show();
            } else {
                $("#withdraw_balance_converge_pay").hide();
            }
        });


        $(":radio[name='withdraw[balance][poundage_type]']").click(function () {
            if ($(this).val() == 1) {
                $("#poundage_unit").html('元');
                $("#poundage_hint").html('固定金额');
            } else {
                $("#poundage_unit").html('%');
                $("#poundage_hint").html('手续费比例')
            }
        });
        $(":radio[name='withdraw[balance][balance_manual]']").click(function () {
            if ($(this).val() == 1) {
                $("#balance_manual").show();
            } else {
                $("#balance_manual").hide();
            }
        });
        $(":radio[name='withdraw[balance][silver_point]']").click(function () {
            if ($(this).val() == 1) {
                $("#silver_point").show();
            } else {
                $("#silver_point").hide();
            }
        });
        $(":radio[name='withdraw[balance][jianzhimao_alipay]']").click(function () {
            if ($(this).val() == 1) {
                $("#jianzhimao_alipay").show();
            } else {
                $("#jianzhimao_alipay").hide();
            }
        });
        $(":radio[name='withdraw[balance][jianzhimao_bank]']").click(function () {
            if ($(this).val() == 1) {
                $("#jianzhimao_bank").show();
            } else {
                $("#jianzhimao_bank").hide();
            }
        });
        $(":radio[name='withdraw[balance][tax_withdraw_bank]']").click(function () {
            if ($(this).val() == 1) {
                $("#tax_withdraw_bank").show();
            } else {
                $("#tax_withdraw_bank").hide();
            }
        });
    })

</script>

@endsection
