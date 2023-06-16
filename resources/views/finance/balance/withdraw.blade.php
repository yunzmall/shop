@extends('layouts.base')
@section('title', '余额提现')
@section('content')

    <style>
        body {
            background-color: #eff3f6;
        }

        .info {
            display: flex;
            margin: 20px 0;
        }

        .user {
            width: 500px;
        }

        .info-title {
            font-size: 14px;
            color: #29ba9c;
            line-height: 40px;
        }

        .info-box {
            display: flex;

        }

        .user-info img {
            width: 60px;
            height: 60px;
            border-radius: 4px;
        }

        .info-item {

            /* padding: 0 100px; */
        }

        .item {
            margin: 5px 10px;

        }

        .box-card {
            width: 300px;
        }

        .el-card__header {
            text-align: center;
            padding: 10px;
        }

        .cell {
            text-align: center;
        }

        .vue-page {
            border-radius: 5px;
            width: calc(100% - 276px);
            float: right;
            margin-right: 15px;
            position: fixed;
            bottom: 0;
            right: 0;
            padding: 15px 5% 15px 0;
            background: #fff;
            height: 60px;
            z-index: 999;
            margin-top: 0;
            box-shadow: 0 2px 9px rgb(51 51 51 / 10%);
            text-align: center;
        }

        .ui-list {
            height: 310px;
            display: flex;
            flex-direction: column;
            flex-wrap: wrap;
        }

        .ui-row {
            display: flex;
            flex-direction: row;
        }

        .changeAll .el-radio {
            margin-right: 10px;
        }

        .el-message-box {
            width: 500px;
        }

        [v-cloak] {
            display: none;
        }
    </style>
    <div class="all">
        <div id="app" v-cloak>
            <box-item text="提现者信息" v-loading="loading">
                <div class="info-box">
                    <div v-if="item.has_one_member">
                        <img style="width: 100px;height: 100px;margin:20px 30px 0 100px"
                             :src="item.has_one_member.avatar_image"
                             alt="" srcset=""/>
                    </div>
                    <div>
                        <div class="info-item">
                            <div class="user-info">
                                <div class="ui-list" style="margin-left: 20px" v-if="item.has_one_member">
                                    <div class="item">昵称：[[item.has_one_member.nickname]]</div>
                                    <div class="item">姓名：[[item.has_one_member.realname]]</div>
                                    <div class="item">手机号：[[item.has_one_member.mobile]]</div>

                                    <div class="item">会员等级：
                                        <span v-if="item.has_one_member.yz_member.level">[[item.has_one_member.yz_member.level.level_name]]</span>
                                        <span v-else>[[shopSet.level_name]]</span>
                                    </div>
                                    <div class="item">提现金额：<span
                                                style="color: red;">[[item.amounts||"0.00"]] 元</span>
                                    </div>
                                    <div class="item">提现类型：[[item.type_name]]</div>
                                    <div class="item">提现方式：[[item.pay_way_name]]</div>
                                    <template class="item" v-if="item.pay_way=='manual'">
                                        <template v-if="item.manual_type==1">
                                            <div class="item">{{\Setting::get('shop.lang.zh_cn.income.manual_withdrawal') ?: '手动打款'}}
                                                方式：银行卡
                                            </div>
                                            <template v-if="item.bank_card">
                                                <div class="item">开户人姓名：[[item.bank_card.member_name]]</div>
                                                <div class="item">开户行：[[item.bank_card.bank_name]]</div>
                                                <div class="item">开户省：[[item.bank_card.bank_province]]</div>
                                                <div class="item">开户市：[[item.bank_card.bank_city]]</div>
                                                <div class="item">开户支行：[[item.bank_card.bank_branch]]</div>
                                                <div class="item">银行卡号：[[item.bank_card.bank_card]]</div>
                                            </template>
                                        </template>
                                        <template v-if="item.manual_type==2">
                                            <div class="item">手动打款方式：微信</div>
                                            <div class="item" v-if="item.has_one_member.yz_member">
                                                微信号：[[item.has_one_member.yz_member.wechat]]
                                            </div>
                                        </template>
                                        <template v-if="item.manual_type==3">
                                            <div class="item">手动打款方式：支付宝</div>
                                            <div class="item" v-if="item.has_one_member.yz_member">
                                                账号姓名：[[item.has_one_member.yz_member.alipayname]]
                                            </div>
                                            <div class="item" v-if="item.has_one_member.yz_member">
                                                支付宝号：[[item.has_one_member.yz_member.alipay]]
                                            </div>
                                        </template>
                                    </template>
                                    <template class="item" v-if="item.pay_way=='silver_point'">
                                        <template v-if="item.bank_card">
                                            <div class="item">开户人姓名：[[item.bank_card.member_name]]</div>
                                            <div class="item">开户行：[[item.bank_card.bank_name]]</div>
                                            <div class="item">开户省：[[item.bank_card.bank_province]]</div>
                                            <div class="item">开户市：[[item.bank_card.bank_city]]</div>
                                            <div class="item">开户支行：[[item.bank_card.bank_branch]]</div>
                                            <div class="item">银行卡号：[[item.bank_card.bank_card]]</div>
                                        </template>
                                    </template>
                                    <div class="item">提现状态：<span style="">[[item.status_name]]</span></div>

                                    <div class="item">申请时间：[[item.created_at]]</div>
                                    <div class="item" v-if="item.audit_at">审核时间：[[item.audit_at]]</div>
                                    <div class="item" v-if="item.pay_at">打款时间：[[item.pay_at]]</div>
                                    <div class="item" v-if="item.arrival_at">到账时间：[[item.arrival_at]]</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ui-row">
                    <div class="item">打款信息：</div>
                    <template v-if="item.status==0">
                        <div class="item">审核金额：<span style="color: red;">[[item.amounts||"0.00"]] 元</span>
                        </div>
                        <div class="item">预计手续费：<span style="color: red;">[[item.poundage||"0.00"]] 元</span>
                        </div>
                        <div class="item">预计应打款：<span style="color: red;">[[(item.amounts-item.poundage-item.servicetax).toFixed(2)]] 元</span>
                        </div>
                    </template>
                    <template v-else>
                        <div class="item">审核金额：<span style="color: red;">[[(Number(item.actual_amounts)+Number(item.actual_servicetax)+Number(item.actual_poundage)).toFixed(2)]] 元</span>
                        </div>
                        <div class="item">手续费：<span
                                    style="color: red;">[[item.actual_poundage||"0.00"]] 元</span></div>
                        <div class="item">应打款：<span
                                    style="color: red;">[[item.actual_amounts||"0.00"]] 元</span></div>
                    </template>
                </div>
            </box-item>
            <box-item text="提现申请信息">
                <el-table :data="list" style="width: 100%" v-loading="loading">
                    <el-table-column label="状态" width="300" v-if="item.status==0||item.status==-1">
                        <template slot-scope="scope">
                            <el-radio-group v-model="pay_status">
                                <el-radio :label="1">通过</el-radio>
                                <el-radio :label="-1">无效</el-radio>
                                <el-radio :label="3">驳回</el-radio>
                            </el-radio-group>
                        </template>
                    </el-table-column>

                    <el-table-column prop="id" label="ID"></el-table-column>
                    <el-table-column prop="type_name" label="提现类型"></el-table-column>
                    <el-table-column prop="amount" label="提现金额"></el-table-column>
                    <el-table-column prop="status_name" label="打款状态"></el-table-column>
                    <el-table-column prop="created_at" label="提现时间"></el-table-column>
                </el-table>
            </box-item>
            <div style="height: 60px;"></div>
            <div class="vue-page">
                <el-button @click="goback">返回列表</el-button>
                <el-button type="primary" @click="Repayment('submit_check')" v-if="item.status==0">提交审核</el-button>
                <template v-if="item.status==1">
                    <el-button v-if="item.pay_way=='silver_point'" type="primary" @click="Repayment('pay_way')">
                        打款到银典支付
                    </el-button>
                    <el-button v-if="item.pay_way=='jianzhimao_bank'" type="primary" @click="Repayment('submit_pay')">
                        打款到兼职猫-银行卡
                    </el-button>
                    <el-button v-if="item.pay_way=='tax_withdraw_bank'" type="primary" @click="Repayment('submit_pay')">
                        打款到@if(app('plugins')->isEnabled('tax-withdraw'))
                            {{ TAX_WITHDRAW_DIY_NAME }}
                        @else
                            税筹添薪
                        @endif -银行卡
                    </el-button>
                    <el-button v-if="item.pay_way=='balance'" type="primary" @click="Repayment('submit_pay')">打款到余额
                    </el-button>
                    <el-button v-if="item.pay_way=='wechat'" type="primary" @click="Repayment('submit_pay')">打款到微信钱包
                    </el-button>
                    <el-button v-if="item.pay_way=='alipay'" type="primary" @click="Repayment('submit_pay')">打款到支付宝
                    </el-button>
                    <el-button v-if="item.pay_way=='manual'" type="primary"
                               @click="Repayment('submit_pay')">{{\Setting::get('shop.lang.zh_cn.income.manual_withdrawal') ?: '手动打款'}}</el-button>
                    <el-button v-if="item.pay_way=='eup_pay'" type="primary" @click="Repayment('submit_pay')">EUP提现
                    </el-button>
                    <el-button v-if="item.pay_way=='huanxun'" type="primary" @click="Repayment('submit_pay')">打款到银行卡
                    </el-button>
                    <el-button v-if="item.pay_way=='yop_pay'" type="primary" @click="Repayment('submit_pay')">易宝提现
                    </el-button>
                    <el-button v-if="item.pay_way=='converge_pay'" type="primary" @click="Repayment('submit_pay')">
                        汇聚提现
                    </el-button>
                    <el-button
                            v-if="item.pay_way=='high_light_wechat'||item.pay_way=='high_light_alipay'||item.pay_way=='high_light_bank'"
                            type="primary" @click="Repayment('submit_pay')">高灯打款
                    </el-button>
                    <el-button
                            v-if="item.pay_way=='worker_withdraw_wechat'||item.pay_way=='worker_withdraw_alipay'||item.pay_way=='worker_withdraw_bank'"
                            type="primary" @click="Repayment('submit_pay')">好灵工打款
                    </el-button>
                    <el-button v-if="item.pay_way=='eplus_withdraw_bank'" type="primary"
                               @click="Repayment('submit_pay')">智E+打款
                    </el-button>
                    <el-button type="primary" @click="Repayment('confirm_pay')">线下确认打款</el-button>
                    <el-button type="danger" @click="Repayment('audited_rebut')">驳回记录</el-button>
                </template>
                <template v-else-if="item.status==4">
                    <el-button type="primary" @click="Repayment('again_pay')">重新打款</el-button>
                    <el-button type="primary" @click="Repayment('confirm_pay')">线下确认打款</el-button>
                    <el-button type="danger" @click="Repayment('audited_rebut')">驳回记录</el-button>
                </template>
                <template v-else-if="item.status==-1">
                    <el-button type="primary" @click="Repayment('submit_cancel')">重新审核</el-button>
                </template>
            </div>

            <el-dialog
                    :title="title"
                    :visible.sync="dialogVisible"
                    width="30%">
                <span>[[msg]]</span>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogVisible = false">取 消</el-button>
                    <el-button type="primary" @click="confirm">确 定</el-button>
                </span>
            </el-dialog>

            <el-dialog
                    title="驳回理由"
                    :visible.sync="dialogVisible2"
                    width="30%">
                <el-input
                        type="textarea"
                        :rows="10"
                        placeholder="请输入内容"
                        resize="none"
                        v-model="reject_reason">
                </el-input>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="dialogVisible2 = false">取 消</el-button>
                    <el-button type="primary" @click="confirm2">确 定</el-button>
                </span>
            </el-dialog>
            @include("finance.balance.verifyPopupComponentV2")
        </div>
    </div>

    @include('public.admin.box-item')

    <script>
        let vm = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    item: {},
                    withdraw: {},
                    shopSet: {},
                    list: [],
                    title: '',
                    msg: '',
                    loading: false,
                    examineData: {},
                    income_total: "",
                    dialogVisible: false,
                    dialogVisible2: false,
                    reject_reason: '',
                    detail: null,
                    changeAllData: "",
                    url: '',
                    id: 0,
                    pay_status: 1,
                    json: {},
                    is_verify: false,
                    dialog_visible_verify: false,
                    verify_phone: '',
                    verify_expire: '',
                    disabled:false,
                    submit_review:false,
                    check_data: {
                        code: ''
                    },
                    time_butont:'获取验证码',
                }
            },
            created() {
                this.id = this.getParam('id');
                this.url = this.getUrl(this.getParam('type'));
                this.getData();
            },
            methods: {
                getUrl(type) {
                    let url = ''
                    switch (type) {
                        case "balance":
                            url = '{!! yzWebUrl('finance.balance-withdraw.detail')!!}';
                            break;
                        case "auction_prepayment":
                            url = '{!! yzWebUrl('finance.prepayment-withdraw.detail')!!}';
                            break;
                    }
                    return url;
                },
                getParam(name) {
                    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                    var r = window.location.search.substr(1).match(reg);
                    if (r != null) return unescape(r[2]);
                    return null;
                },
                getData() {
                    this.loading = true;
                    this.$http.post(this.url, {
                        id: this.id
                    }).then((response) => {
                        if (response.data.result) {
                            let data = response.data.data;
                            this.item = data.item
                            this.is_verify = data.is_verify
                            this.verify_phone = data.verify_phone
                            this.verify_expire = data.verify_expire
                            this.shopSet = data.shopSet
                            this.list.push({
                                id: this.item.id,
                                type_name: this.item.type_name,
                                amount: this.item.amounts,
                                status_name: this.item.status_name,
                                created_at: this.item.created_at
                            })
                        } else this.$message.error(data.msg);
                        this.loading = false;
                    })
                },
                Repayment(name) {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.json = {}
                    switch (name) {
                        case "submit_check" :
                            this.json.submit_check = 1;
                            if (this.pay_status === -1 || this.pay_status === 3) {
                                this.title = '提交审核';
                                this.msg = ""
                                this.dialogVisible2 = true;
                                this.submit_review = true;
                                loading.close();
                                return
                            }
                            break;
                        case "audited_rebut" :
                            this.title = '驳回记录';
                            this.msg = "驳回后，需要会员重新申请提现（仅驳回审核通过提现!）"
                            this.dialogVisible = true;
                            loading.close();
                            return
                        case "submit_cancel" :
                            this.json.submit_cancel = 1;
                            break;
                        case "submit_pay" :
                            this.json.submit_pay = 1;
                            break;
                        case "again_pay" :
                            this.json.again_pay = 1;
                            break;
                        case "confirm_pay" :
                            this.json.confirm_pay = 1;
                            this.title = '确认线下打款';
                            this.msg = "本打款方式需要线下打款，系统只是完成流程!"
                            this.dialogVisible = true;
                            loading.close();
                            return
                    }
                    this.json.id = this.id;
                    let url = '{!! yzWebFullUrl('finance.balance-withdraw.examine') !!}'
                    this.json.status = this.pay_status
                    this.$http.post(url, this.json).then(function (response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                            location.reload();
                            loading.close();
                        } else {
                            if (response.data.data) {
                                if (response.data.data.status === -1) {
                                    this.dialog_visible_verify = true
                                } else {
                                    this.$message({
                                        message: response.data.msg,
                                        type: 'error'
                                    });
                                }
                            } else {
                                this.$message({
                                    message: response.data.msg,
                                    type: 'error'
                                });
                            }
                        }
                        loading.close();
                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        loading.close();
                    });
                },
                goback() {
                    window.history.back();
                },
                confirm() {
                    if (this.title === "驳回记录") {
                        this.dialogVisible = false;
                        this.dialogVisible2 = true;
                        return
                    }
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    let url = '{!! yzWebFullUrl('finance.balance-withdraw.examine') !!}'
                    this.json.id = this.id
                    this.$http.post(url, this.json).then(function (response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                            location.reload();
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                        loading.close();
                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        loading.close();
                    });
                },
                confirm2() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    let json = {}
                    if (this.submit_review) {
                        json = {
                            status: this.pay_status,
                            submit_check: 1,
                            id: this.id,
                            reject_reason: this.reject_reason
                        }
                    } else {
                        json = {
                            audited_rebut: 1,
                            id: this.id,
                            reject_reason: this.reject_reason
                        }
                    }

                    let url = '{!! yzWebFullUrl('finance.balance-withdraw.examine') !!}'

                    this.$http.post(url, json).then(function (response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                            loading.close();
                            location.reload();
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                        loading.close();
                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        loading.close();
                    });
                },
                //获取验证码 并只验证手机号 是否正确
                getCode() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post("{!! yzWebFullUrl('finance.withdraw.sendCode') !!}").then(function (response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                            this.tackBtn()
                            loading.close();
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                        loading.close();
                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        loading.close();
                    });
                },
                tackBtn() {       //验证码倒数60秒
                    let time = 60;
                    let timer = setInterval(() => {
                        if (time === 0) {
                            clearInterval(timer);
                            this.time_butont = '获取验证码';
                            this.disabled = false;
                        } else {
                            this.disabled = true;
                            this.time_butont = time + '秒后重试';
                            time--;
                        }
                    }, 1000);
                },
                check() {  //点击登录 验证手机& 验证码是否符合条件
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post("{!! yzWebFullUrl('finance.withdraw.checkVerifyCode') !!}", {code:this.check_data.code}).then(function (response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                            this.dialog_visible_verify = false
                            loading.close();
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                        loading.close();
                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        loading.close();
                    });
                },
            }
        })
    </script>


@endsection