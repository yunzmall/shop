<div class="tab-pane  active">
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到余额</label>
        <div class="col-sm-9 col-xs-12">
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][balance]' value='1' @if($set['balance'] == 1) checked @endif />
                开启
            </label>
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][balance]' value='0' @if($set['balance'] == 0) checked @endif />
                关闭
            </label>
            <span class='help-block'>可开启独立手续费设置，独立手续费：提现到余额的收入(不含收银台)，计算比例按照独立手续费中的比例计算【优先级高于插件独立设置】</span>
        </div>
    </div>
</div>



<div id='withdraw_income_balance' @if(empty($set['balance']))style="display:none"@endif>

    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
        <div class="col-sm-9 col-xs-12">
            <div class="alipay" >
                <label class='radio-inline' >独立手续费</label>
            </div>
            <div class="switch">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][balance_special]' value='1' @if($set['balance_special'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][balance_special]' value='0' @if($set['balance_special'] == 0) checked @endif />
                    关闭
                </label>
            </div>
        </div>
    </div>

    <div id='balance_special' @if(empty($set['balance_special']))style="display:none"@endif>
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
            <div class="col-sm-9 col-xs-12">
                <div class="alipay" >
                    <label class='radio-inline' >提现手续费类型</label>
                </div>
                <div class="switch">
                    <label class='radio-inline'>
                        <input type='radio' name='withdraw[income][special_poundage_type]' value='1'
                               @if($set['special_poundage_type'] == 1) checked @endif />
                        固定金额
                    </label>
                    <label class='radio-inline'>
                        <input type='radio' name='withdraw[income][special_poundage_type]' value='0'
                               @if(empty($set['special_poundage_type'])) checked @endif />
                        手续费比例
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
            <div class="col-sm-9 col-xs-12">
                <div class="alipay" >
                    <label class='radio-inline' >提现手续费</label>
                </div>
                <div class="cost" >
                    <label class='radio-inline'>
                        <div class="input-group">
                            <input type="text" name="withdraw[income][special_poundage]" class="form-control" value="{{ $set['special_poundage'] ?? ''}}" placeholder="提现至余额独立手续费比例"/>
                            <div class="input-group-addon" id="special_poundage_unit">@if($set['special_poundage_type'] == 1) 元 @else
                                    % @endif</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
            <div class="col-sm-9 col-xs-12">
                <div class="alipay" >
                    <label class='radio-inline' style="padding-left:0px">提现劳务税</label>
                </div>
                <div class="cost" >
                    <label class='radio-inline'>
                        <div class="input-group">
                            <input type="text" name="withdraw[income][special_service_tax]" class="form-control" value="{{ $set['special_service_tax'] ?? ''}}" placeholder="提现至余额独立劳务税比例"/>
                            <div class="input-group-addon">%</div>
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="tab-pane  active">
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到微信</label>
        <div class="col-sm-9 col-xs-12">
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][wechat]' value='1' @if($set['wechat'] == 1) checked @endif />
                开启
            </label>
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][wechat]' value='0' @if($set['wechat'] == 0) checked @endif />
                关闭
            </label>
        </div>
    </div>

    <div id='withdraw_income_wechat' @if(empty($set['wechat']))style="display:none"@endif>
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">单笔付款金额</label>
            <div class="col-sm-9 col-xs-12">
                <div class="input-group">
                    <div class="input-group">
                        <div class="input-group-addon">单笔最低金额</div>
                        <input type="text" name="withdraw[income][wechat_min]" class="form-control"
                               value="{{$set['wechat_min']}}" placeholder=""/>
                        <div class="input-group-addon">单笔最高金额</div>
                        <input type="text" name="withdraw[income][wechat_max]" class="form-control"
                               value="{{$set['wechat_max']}}" placeholder=""/>
                    </div>
                </div>
                <div class="help-block">
                    可设置区间0.3-20000，设置为0为空则不限制，请参考微信商户平台--产品中心--企业付款到零钱--产品设置--额度设置中设置
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">每日向同一用户付款不允许超过</label>
            <div class="col-sm-9 col-xs-12">
                <div class="input-group">
                    <div class="input-group">

                        <input type="text" name="withdraw[income][wechat_frequency]" class="form-control"
                               value="{{$set['wechat_frequency']}}" placeholder=""/>
                        <div class="input-group-addon">次</div>
                    </div>
                </div>
                <div class="help-block">
                    可设置1-10次,不设置或为空默认为10
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">商家转账到零钱</label>
            <div class="col-sm-9 col-xs-12">
                <div class="input-group">
                    <label class='radio-inline'>
                        <input type='radio' name='withdraw[income][wechat_api_v3]' value='1' @if($set['wechat_api_v3'] == 1) checked @endif />
                        开启
                    </label>
                    <label class='radio-inline'>
                        <input type='radio' name='withdraw[income][wechat_api_v3]' value='0' @if($set['wechat_api_v3'] == 0) checked @endif />
                        关闭
                    </label>
                </div>
                <div class="help-block">
                    如在2022-5-18之后开通的微信商家转账零钱功能（原企业付款到零钱），请开启此功能，并在【系统】-【支付设置】处设置上传相关的证书及秘钥
                </div>
            </div>
        </div>

    </div>

</div>





<div class="tab-pane  active">
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到支付宝</label>
        <div class="col-sm-9 col-xs-12">
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][alipay]' value='1' @if($set['alipay'] == 1) checked @endif />
                开启
            </label>
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][alipay]' value='0' @if($set['alipay'] == 0) checked @endif />
                关闭
            </label>
        </div>
    </div>

    <div id='withdraw_income_alipay' @if(empty($set['alipay']))style="display:none"@endif>
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">单笔付款金额</label>
            <div class="col-sm-9 col-xs-12">
                <div class="input-group">
                    <div class="input-group">
                        <div class="input-group-addon">单笔最低金额</div>
                        <input type="text" name="withdraw[income][alipay_min]" class="form-control"
                               value="{{$set['alipay_min']}}" placeholder=""/>
                        <div class="input-group-addon">单笔最高金额</div>
                        <input type="text" name="withdraw[income][alipay_max]" class="form-control"
                               value="{{$set['alipay_max']}}" placeholder=""/>
                    </div>
                </div>
                <div class="help-block">
                   不设置或为空,则不限制
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">每日向同一用户付款不允许超过</label>
            <div class="col-sm-9 col-xs-12">
                <div class="input-group">
                    <div class="input-group">

                        <input type="text" name="withdraw[income][alipay_frequency]" class="form-control"
                               value="{{$set['alipay_frequency']}}" placeholder=""/>
                        <div class="input-group-addon">次</div>
                    </div>
                </div>
                <div class="help-block">
                    不设置或为空,则不限制
                </div>
            </div>
        </div>

    </div>

</div>

@if(app('plugins')->isEnabled('huanxun'))
<div class="tab-pane  active">
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到环迅支付</label>
        <div class="col-sm-9 col-xs-12">
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][huanxun]' value='1' @if($set['huanxun'] == 1) checked @endif />
                开启
            </label>
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][huanxun]' value='0' @if($set['huanxun'] == 0) checked @endif />
                关闭
            </label>
            <span class='help-block'>提现到环迅支付，支持收入提现免审核</span>
        </div>
    </div>
</div>
@endif

@if(app('plugins')->isEnabled('eup-pay'))
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到EUP</label>
            <div class="col-sm-9 col-xs-12">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][eup_pay]' value='1' @if($set['eup_pay'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][eup_pay]' value='0' @if($set['eup_pay'] == 0) checked @endif />
                    关闭
                </label>
            </div>
        </div>
    </div>
@endif

@if(app('plugins')->isEnabled('converge_pay'))
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到汇聚支付</label>
            <div class="col-sm-9 col-xs-12">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][converge_pay]' value='1' @if($set['converge_pay'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][converge_pay]' value='0' @if($set['converge_pay'] == 0) checked @endif />
                    关闭
                </label>
            </div>
        </div>

        <div id='withdraw_income_converge' @if(empty($set['converge_pay']))style="display:none"@endif>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">单笔付款金额</label>
                <div class="col-sm-9 col-xs-12">
                    <div class="input-group">
                        <div class="input-group">
                            <div class="input-group-addon">单笔最低金额</div>
                            <input type="text" name="withdraw[income][converge_pay_min]" class="form-control"
                                   value="{{$set['converge_pay_min']}}" placeholder=""/>
                            <div class="input-group-addon">单笔最高金额</div>
                            <input type="text" name="withdraw[income][converge_pay_max]" class="form-control"
                                   value="{{$set['converge_pay_max']}}" placeholder=""/>
                        </div>
                    </div>
                    <div class="help-block">
                        不设置或为空,则不限制
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">每日向同一用户付款不允许超过</label>
                <div class="col-sm-9 col-xs-12">
                    <div class="input-group">
                        <div class="input-group">

                            <input type="text" name="withdraw[income][converge_pay_frequency]" class="form-control"
                                   value="{{$set['converge_pay_frequency']}}" placeholder=""/>
                            <div class="input-group-addon">次</div>
                        </div>
                    </div>
                    <div class="help-block">
                        不设置或为空,则不限制
                    </div>
                </div>
            </div>

        </div>

    </div>
@endif

@if(app('plugins')->isEnabled('yop-pay'))
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到易宝</label>
            <div class="col-sm-9 col-xs-12">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][yop_pay]' value='1' @if($set['yop_pay'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][yop_pay]' value='0' @if($set['yop_pay'] == 0) checked @endif />
                    关闭
                </label>
            </div>
        </div>
    </div>
@endif

@if(app('plugins')->isEnabled('yee-pay'))
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到易宝代付</label>
            <div class="col-sm-9 col-xs-12">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][yee_pay]' value='1' @if($set['yee_pay'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][yee_pay]' value='0' @if($set['yee_pay'] == 0) checked @endif />
                    关闭
                </label>
            </div>
        </div>
    </div>
@endif

@if(app('plugins')->isEnabled('high-light') && \Yunshop\HighLight\services\SetService::getStatus())
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到微信-高灯</label>
            <div class="col-sm-9 col-xs-12">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][high_light_wechat]' value='1' @if($set['high_light_wechat'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][high_light_wechat]' value='0' @if($set['high_light_wechat'] == 0) checked @endif />
                    关闭
                </label>
            </div>
        </div>
    </div>
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到支付宝-高灯</label>
            <div class="col-sm-9 col-xs-12">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][high_light_alipay]' value='1' @if($set['high_light_alipay'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][high_light_alipay]' value='0' @if($set['high_light_alipay'] == 0) checked @endif />
                    关闭
                </label>
            </div>
        </div>
    </div>
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到银行卡-高灯</label>
            <div class="col-sm-9 col-xs-12">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][high_light_bank]' value='1' @if($set['high_light_bank'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][high_light_bank]' value='0' @if($set['high_light_bank'] == 0) checked @endif />
                    关闭
                </label>
            </div>
        </div>
    </div>
@endif

@if(app('plugins')->isEnabled('eplus-pay') && \Yunshop\EplusPay\services\SettingService::usable())
<div class="tab-pane  active">
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到银行卡-智E+</label>
        <div class="col-sm-9 col-xs-12">
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][eplus_withdraw_bank]' value='1'
                       @if($set['eplus_withdraw_bank'] == 1) checked @endif />
                开启
            </label>
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][eplus_withdraw_bank]' value='0'
                       @if(empty($set['eplus_withdraw_bank'])) checked @endif />
                关闭
            </label>
        </div>
    </div>
</div>
@endif

@if(\app\common\services\finance\IncomeService::workerWithdrawEnable(2))
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到微信-好灵工</label>
            <div class="col-sm-9 col-xs-12">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][worker_withdraw_wechat]' value='1'
                           @if($set['worker_withdraw_wechat'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][worker_withdraw_wechat]' value='0'
                           @if($set['worker_withdraw_wechat'] == 0) checked @endif />
                    关闭
                </label>
            </div>
        </div>
    </div>
@endif
@if(\app\common\services\finance\IncomeService::workerWithdrawEnable(1))
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到支付宝-好灵工</label>
            <div class="col-sm-9 col-xs-12">

                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][worker_withdraw_alipay]' value='1'
                           @if($set['worker_withdraw_alipay'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][worker_withdraw_alipay]' value='0'
                           @if($set['worker_withdraw_alipay'] == 0) checked @endif />
                    关闭
                </label>

            </div>
        </div>
    </div>
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现到银行卡-好灵工</label>
            <div class="col-sm-9 col-xs-12">

                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][worker_withdraw_bank]' value='1'
                           @if($set['worker_withdraw_bank'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][worker_withdraw_bank]' value='0'
                           @if($set['worker_withdraw_bank'] == 0) checked @endif />
                    关闭
                </label>

            </div>
        </div>
    </div>
@endif

<div class="tab-pane  active">
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">{{ \Setting::get('shop.lang.zh_cn.income.manual_withdrawal') ? \Setting::get('shop.lang.zh_cn.income.manual_withdrawal') : "手动提现" }}</label>
        <div class="col-sm-9 col-xs-12">
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][manual]' value='1' @if($set['manual'] == 1) checked @endif />
                开启
            </label>
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][manual]' value='0' @if($set['manual'] == 0) checked @endif />
                关闭
            </label>
            <span class='help-block'>手动提现包含 银行卡、微信号、支付宝等三种类型，会员需要完善对应资料才可以提现</span>
        </div>
    </div>
</div>
<div id='manual_type' @if(empty($set['manual']))style="display:none"@endif>
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
        <div class="col-sm-9 col-xs-12">
            <div class="switch">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][manual_type]' value='1' @if($set['manual_type'] == 1 || $set['balance_special'] == 1 || empty($set['manual_type'])) checked @endif />
                    银行卡
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][manual_type]' value='2' @if($set['manual_type'] == 2) checked @endif />
                    微信
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][manual_type]' value='3' @if($set['manual_type'] == 3) checked @endif />
                    支付宝
                </label>
            </div>
        </div>
    </div>
</div>

@if(app('plugins')->isEnabled('silver-point-pay'))
<div class="tab-pane  active">
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">
            提现到银典支付
        </label>
        <div class="col-sm-9 col-xs-12">
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][silver_point]' value='1' @if($set['silver_point'] == 1) checked @endif />
                开启
            </label>
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][silver_point]' value='0' @if($set['silver_point'] == 0) checked @endif />
                关闭
            </label>
            <span class='help-block'></span>
        </div>
    </div>
</div>
@endif

@if(app('plugins')->isEnabled('jianzhimao-withdraw'))
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">
                提现到兼职猫-银行卡
            </label>
            <div class="col-sm-9 col-xs-12">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][jianzhimao_bank]' value='1' @if($set['jianzhimao_bank'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][jianzhimao_bank]' value='0' @if($set['jianzhimao_bank'] == 0) checked @endif />
                    关闭
                </label>
                <span class='help-block'></span>
            </div>
        </div>
    </div>
@endif

@if(app('plugins')->isEnabled('tax-withdraw'))
    <div class="tab-pane  active">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">
                提现到@php echo TAX_WITHDRAW_DIY_NAME; @endphp -银行卡
            </label>
            <div class="col-sm-9 col-xs-12">
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][tax_withdraw_bank]' value='1' @if($set['tax_withdraw_bank'] == 1) checked @endif />
                    开启
                </label>
                <label class='radio-inline'>
                    <input type='radio' name='withdraw[income][tax_withdraw_bank]' value='0' @if($set['tax_withdraw_bank'] == 0) checked @endif />
                    关闭
                </label>
                <span class='help-block'></span>
            </div>
        </div>
    </div>
@endif

<div class="tab-pane  active">
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">可提现日期</label>
        <div class="col-sm-9 col-xs-12">
            <label class='checkbox-inline'>
                <input type='checkbox' name='withdraw[income][withdraw_date]' class="withdraw_data" value='0' @if($set['withdraw_date'] === '0' || !isset($set['withdraw_date'])) checked @endif />
                全部
            </label>
            @for($i = 1 ; $i <= 31; $i++)
                <label class='checkbox-inline'>
                    <input type='checkbox' name='withdraw[income][withdraw_date][{{ $i }}]' class="withdraw_data_day" value='{{ $i }}' @if(isset($set['withdraw_date'][$i])) checked @endif />
                    {{ $i }}号
                </label>
            @endfor
        </div>

    </div>
</div>


<div class="tab-pane  active">
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">劳务税显示</label>
        <div class="col-sm-9 col-xs-12">
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][service_switch]' value='1' @if($set['service_switch'] == 1 || !isset($set['service_switch'])) checked @endif />
                开启
            </label>
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][service_switch]' value='0' @if($set['service_switch'] === '0') checked @endif />
                关闭
            </label>
            <span class='help-block'>关闭只隐藏前端显示，若有设置劳务税，前端提现还是会计算劳务税</span>
        </div>

    </div>
</div>



<div class="tab-pane  active">
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">劳务税计算方式</label>
        <div class="col-sm-9 col-xs-12">
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][service_tax_calculation]' value='0' @if($set['service_tax_calculation'] == 0) checked @endif />
                提现金额-手续费
            </label>
            <label class='radio-inline'>
                <input type='radio' name='withdraw[income][service_tax_calculation]' value='1' @if($set['service_tax_calculation'] == 1) checked @endif />
                提现金额
            </label>
        </div>
    </div>
</div>

<div class="tab-pane  active">
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">劳务税比例</label>
        <div class="col-sm-9 col-xs-12">
            <div class="input-group">
                <input type="text" name="withdraw[income][servicetax_rate]" class="form-control" value="{{ $set['servicetax_rate'] ?? ''}}" placeholder="请输入提现劳务税比例"/>
                <div class="input-group-addon">%</div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
        <div class="col-xs-6">
            <div class="return-queue">
                @foreach($set['servicetax'] as $key => $queue)
                    <div class="input-group">
                        <div class="input-group-addon">范围</div>
                        <input type="text" name="withdraw[income][servicetax][{{ $key }}][servicetax_money]"
                               class="form-control return_level"
                               value="{{ $queue['servicetax_money'] }}"/>
                        <div class="input-group-addon team-level">元</div>
                        <input type="text" name="withdraw[income][servicetax][{{ $key }}][servicetax_rate]"
                               class="form-control"
                               value="{{ $queue['servicetax_rate'] }}"/>
                        <div class="input-group-addon">%</div>

                        <div class="input-group-addon del-queue" title="删除"><i class="fa fa-trash"></i></div>
                    </div>
                @endforeach
            </div>
            <span class='help-block'>必须按金额从小到大填写</span>
        </div>
    </div>

    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
        <div class="col-xs-6">
            <span class='help-block'><input type="button" value="添加比例" class="btn btn-success add-queue"/></span>
        </div>
    </div>

    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">提现规则说明</label>
        <div class="col-xs-9">
            {!! yz_tpl_ueditor('withdraw[income][withdraw_rich_text]', $withdraw_rich_text['content']) !!}
        </div>
    </div>
</div>

{{--<div class="tab-pane  active">--}}
    {{--<div class="return-queue">--}}
        {{--@foreach($setQueue as $key => $queue)--}}
            {{--<div class="input-group">--}}
                {{--<div class="input-group-addon">奖励比例</div>--}}
                {{--<input type="text" name="setdata[queue][{{$key}}][return_rate]"--}}
                       {{--class="form-control"--}}
                       {{--value="{{$queue['return_rate']}}"/>--}}
                {{--<div class="input-group-addon">%</div>--}}
                {{--<input type="text" name="setdata[queue][{{$key}}][return_level]"--}}
                       {{--class="form-control return_level"--}}
                       {{--value="{{$queue['return_level']}}"/>--}}
                {{--<input type="hidden" name="setdata[queue][{{$key}}][return_level_id]"--}}
                       {{--class="form-control return_level_id"--}}
                       {{--value="{{$queue['return_level_id']}}"/>--}}
                {{--<div class="input-group-addon team-level">选择奖励等级</div>--}}
                {{--<div class="input-group-addon del-queue" title="删除"><i class="fa fa-trash"></i></div>--}}
            {{--</div>--}}
        {{--@endforeach--}}
    {{--</div>--}}
    {{--<span class='help-block'>奖励金额=商城每天完成订单总营业额的百分比</span>--}}
    {{--<span class='help-block'><input type="button" value="添加奖励政策" class="btn btn-success add-queue"/></span>--}}
{{--</div>--}}

{{--<div class="tab-pane  active">--}}
{{--    <div class="form-group">--}}
{{--        <label class="col-xs-12 col-sm-3 col-md-2 control-label">收入提现免审核</label>--}}
{{--        <div class="col-sm-9 col-xs-12">--}}
{{--            <label class='radio-inline'>--}}
{{--                <input type='radio' name='withdraw[income][free_audit]' value='1' @if($set['free_audit'] == 1) checked @endif />--}}
{{--                开启--}}
{{--            </label>--}}
{{--            <label class='radio-inline'>--}}
{{--                <input type='radio' name='withdraw[income][free_audit]' value='0' @if($set['free_audit'] == 0) checked @endif />--}}
{{--                关闭--}}
{{--            </label>--}}
{{--            <span class='help-block'>收入提现自动审核、自动打款（自动打款只支持提现到余额、提现到微信、提现到环迅支付、提现到汇聚支付四种方式！）</span>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--</div>--}}





<script language="javascript">
    $(function () {
        var day = $(".withdraw_data_day");//获取div下的input
        $(".withdraw_data").click(function () {
            for(i=0;i<day.length;i++){
                day[i].checked=false
            }
        });
        $(".withdraw_data_day").click(function () {
           $(".withdraw_data").attr("checked",false);
        });

        $(":radio[name='withdraw[income][balance]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw_income_balance").show();
            }
            else {
                $("#withdraw_income_balance").hide();
            }
        });

        $(":radio[name='withdraw[income][wechat]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw_income_wechat").show();
            }
            else {
                $("#withdraw_income_wechat").hide();
            }
        });

        $(":radio[name='withdraw[income][alipay]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw_income_alipay").show();
            }
            else {
                $("#withdraw_income_alipay").hide();
            }
        });

        $(":radio[name='withdraw[income][converge_pay]']").click(function () {
            if ($(this).val() == 1) {
                $("#withdraw_income_converge").show();
            }
            else {
                $("#withdraw_income_converge").hide();
            }
        });


        $(":radio[name='withdraw[income][balance_special]']").click(function () {
            if ($(this).val() == 1) {
                $("#balance_special").show();
            }
            else {
                $("#balance_special").hide();
            }
        });
        $(":radio[name='withdraw[income][manual]']").click(function () {
            if ($(this).val() == 1) {
                $("#manual_type").show();
            }
            else {
                $("#manual_type").hide();
            }
        });
        $(":radio[name='withdraw[income][special_poundage_type]']").click(function () {
            if ($(this).val() == 1) {
                $("#special_poundage_unit").html('元');
            }
            else {
                $("#special_poundage_unit").html('%');
            }
        });
    })

    var i = "{{ $income_count }}";
    $('.add-queue').click(function () {
        var html = '';
        html += '<div class="input-group">';
        html += '<div class="input-group-addon">范围</div>';
        html += '<input type="text" name="withdraw[income][servicetax]['+ i +'][servicetax_money]"';
        html += 'class="form-control return_level"';
        html += 'value=""/>';
        html += '<div class="input-group-addon team-level" >元</div>';
        html += '<input type="text" name="withdraw[income][servicetax]['+ i +'][servicetax_rate]"';
        html += 'class="form-control"';
        html += 'value=""/>';
        html += '<div class="input-group-addon">% </div>';
        html += '<div class="input-group-addon del-queue" title="删除" ><i class="fa fa-trash"></i></div>';
        html += '</div>';
        $('.return-queue').append(html);
        i = parseInt(i) + parseInt(1);
    });

    $(document).on('click', '.del-queue', function () {
        var _this = $(this);
        _this.parent('.input-group').remove();
    });
</script>