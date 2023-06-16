<div class="panel panel-default">
    <div class="panel-heading">
        发票
    </div>
    <div class="panel-body">
        <div class="form-group">
            <label class="col-xs-12 col-sm-3 col-md-2 control-label">发票类型 :</label>
            <div class="col-sm-9 col-xs-12">
                @if($order['order_invoice']['invoice_type']==2)
                    <p class="form-control-static">专用发票</p>
                @elseif($order['order_invoice']['invoice_type']==1)
                    <p class="form-control-static">纸质发票</p>
                @else
                    <p class="form-control-static">电子发票</p>
                @endif
            </div>
        </div>
        @if($order['order_invoice']['invoice_type']==2)
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">单位 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">{{$order['order_invoice']['collect_name']}}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">税号 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">{{$order['order_invoice']['gmf_taxpayer']}}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">开户银行 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">{{$order['order_invoice']['gmf_bank']}}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">银行账号 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">{{$order['order_invoice']['gmf_bank_admin']}}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">注册地址 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">{{$order['order_invoice']['gmf_address']}}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">注册电话 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">{{$order['order_invoice']['gmf_mobile']}}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">收票人手机号 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">{{$order['order_invoice']['col_mobile']}}</p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">邮箱 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">{{$order['order_invoice']['email']}}</p>
                </div>
            </div>
        @else
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">发票抬头 :</label>
                <div class="col-sm-9 col-xs-12">
                    @if($order['order_invoice']['rise_type']==1)
                        <p class="form-control-static">个人</p>
                    @else
                        <p class="form-control-static">单位</p>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">单位/抬头名称 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">{{$order['order_invoice']['collect_name']}}</p>
                </div>
            </div>
            @if($order['order_invoice']['rise_type']==0)
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">税号 :</label>
                    <div class="col-sm-9 col-xs-12">
                        <p class="form-control-static">{{$order['order_invoice']['gmf_taxpayer']}}</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">开户银行 :</label>
                    <div class="col-sm-9 col-xs-12">
                        <p class="form-control-static">{{$order['order_invoice']['gmf_bank']}}</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">银行账号 :</label>
                    <div class="col-sm-9 col-xs-12">
                        <p class="form-control-static">{{$order['order_invoice']['gmf_bank_admin']}}</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">注册地址 :</label>
                    <div class="col-sm-9 col-xs-12">
                        <p class="form-control-static">{{$order['order_invoice']['gmf_address']}}</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">注册电话 :</label>
                    <div class="col-sm-9 col-xs-12">
                        <p class="form-control-static">{{$order['order_invoice']['gmf_mobile']}}</p>
                    </div>
                </div>
            @endif
            @if(!empty($order['order_invoice']['content']))
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">发票内容 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">{{$order['order_invoice']['content']}}</p>
                </div>
            </div>
            @endif
            @if($order['order_invoice']['invoice_type']==1)
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">收票信息 :</label>
                    <div class="col-sm-9 col-xs-12">
                        <p class="form-control-static">{{$order['order_invoice']['col_name']}} / {{$order['order_invoice']['col_mobile']}}</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">收票地址 :</label>
                    <div class="col-sm-9 col-xs-12">
                        <p class="form-control-static">{{$order['order_invoice']['col_address']}}</p>
                    </div>
                </div>
            @else
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">收票人手机号 :</label>
                    <div class="col-sm-9 col-xs-12">
                        <p class="form-control-static">{{$order['order_invoice']['col_mobile']}}</p>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">邮箱 :</label>
                    <div class="col-sm-9 col-xs-12">
                        <p class="form-control-static">{{$order['order_invoice']['email']}}</p>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>