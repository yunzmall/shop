@extends('layouts.base')

@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .vue-main{
            min-height: 0;
        }
        .content {
            background: #eff3f6;
            padding: 10px !important;
        }

        .el-form-item__label {
            width: 300px;
            text-align: right;
            color: #2f2310;
        }

        .on-submit-div {
            background-color: #fff !important;
            border-top: #f6f6f6;
            margin: 0 10px;
            border-top-style: solid;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .el-radio-button__inner, .el-radio-group {
            vertical-align: 0;
        }
    </style>
    <div id="app" v-cloak class="main">
        <div class="block">
            @include('layouts.vueTabs')
        </div>
        <el-form ref="form" :model="set">
            <div class="block">
                <div class="vue-main">
                    <div class="vue-main-form">
                        <div class="vue-main-title" style="margin-bottom:20px">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">
                                积分抵扣设置
                            </div>
                        </div>
                        <el-form-item label="积分转让">
                            <el-switch
                                    v-model="set.point_transfer"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block'>积分转让： 会员之间可以进行积分转让</span>
                            <el-input style="width: 450px;margin-left: 300px"
                                      v-if="set.point_transfer==1"
                                      v-model="set.point_transfer_poundage">
                                <template slot="append">%</template>
                                <template slot="prepend">手续费</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="积分明细显示受让人">
                            <el-switch
                                    v-model="set.show_transferor"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item label="积分抵扣">
                            <el-switch
                                    v-model="set.point_deduct"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block'>开启积分抵扣, 商品最多抵扣的数目需要在商品【营销设置】中单独设置, 否则统一设置</span>
                        </el-form-item>
                        <el-form-item label="开启默认积分抵扣">
                            <el-switch
                                    v-model="set.default_deduction"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block'>开启默认积分抵扣提交订单页面会默认开启积分抵扣按钮</span>
                            <span class='help-block' style="margin-left: 300px">注:仅支持平台自营和供应商订单</span>
                        </el-form-item>
                        <el-form-item label="积分返还">
                            <el-switch
                                    v-model="set.point_rollback"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block'>开启积分返还： 未付款订单、退款订单关闭订单后，用于抵扣的积分返还到会员积分账户</span>
                        </el-form-item>
                        <el-form-item label="积分扣除">
                            <el-switch
                                    v-model="set.point_refund"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block'>开启积分扣除：会员下单赠送出去的积分，退货就扣除赠送出去积分，积分扣除可能会变为负数。</span>
                        </el-form-item>
                        <el-form-item label="抵扣运费">
                            <el-switch
                                    v-model="set.point_freight"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block'>开启积分抵扣运费： 积分可用于抵扣运费</span>
                        </el-form-item>
                        <el-form-item label="积分仅支持抵扣整数">
                            <el-switch
                                    v-model="set.point_deduction_integer"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block'>开启后，积分商品抵扣只允许抵扣整数限制</span>
                        </el-form-item>
                        <el-form-item label="商品详情页抵扣显隐">
                            <el-switch
                                    v-model="set.goods_page_deduct_show"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="0"
                                    inactive-value="">
                            </el-switch>
                            <span class='help-block'>关闭后，前端商品详情页--活动里不会显示积分抵扣相关信息，
                                积分抵扣具体数据是根据最终的价格多次计算的（会员按商品售价计算容易误解）</span>
                        </el-form-item>
                        <el-form-item label="积分抵扣比例">
                            <el-input style="width: 450px"
                                      v-model="set.money">
                                <template slot="append">元</template>
                                <template slot="prepend">1个积分抵扣</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="商品抵扣">
                            <el-input style="width: 450px"
                                      v-model="set.money_max">
                                <template slot="append">%</template>
                                <template slot="prepend">最多可抵扣</template>
                            </el-input>
                            <span class='help-block'>商品最多可抵扣</span>
                            <el-input style="width: 450px;margin-left: 300px"
                                      v-model="set.money_min">
                                <template slot="append">%</template>
                                <template slot="prepend">最少可抵扣</template>
                            </el-input>
                            <span class='help-block' style="margin-left: 300px">商品最少抵扣比例</span>
                        </el-form-item>

                        <el-form-item label="计算方式">
                            <el-radio-group v-model="set.deduction_amount_type">
                                <el-radio label="0">订单价格:(不包括运费及抵扣金额)</el-radio>
                                <el-radio label="1">利润:(订单商品最终价格-商品成本,负数取0。不支持门店、收银台、酒店订单)</el-radio>
                            </el-radio-group>
                        </el-form-item>
                    </div>
                </div>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="vue-main">
                    <div class="vue-main-form">
                        <div class="vue-main-title" style="margin-bottom:20px">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">
                                自动转出[[set.love_name]]
                            </div>
                        </div>
                        <el-form-item :label="'自动转入'+set.love_name">
                            <el-switch
                                    v-model="set.transfer_love"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block'>会员积分自动转入可用[[set.love_name]]</span>
                        </el-form-item>
                        <el-form-item :label="'手动转入'+set.love_name">
                            <el-switch
                                    v-model="set.exchange_to_love_by_member"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block'>会员可在前端页面自行将积分转为[[set.love_name]]</span>
                        </el-form-item>
                        <el-form-item v-if="set.transfer_love==1" label="自动转入周期">
                            <el-radio-group v-model="set.transfer_cycle">
                                <el-radio label="0">每天</el-radio>
                                <el-radio label="1">每周</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item v-if="set.transfer_cycle==0&&set.transfer_love==1" label="转入时间">
                            <el-select style="width: 150px;" v-model="set.transfer_time_hour" placeholder="请选择">
                                <el-option
                                        v-for="item in cron_time"
                                        :key="item.value"
                                        :label="item.label"
                                        :value="item.value">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item v-if="set.transfer_cycle==1&&set.transfer_love==1" label="转入时间">
                            <el-select style="width: 150px;" v-model="set.transfer_time_week" placeholder="请选择">
                                <el-option
                                        v-for="(item,index) in week_data"
                                        :key="index"
                                        :label="item"
                                        :value="item">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item v-if="set.transfer_love==1" label="转入类型">
                            <el-radio-group v-model="set.transfer_compute_mode">
                                <el-radio label="0">固定值</el-radio>
                                <el-radio label="1">营业额</el-radio>
                            </el-radio-group>
                            <div class="help-block">
                                固定值：会员积分 * N% = 转出的积分
                            </div>
                            <div class="help-block" style="margin-left: 300px">
                                营业额：周期订单总和 * N% / 会员积分持有总量 * 会员持有积分 = 转出的积分
                            </div>
                        </el-form-item>
                        <el-form-item v-if="set.transfer_love==1" label=" ">
                            <el-input style="width: 450px"
                                      v-model="set.transfer_love_rate">
                                <template slot="append">%</template>
                                <template slot="prepend">自动转入比例</template>
                            </el-input>
                            <div style="margin-left: 300px">
                                <div class="help-block">
                                    可以在会员积分页面设置会员独立的转入比例，优先使用独立转入比例
                                    <br>
                                    转入类型为营业额时：会员独立的转入比例失效，使用全局比例设置
                                </div>
                                <div class="help-block">
                                    如果自动转入比例为空、为零，同时会员设置了独立比例，则只自操作有设置比例的会员积分
                                </div>
                            </div>
                        </el-form-item>
                        <el-form-item label="比例设置">
                            <el-input style="width: 450px"
                                      v-model="set.transfer_integral">
                                <template slot="append"><b>:</b></template>
                                <template slot="prepend">积分转入[[set.love_name]]</template>
                            </el-input>
                            <el-input style="width: 100px;margin-left: -5px"
                                      v-model="set.transfer_integral_love">
                            </el-input>
                            <div style="margin-left: 300px">
                                <div class="help-block">
                                    如果积分转入[[set.love_name]]比例设置为空、为零，则默认为1：1
                                </div>

                            </div>
                        </el-form-item>
                    </div>
                </div>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="vue-main">
                    <div class="vue-main-form">
                        <div class="vue-main-title" style="margin-bottom:20px">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">
                                积分转化[[set.integral_name]]
                            </div>
                        </div>
                        <el-form-item :label="'积分转化'+set.integral_name">
                            <el-switch
                                    v-model="set.is_transfer_integral"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>

                        <el-form-item v-if="set.is_transfer_integral==1" label=" ">
                            <el-input style="width: 450px"
                                      v-model="set.transfer_point_ratio">
                                <template slot="append"><b>:</b></template>
                                <template slot="prepend">转化比例</template>
                            </el-input>
                            <el-input style="width: 80px;margin-left: -5px"
                                      v-model="set.transfer_integral_ratio">
                            </el-input>
                            <span class='help-block' style="margin-left: 300px">如果比例设置为空、为零，则默认为1：1</span>
                        </el-form-item>

                    </div>
                </div>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="vue-main">
                    <div class="vue-main-form">
                        <div class="vue-main-title" style="margin-bottom:20px">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">
                                积分赠送设置
                            </div>
                        </div>
                        <el-form-item label="前端显示">
                            <el-checkbox true-label="on" v-model="set.goods_point.search_page">商品搜索页/分类页 </el-checkbox>
                            <el-checkbox true-label="on" v-model="set.goods_point.goods_page">商品详情页</el-checkbox>
                            <el-checkbox true-label="on" v-model="set.goods_point.single_page">下单页</el-checkbox>
                            <el-checkbox true-label="on" v-model="set.goods_point.order_page">订单详情页</el-checkbox>
                        </el-form-item>
                        <el-form-item label="数据显示">
                            <el-radio v-model="set.data_display_type" label="0">赠送比例</el-radio>
                            <el-radio v-model="set.data_display_type" label="1">赠送数量</el-radio>
                            <span class='help-block' >赠送比例：商品设置的是百分比显示百分比，比如：设置3%，前端显示3%</span>
                            <span class='help-block' style="margin-left: 300px">赠送数量：商品设置的是百分比显示以商品现价*赠送比例显示</span>
                        </el-form-item>
                        <el-form-item label="购买商品赠送规则">
                            <el-radio v-model="set.give_type" label="0">实付金额</el-radio>
                            <el-radio v-model="set.give_type" label="1">商品利润</el-radio>
                            <span class='help-block' >实付金额:订单商品实际支付金额</span>
                            <span class='help-block' style="margin-left: 300px">商品利润:订单商品实际支付金额-商品成本,负数取0。门店和收银台利润按平台提成计算</span>
                        </el-form-item>
                        <el-form-item label="购买商品赠送积分">
                            <el-input style="width: 450px" v-model="set.give_point">
                                <template slot="append"><b>积分</b></template>
                                <template slot="prepend">购买商品赠送</template>
                            </el-input>
                            <br>
                            <el-input style="width: 450px;margin-top: 20px" v-model="set.first_parent_point">
                                <template slot="append"><b>积分</b></template>
                                <template slot="prepend">会员一级上级赠送</template>
                            </el-input>
                            <br>
                            <el-input style="width: 450px;margin-left: 300px;margin-top: 20px" v-model="set.second_parent_point">
                                <template slot="append"><b>积分</b></template>
                                <template slot="prepend">会员二级上级赠送</template>
                            </el-input>
                            <span class='help-block' style="margin-left: 300px">例: 购买2件，设置10 积分, 不管成交价格是多少， 赠送积分 = 20积分</span>
                            <span class='help-block' style="margin-left: 300px">例: 购买2件，设置10%积分, 赠送积分 = 实付金额 * 2 * 10% 或 商品利润 * 2 * 10%</span>
                        </el-form-item>
                        <el-form-item label="余额支付不赠送积分">
                            <el-switch
                                    v-model="set.balance_pay_reward"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block'>
                                开启后，订单支付类型为余额支付的，不奖励[商品赠送]积分，包括门店，收银台
                            </span>
                        </el-form-item>
                        <el-form-item label="消费赠送类型">
                            <el-radio v-model="set.point_award_type" label="1">百分比</el-radio>
                            <el-radio v-model="set.point_award_type" label="0">固定数值</el-radio>
                            <span class='help-block' >百分比:单笔订单满200元, 设置10积分, 成交价格200元, 则购买后获得 20 积分（200*10%）</span>
                            <span class='help-block' style="margin-left: 300px">固定数值:单笔订单满200元, 设置10积分, 成交价格200元, 则购买后获得 10 积分</span>
                        </el-form-item>
                        <el-form-item label="消费赠送">
                            <span class="help-block">两项都填写才能生效 <span style="color:green; font-weight:bold">且阶梯优先级最大</span></span>
                            <el-input style="width: 290px;margin-left: 300px" v-model="set.enough_money">
                                <template slot="append"><b>元 赠送</b></template>
                                <template slot="prepend">单笔订单满</template>
                            </el-input>
                            <el-input style="width: 150px;margin-left: -5px" v-model="set.enough_point">
                                <template slot="append"><b>积分</b></template>
                            </el-input>
                            <br>
                            <el-button @click="addEnoughs" style="margin-left: 300px;margin-top: 20px"><i class='fa fa-plus'></i> 增加项</el-button>
                            <div v-for="(item,index) in set.enoughs" style="margin-top: 20px;margin-bottom: 20px">
                                <el-input style="width: 290px;margin-left: 300px" v-model="item.enough">
                                    <template slot="append"><b>元 赠送</b></template>
                                    <template slot="prepend">单笔订单满</template>
                                </el-input>
                                <el-input style="width: 150px;margin-left: -5px" v-model="item.give">
                                    <template slot="append"><b>积分</b></template>
                                </el-input>
                                <button class='btn btn-danger' type='button'
                                        @click="removeConsumeItem(index)"><i class='fa fa-remove'></i>
                                </button>
                            </div>
                        </el-form-item>
                    </div>
                </div>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="vue-main">
                    <div class="vue-main-form">
                        <div class="vue-main-title" style="margin-bottom:20px">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">
                                绑定手机号奖励设置
                            </div>
                        </div>
                        <el-form-item label="绑定手机号奖励设置">
                            <el-switch
                                    v-model="set.bind_mobile_award"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block' style="margin-left: 300px">开启该功能后，用户绑定手机号可获得一次性奖励（包含手机号注册会员）</span>
                        </el-form-item>
                        <el-form-item v-if="set.bind_mobile_award==1" label=" ">
                            <el-input style="width: 450px" v-model="set.bind_mobile_award_point">
                                <template slot="append"><b>积分</b></template>
                            </el-input>
                        </el-form-item>
                    </div>
                </div>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="vue-main">
                    <div class="vue-main-form">
                        <div class="vue-main-title" style="margin-bottom:20px">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">
                                积分奖励设置
                            </div>
                        </div>
                        <el-form-item label="收入提现赠送：">
                            <el-switch
                                    v-model="set.income_withdraw_award"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block' style="margin-left: 300px">收入提现：收入提现奖励提现手续费等值积分【比例1：1】</span>
                        </el-form-item>
                        <el-form-item label="收入提现赠送比例：">
                            <el-switch
                                    v-model="set.income_withdraw_award_scale"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                            <span class='help-block' style="margin-left: 300px">奖励积分=收入提现审核金额x设置比例</span>
                        </el-form-item>
                        <el-form-item v-if="set.income_withdraw_award_scale==1" label=" ">
                            <el-input style="width: 450px" v-model="set.income_withdraw_award_scale_point">
                                <template slot="append"><b>%</b></template>
                            </el-input>
                        </el-form-item>
                    </div>
                </div>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
                <div class="vue-main">
                    <div class="vue-main-form">
                        <div class="vue-main-title" style="margin-bottom:20px">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">
                                积分不足消息提醒设置
                            </div>
                        </div>
                        <el-form-item label="积分不足消息通知开关">
                            <el-switch
                                    v-model="set.point_floor_on"
                                    active-color="#13ce66"
                                    inactive-color="#bfbfbf"
                                    active-value="1"
                                    inactive-value="0">
                            </el-switch>
                        </el-form-item>
                        <el-form-item v-if="set.point_floor_on==1" label="积分不足">
                            <el-input style="width: 290px;" v-model="set.point_floor">
                                <template slot="append">积分</template>
                            </el-input>
                        </el-form-item>
                        <el-form-item v-if="set.point_floor_on==1" label="消息通知类型">
                            <el-radio-group v-model="set.point_message_type">
                                <el-radio :label="1">指定会员</el-radio>
                                <el-radio :label="2">指定会员等级</el-radio>
                                <el-radio  :label="3">指定会员分组</el-radio>
                            </el-radio-group>
                            <div style="margin-left:300px;margin-top: 20px" v-if="set.point_message_type==1">
                                <label class="note-span">选择指定会员：</label>
                                <el-input style="width: 350px;" v-model="set.uids"></el-input>
                                <span class='help-block'>请填写会员id，会员id之间用英文逗号隔开</span>
                            </div>
                            <div style="margin-left:300px;margin-top: 20px" v-if="set.point_message_type==2">
                                <label class="note-span">使用条件 - 会员等级</label>
                                <el-select style="width: 150px;" v-model="set.level_limit" placeholder="请选择">
                                    <el-option
                                            v-for="item in memberLevels"
                                            :key="item.id"
                                            :label="item.level_name"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </div>
                            <div style="margin-left:300px;margin-top: 20px" v-if="set.point_message_type==3">
                                <label class="note-span">使用条件 - 会员分组</label>
                                <el-select style="width: 150px;" v-model="set.group_type" placeholder="请选择">
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
                </div>
            </div>
            <!-- 分页 -->
            <div class="vue-page" style="text-align: center">
                <el-button type="primary" @click="onSubmit">提交</el-button>
            </div>

        </el-form>
    </div>
    <script>
        var vm = new Vue({
            el: '#app',
            // 防止后端冲突,修改ma语法符号
            delimiters: ['[[', ']]'],
            data() {
                return {
                    set: {
                        goods_point: {
                            search_page: "on",
                            goods_page: "",
                            single_page: "",
                            order_page: ""
                        },
                    },
                    activeName: 'basic_set',
                    tab_list: [],
                    cron_time:[],
                    memberLevels: [],
                    group_type: [],
                    week_data:[]
                }
            },
            created() {
                this.cronSelect()
                this.getData(1)
            },
            //定义全局的方法
            beforeCreate() {
            },
            filters: {},
            methods: {
                addEnoughs() {
                    if (typeof(this.set.enoughs) === 'undefined') {
                        this.$set(this.set,'enoughs',[])
                    }
                    this.set.enoughs.push({
                        'enough': '',
                        'give': ''
                    })
                },
                cronSelect() {
                    for (let i = 0; i < 24; i++) {
                        this.cron_time.push(
                            {
                                value: i + 1 + '',
                                label: '每天' + i + ':00' + '点转入'
                            }
                        )
                    }
                },
                getData() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('finance.point-set.index') !!}').then(function (response) {
                        if (response.data.result) {
                            this.set = response.data.data.set
                            this.tab_list = response.data.data.tab_list
                            this.memberLevels = response.data.data.memberLevels
                            this.group_type = response.data.data.memberGroups
                            this.week_data = response.data.data.week_data
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
                onSubmit() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('finance.point-set.index') !!}', {
                        set: this.set
                    }).then(function (response) {
                        if (response.data.result) {
                            this.set = response.data.data.set
                            this.tab_list = response.data.data.tab_list
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
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
                removeConsumeItem(index)
                {
                    this.set.enoughs.splice(index, 1)
                },
                getUrl() {
                    let url = ''
                    switch (this.activeName) {
                        case 'member_point' :
                            url = '{!! yzWebFullUrl('finance.point-member.index') !!}';
                            break;
                        case 'basic_set' :
                            url = '{!! yzWebFullUrl('finance.point-set.index') !!}';
                            break;
                        case 'recharge_record' :
                            url = '{!! yzWebFullUrl('point.recharge-records.index') !!}';
                            break;
                        case 'point_detailed' :
                            url = '{!! yzWebFullUrl('point.records.index') !!}';
                            break;
                        case 'point_queue' :
                            url = '{!! yzWebFullUrl('point.queue.index') !!}';
                            break;
                        case 'queue_detailed' :
                            url = '{!! yzWebFullUrl('point.queue-log.index') !!}';
                            break;
                        case 'superior_queue' :
                            url = '{!! yzWebFullUrl('point.queue-log.parentIndex') !!}';
                            break;
                    }
                    return url
                },
                handleClick() {
                    window.location.href = this.getUrl()
                },
            },
        })
    </script>

@endsection