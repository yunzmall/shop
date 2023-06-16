@extends('layouts.base')
@section('title', '余额设置')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .el-form-item__label {
            width: 300px;
            text-align: right;
        }
        .alert.alert-warning {
            border: 1px;
            color: red;
            border-radius: 3px;
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(255, 152, 0, 0.4);
            background-color: #fcf4f4;
        }
        .note-span {
            width: 290px;
            text-align: right;
            margin-right: 10px;
        }

        .el-radio-group {
            display: inline-block;
            line-height: 1;
            vertical-align: inherit;
        }
        .content{
            background: #eff3f6;
            padding: 10px!important;
        }
        .con{
            padding-bottom:40px;
            position:relative;
            border-radius: 8px;
            min-height:100vh;
        }
        .con .setting .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
        }
        .con .setting .block .title{
            font-size:18px;
            margin-bottom:15px;
            display:flex;
            align-items:center;
        }
        .con .confirm-btn{
            width: calc(100% - 266px);
            position:fixed;
            bottom:0;
            right:0;
            margin-right:10px;
            line-height:63px;
            background-color: #ffffff;
            box-shadow: 0px 8px 23px 1px
            rgba(51, 51, 51, 0.3);
            background-color:#fff;
            text-align:center;
        }
        b{
            font-size:14px;
        }
        .el-checkbox__inner{
            border:solid 1px #56be69!important;
        }
    </style>
    <div id="app" class="main rightlist">
        <div class="con">
            <div class="setting">

        <div style="align-content: center">
            <el-form ref="form" :model="balance">
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>余额设置</b></div>
                    <div class="alert alert-warning alert-important" style="margin-left:300px;text-align: center;border-style:solid !important;width: 500px;border-color: red !important;border-radius: 5px">
                        余额支付开关、及其他支付设置，请到支付方式查看<a style="color: red" href="{{ yzWebUrl('setting.shop.pay') }}" target="_blank">【点击支付方式设置】</a>.
                    </div>
                    <el-form-item label="开启账户充值">
                        <el-switch
                                v-model="balance.recharge"
                                active-color="#13ce66"
                                inactive-color="#bfbfbf"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                        <span class='help-block'>是否允许用户对账户余额进行充值</span>
                    </el-form-item>
                    <el-form-item label=" " v-if="balance.recharge==1">
                        <el-radio-group v-model="balance.recharge_activity">
                            <el-radio :label="1">启用充值活动</el-radio>
                            <el-radio :label="0">关闭充值活动</el-radio>
                            <el-radio v-if="re_recharge_activity==1" :label="2">重置充值活动</el-radio>
                            <span class='help-block'>开启时需选择活动开始及结束时间、会员最多参与次数(-1，0，空则不限参与次数)，重置充值活动：开启新充值活动统计</span>

                            <div v-if="balance.recharge_activity">
                                <el-input style="width: 350px;margin-right: 20px"
                                          v-model="balance.recharge_activity_fetter">
                                    <template slot="append">次</template>
                                    <template slot="prepend">会员最多参与次数</template>
                                </el-input>
                                <el-date-picker
                                        v-model="recharge_activity_time"
                                        type="datetimerange"
                                        :picker-options="pickerOptions"
                                        value-format="timestamp"
                                        range-separator="至"
                                        start-placeholder="开始日期"
                                        end-placeholder="结束日期"
                                        align="right">
                                </el-date-picker>
                            </div>

                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label=" " v-if="balance.recharge==1">
                        <el-radio-group v-model="balance.proportion_status">
                            <el-radio label="0">赠送固定金额</el-radio>
                            <el-radio label="1">赠送充值比例</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label=" " v-if="balance.recharge==1">
                        充值满额送：
                        <el-button @click="addRechargeItem"><i class='fa fa-plus'></i> 增加优惠项</el-button>
                        <div v-for="(item,index) in balance.sale" :key="index">
                            <el-input style="width: 350px;margin-left: 300px;margin-top: 20px" v-model="item.enough">
                                <template slot="append">赠送</template>
                                <template slot="prepend">满</template>
                            </el-input>

                            <el-input style="width: 350px;;margin-top: 20px" v-model="item.give">
                                <template v-if="balance.proportion_status==1" slot="append">%</template>
                                <template v-if="balance.proportion_status==0" slot="append">元</template>
                            </el-input>
                            <el-button style="margin-top: 20px" class='btn btn-danger' type='button'
                                       @click="removeRechargeItem(index)"><i
                                        class='fa fa-remove'></i>
                            </el-button>
                        </div>
                        <div v-if="balance.recharge==1" style="margin-left: 300px;line-height: 30px;margin-top: 10px">
                            <span class="help-block">两项都填写才能生效</span>
                            <span class="help-block">赠送固定金额：充值满100，赠送10元,实际赠送10元</span>
                            <span class="help-block">赠送充值比例：充值满200，赠送15%，实际赠送30【200*15%】元</span>
                        </div>
                    </el-form-item>
                </div>
                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>余额转账设置</b></div>
                    <el-form-item label="开启余额转账">
                        <el-switch
                                v-model="balance.transfer"
                                active-color="#13ce66"
                                inactive-color="#bfbfbf"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                        <span class='help-block'>是否允许用户对账户余额进行转账</span>
                    </el-form-item>
                    <el-form-item label="余额团队转账">
                        <el-switch
                                v-model="balance.team_transfer"
                                active-color="#13ce66"
                                inactive-color="#bfbfbf"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                        <span class='help-block'>开启后用户只能对团队成员转账</span>
                    </el-form-item>
                </div>
                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <div class="block" v-if="is_open">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>余额转换[[love_name]]设置</b></div>
                    <el-form-item :label="'余额转换'+love_name">
                        <el-switch
                                v-model="balance.love_swich"
                                active-color="#13ce66"
                                inactive-color="#bfbfbf"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                        <div>
                            <el-input v-if="balance.love_swich==1" style="width: 350px;;margin-top: 20px"
                                      v-model="balance.love_rate">
                                <template slot="prepend">余额转换比例</template>
                                <template slot="append">%</template>
                            </el-input>
                            <span style="margin-left: 300px;" class='help-block'>转化实例:实际转化10个 [[love_name]],余额转化比例10%，则需要10 / 10%，比例为空或为0则默认为1:1</span>
                        </div>

                    </el-form-item>
                </div>
                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>消息提醒</b></div>
                    <el-form-item label="开启余额定时提醒">
                        <el-switch
                                v-model="balance.sms_send"
                                active-color="#13ce66"
                                inactive-color="#bfbfbf"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                        <div style="margin-left: 300px;" v-if="balance.sms_send==1">
                            <el-select style="width: 150px;" v-model="balance.sms_hour" placeholder="请选择">
                                <el-option
                                        v-for="item in cron_time"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value">
                                </el-option>
                            </el-select>
                            <el-input style="width: 350px;" v-model="balance.sms_hour_amount">
                                <template slot="prepend">金额超过</template>
                                <template slot="append">元</template>
                            </el-input>
                        </div>
                        <span style="margin-left: 300px;" class='help-block'>重新设置时间后一定要在一分钟后重启队列，若不重启该设置则会在第二天才生效</span>
                    </el-form-item>
                    <el-form-item label="开启余额不足消息提醒">
                        <el-switch
                                v-model="balance.blance_floor_on"
                                active-color="#13ce66"
                                inactive-color="#bfbfbf"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                    </el-form-item>
                    <el-form-item label="" v-if="balance.blance_floor_on==1">
                        <div>
                            <label class="note-span">余额不足</label>
                            <el-input style="width: 350px;" v-model="balance.blance_floor">
                                <template slot="prepend">余额不足</template>
                                <template slot="append">元</template>
                            </el-input>
                        </div>
                        <div>
                            <label class="note-span">消息通知类型</label>
                            <el-radio-group v-model="balance.balance_message_type">
                                <el-radio label="1">指定会员</el-radio>
                                <el-radio label="2">指定会员等级</el-radio>
                                <el-radio label="3">指定会员分组</el-radio>
                            </el-radio-group>
                        </div>
                        <div style="margin-top: 20px" v-if="balance.balance_message_type==1">
                            <label class="note-span">选择指定会员：</label>
                            <el-input style="width: 350px;" v-model="balance.uids"></el-input>
                            <span style="margin-left: 300px" class='help-block'>请填写会员id，会员id之间用英文逗号隔开</span>
                        </div>
                        <div style="margin-top: 20px" v-if="balance.balance_message_type==2">
                            <label class="note-span">使用条件 - 会员等级</label>
                            <el-select style="width: 150px;" v-model="balance.level_limit" placeholder="请选择">
                                <el-option
                                        v-for="item in memberLevels"
                                        :key="item.id"
                                        :label="item.level_name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </div>
                        <div style="margin-top: 20px" v-if="balance.balance_message_type==3">
                            <label class="note-span">使用条件 - 会员分组</label>
                            <el-select style="width: 150px;" v-model="balance.group_type" placeholder="请选择">
                                <el-option
                                        v-for="item in group_type"
                                        :key="item.id"
                                        :label="item.group_name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </div>
                    </el-form-item>
                </div>
                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>余额抵扣设置</b></div>
                    <el-form-item label="余额抵扣">
                        <el-switch
                                v-model="balance.balance_deduct"
                                active-color="#13ce66"
                                inactive-color="#bfbfbf"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                        <span style="margin-left: 300px" class='help-block'>开启后订单不支持余额支付</span>
                        <div style="margin-top: 20px" v-if="balance.balance_deduct==1">
                            <label class="note-span">商品抵扣</label>
                            <el-input style="width: 350px;" v-model="balance.money_max">
                                <template slot="append">%</template>
                                <template slot="prepend">最多可抵扣</template>
                            </el-input>
                            <span style="margin-left: 300px" class='help-block'>商品最高抵扣比例</span>
                        </div>
                    </el-form-item>
                    <el-form-item label="余额返还">
                        <el-switch
                                v-model="balance.balance_deduct_rollback"
                                active-color="#13ce66"
                                inactive-color="#bfbfbf"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                        <span style="margin-left: 300px" class='help-block'>开启余额返还：未付款订单、退款订单关闭订单后，用于抵扣的余额返还到会员账户</span>
                    </el-form-item>
                </div>
                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <div class="block">
                    <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>余额充值设置</b></div>

                    <el-form-item label="充值余额赠送积分">
                        <el-switch
                                v-model="balance.charge_reward_swich"
                                active-color="#13ce66"
                                inactive-color="#bfbfbf"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                        <div style="margin-top: 20px" v-if="balance.charge_reward_swich==1">
                            <el-input style="margin-left: 300px;width: 350px;" v-model="balance.charge_reward_rate">
                                <template slot="append">%</template>
                                <template slot="prepend">余额转换比例</template>
                            </el-input>
                            <span style="margin-left: 300px" class='help-block'>转化实例:实际赠送10积分,余额转化比例10%，则需要充值10 / 10%，比例为空或为0则默认为1:1</span>
                        </div>
                    </el-form-item>
                    <el-form-item label="充值余额审核">
                        <el-switch
                                v-model="balance.charge_check_swich"
                                active-color="#13ce66"
                                inactive-color="#bfbfbf"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                        <span style="margin-left: 300px" class='help-block'>开启后，后台手动充值余额需要审核后才充值成功，与前端充值以及后台批量充值无关</span>
                    </el-form-item>
                    <el-form-item label="收入提现赠送余额">
                        <el-switch
                                v-model="balance.income_withdraw_award"
                                active-color="#13ce66"
                                inactive-color="#bfbfbf"
                                active-value="1"
                                inactive-value="0"
                        >
                        </el-switch>
                        <div v-if="balance.income_withdraw_award==1">
                            <div style="margin-top: 20px">
                                <label class="note-span">赠送余额比例 (统一设置)</label>
                                <el-input style="width: 350px;" v-model="balance.income_withdraw_award_rate">
                                    <template slot="append">%</template>
                                </el-input>
                            </div>
                            <div style="margin-top: 20px">
                                <label class="note-span">赠送余额比例 (微信独立)</label>
                                <el-input style="width: 350px;" v-model="balance.income_withdraw_wechat_rate">
                                    <template slot="append">%</template>
                                </el-input>
                            </div>
                            <div v-if="high_light_open==1" style="margin-top: 20px">
                                <label class="note-span">赠送余额比例 (高灯独立)</label>
                                <el-input style="width: 350px;" v-model="balance.income_withdraw_light_rate">
                                    <template slot="append">%</template>
                                </el-input>
                            </div>
                        </div>
                    </el-form-item>
                    <el-form-item v-if="balance.income_withdraw_award==1" label="收入提现赠送说明">
                        <el-input
                                style="width: 600px;"
                                type="textarea"
                                placeholder="请输入内容"
                                v-model="balance.income_withdraw_award_explain">
                        </el-input>
                        <span style="margin-left: 300px" class='help-block'>收入提现页面提现成功自定义提示语，变量：[奖励余额]</span>
                    </el-form-item>
                </div>
                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <!-- 分页 -->
                <div class="vue-page" style="text-align: center">
                        <el-button type="primary" @click="onSubmit">提交</el-button>
                </div>

            </el-form>
        </div>
            </div>
        </div>
    </div>
    <script>
        var app = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                return {
                    balance: {},
                    love_name: '',
                    cron_time: [],
                    re_recharge_activity: 0,
                    memberLevels: [],
                    group_type: [],
                    is_open: '',
                    recharge_activity_time: [],
                    high_light_open: 0,
                    pickerOptions: {
                        shortcuts: [{
                            text: '最近一周',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                                picker.$emit('pick', [start, end]);
                            }
                        }, {
                            text: '最近一个月',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                                picker.$emit('pick', [start, end]);
                            }
                        }, {
                            text: '最近三个月',
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                                picker.$emit('pick', [start, end]);
                            }
                        }]
                    },
                }
            },
            created() {
                this.cronSelect()
                this.getData()
            },
            mounted() {

            },
            methods: {
                cronSelect() {
                    for (let i = 1; i < 24; i++) {
                        this.cron_time.push(
                            {
                                value: i + '',
                                label: '每天' + i + ':00' + '点'
                            }
                        )
                    }
                },
                getData() {
                    this.$http.post("{!! yzWebFullUrl('finance.balance-set.see') !!}", {}).then(response => {
                        if (response.data.result == 1) {
                            this.balance = response.data.data.balance
                            if (typeof(this.balance.sale) === 'undefined') {
                                this.$set(this.balance,'sale',[])
                            }
                            this.re_recharge_activity = response.data.data.balance.recharge_activity
                            this.love_name = response.data.data.love_name
                            this.memberLevels = response.data.data.memberLevels
                            this.group_type = response.data.data.group_type
                            this.is_open = response.data.data.is_open
                            this.recharge_activity_time[0] = this.balance.recharge_activity_start ? this.balance.recharge_activity_start : ''
                            this.recharge_activity_time[1] = this.balance.recharge_activity_end ? this.balance.recharge_activity_end : ''
                            this.high_light_open = response.data.data.high_light_open
                            console.log(this.recharge_activity_time)
                        } else {
                            this.$message.error(response.data.msg);
                        }
                        this.table_loading = false;
                    }), function (res) {
                        console.log(res);
                        this.table_loading = false;
                    };
                },
                onSubmit() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.balance.recharge_activity_time = {
                        start: this.recharge_activity_time[0],
                        end: this.recharge_activity_time[1]
                    }
                    console.log(this.balance.recharge_activity_time)
                    this.$http.post("{!! yzWebFullUrl('finance.balance-set.store') !!}", {'balance': this.balance}).then(response => {
                        if (response.data.result == 1) {
                            this.$message.success(response.data.msg);
                            loading.close()
                        } else {
                            this.$message.error(response.data.msg);
                            loading.close()
                        }
                        this.table_loading = false;
                    }), function (res) {
                        console.log(res);
                        this.table_loading = false;
                    };
                },
                addRechargeItem() {
                    this.balance.sale.push({
                        'enough': 0,
                        'give': 0
                    })
                },
                removeRechargeItem(index) {
                    this.balance.sale.splice(index, 1)
                }
            },
        })
    </script>
@endsection
