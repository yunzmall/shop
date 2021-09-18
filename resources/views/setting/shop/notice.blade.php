@extends('layouts.base')

@section('content')
    <style>
        .panel{
            margin-bottom:10px!important;
            padding-left: 20px;
            border-radius: 10px;
        }
        .panel .active a {
            background-color: #29ba9c!important;
            border-radius: 18px!important;
            color:#fff;
        }
        .panel a{
            border:none!important;
            background-color:#fff!important;
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
        .confirm-btn{
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
            z-index:99;
        }
        b{
            font-size:14px;
        }

        .add-people{
            width: 91px;
            height: 91px;
            border: dashed 1px #dde2ee;
            display:flex;
            flex-direction:column;
            justify-content:center;
            align-items:center;
        }
    </style>
    <div id='re_content' >
        @include('layouts.newTabs')
        <div class="con">
            <div class="setting">
                <el-form ref="form"  label-width="15%">
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>基础设置
                            </b>
                            <i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
                                <el-popover
                                        placement="bottom-start"
                                        title="提示"
                                        width="400"
                                        trigger="hover"
                                        content="请将公众平台模板消息所在行业选择为： IT科技/互联网|电子商务。
                开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭按钮，再选择自定义消息模版，选择默认消息模板关闭按钮则不会收到消息提醒">
                                    <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
                                </el-popover>
                            </i>
                        </div>
                        <el-form-item label="商城消息提醒">
                            <template>
                                <el-switch
                                        v-model="yz_notice.toggle"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div>提示：控制商城全部消息（包含插件消息）</div>
                        </el-form-item>
                        <el-form-item label="两级消息通知">
                            <template>
                                <el-switch
                                        v-model="yz_notice.other_toggle"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div>开启：会员可以收到一级、二级下线下单、付款、发货、收货通知（使用任务处理通知）建议使用业务处理通知模板消息编号: OPENTM207574677</div>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>积分变动通知
                            </b></div>
                        <el-form-item label="积分变动通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('point_change')"
                                        v-model="default_temp.point_change"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="积分变动通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.point_change" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.point_change"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: OPENTM207509450</div>
                        </el-form-item>
                        <el-form-item label="积分不足通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.point_deficiency" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.point_deficiency"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>

                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>余额变动通知
                            </b></div>
                        <el-form-item label="余额变动通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('balance_change')"
                                        v-model="default_temp.balance_change"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="余额变动通知">
                            <template>
                                <el-select style="width:50%;"filterable placeholder="请选择"  v-model="yz_notice.balance_change" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.balance_change"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: OPENTM401833445</div>
                        </el-form-item>
                        <el-form-item label="余额不足通知">
                            <template>
                                <el-select style="width:50%;"  filterable placeholder="请选择" v-model="yz_notice.balance_deficiency" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.balance_deficiency"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                        </el-form-item>
                        <el-form-item label="余额提现提交通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('withdraw_submit')"
                                        v-model="default_temp.withdraw_submit"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="余额提现提交通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.withdraw_submit" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.withdraw_submit"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: TM00979</div>
                        </el-form-item>
                        <el-form-item label="余额提现成功通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('withdraw_success')"
                                        v-model="default_temp.withdraw_success"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="余额提现成功通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.withdraw_success" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.withdraw_success"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: TM00980</div>
                        </el-form-item>
                        <el-form-item label="余额提现失败默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('withdraw_fail')"
                                        v-model="default_temp.withdraw_fail"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="余额提现失败通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.withdraw_fail" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.withdraw_fail"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号:TM00981</div>
                        </el-form-item>
                        <el-form-item label="余额提现驳回通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.withdraw_reject" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.withdraw_reject"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号:TM00982</div>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>卖家通知
                            </b></div>
                        <el-form-item label="购买商品通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('buy_goods_msg')"
                                        v-model="default_temp.buy_goods_msg"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="购买商品通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.buy_goods_msg" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.buy_goods_msg"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                        </el-form-item>
                        <el-form-item label="订单生成通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('seller_order_create')"
                                        v-model="default_temp.seller_order_create"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="订单生成通知方式">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.seller_order_create" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.seller_order_create"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                        </el-form-item>
                        <el-form-item label="订单支付通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('seller_order_pay')"
                                        v-model="default_temp.seller_order_pay"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="订单支付通知方式">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.seller_order_pay" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.seller_order_pay"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: OPENTM207525131</div>
                        </el-form-item>
                        <el-form-item label="订单完成通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('seller_order_finish')"
                                        v-model="default_temp.seller_order_finish"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="订单完成通知方式">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.seller_order_finish" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.seller_order_finish"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: OPENTM413711838</div>
                        </el-form-item>
                        <el-form-item label="申请退款/退货/换货申请通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('order_refund_apply_to_saler')"
                                        v-model="default_temp.order_refund_apply_to_saler"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="申请退款/退货/换货申请通知方式">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.order_refund_apply_to_saler" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.order_refund_apply_to_saler"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: OPENTM414174084</div>
                        </el-form-item>
                        <el-form-item label="通知人">
                            <div style="display:flex;">
                                <div class="good" v-for="(item,index,key) in yz_notice.salers" style="width:91px;display:flex;margin-right:20px;flex-direction: column">
                                    <div class="img" style="position:relative;">
                                        <a style="color:#333;"><div style="width: 20px;height: 20px;background-color: #dde2ee;display:flex;align-items:center;justify-content:center; #999999;position:absolute;right:-10px;top:-10px;border-radius:50%;" @click="delPeople(item)">X</div></a>
                                        <img :src="item.avatar" style="width:91px;height:91px;">
                                    </div>
                                    <div style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;font-size:12px;">[[item.nickname]]</div>
                                </div>
                                <div class="add-people" @click="openPeople">
                                    <a style="font-size:32px;color: #999999;"><i class="el-icon-plus" ></i></a>
                                    <div style="color: #999999;">添加人员</div>
                                </div>
                            </div>
                        </el-form-item>
                        <el-form-item label="通知方式">
                            <template>
                                <el-checkbox v-model="yz_notice.notice_enable.created">下单通知</el-checkbox>
                                <el-checkbox v-model="yz_notice.notice_enable.paid" >付款通知</el-checkbox>
                                <el-checkbox v-model="yz_notice.notice_enable.received" >买家确认收货通知</el-checkbox>
                            </template>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>买家通知
                            </b></div>
                        <el-form-item label="订单提交成功通知(买家)">
                            <template>
                                <el-switch
                                        @change="changeVal('order_submit_success')"
                                        v-model="default_temp.order_submit_success"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="订单提交成功通知默认模板">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.order_submit_success" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.order_submit_success"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: OPENTM200746866</div>
                        </el-form-item>
                        <el-form-item label="订单取消通知方式">
                            <template>
                                <el-switch
                                        @change="changeVal('order_cancel')"
                                        v-model="default_temp.order_cancel"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="订单取消通知(买家)">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.order_cancel" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.order_cancel"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: OPENTM412815063</div>
                        </el-form-item>
                        <el-form-item label="订单支付成功通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('order_pay_success')"
                                        v-model="default_temp.order_pay_success"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="订单支付成功通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.order_pay_success"  @change="getSelect">
                                    <el-option
                                            v-for="item in temp.order_pay_success"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: OPENTM204987032</div>
                        </el-form-item>
                        <el-form-item label="订单发货通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('order_send')"
                                        v-model="default_temp.order_send"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="订单发货通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择"  v-model="yz_notice.order_send" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.order_send"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: OPENTM413713493</div>
                        </el-form-item>
                        <el-form-item label="订单确认收货通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('order_finish')"
                                        v-model="default_temp.order_finish"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="订单确认收货通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.order_finish" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.order_finish"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: OPENTM411450578</div>
                        </el-form-item>
                        <el-form-item label="退款申请通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('order_refund_apply')"
                                        v-model="default_temp.order_refund_apply"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="退款申请通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.order_refund_apply" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.order_refund_apply"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: TM00431</div>
                        </el-form-item>
                        <el-form-item label="退款成功通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('order_refund_success')"
                                        v-model="default_temp.order_refund_success"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="退款成功通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.order_refund_success" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.order_refund_success"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: TM00430</div>
                        </el-form-item>
                        <el-form-item label="退款申请驳回通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('order_refund_reject')"
                                        v-model="default_temp.order_refund_reject"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="退款申请驳回通知">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择"  v-model="yz_notice.order_refund_reject" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.order_refund_reject"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号: TM00432</div>
                        </el-form-item>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title">
                            <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span>
                            <b>其他通知
                            </b></div>
                        <el-form-item label="会员升级通知默认模板">
                            <template>
                                <el-switch
                                        @change="changeVal('customer_upgrade')"
                                        v-model="default_temp.customer_upgrade"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="会员升级通知方式">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.customer_upgrade" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.customer_upgrade"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                            <div>通知公众平台模板消息编号:  OPENTM400341556</div>
                        </el-form-item>
                        <el-form-item label="两级消息通知">
                            <template>
                                <el-switch
                                        @change="changeVal('other_toggle_temp')"
                                        v-model="default_temp.other_toggle_temp"
                                        :active-value="1"
                                        :inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <span><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                    placement="bottom-start"
                    title="提示"
                    width="400"
                    trigger="hover"
                    content="开启默认模版消息，无需进行额外设置。如需进行个性化消息推送，需要先关闭
按钮，再选择自定义消息模版,选择默认消息模板关闭按钮则不会收到消息提醒">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></span>
                        </el-form-item>
                        <el-form-item label="两级消息通知默认模板">
                            <template>
                                <el-select style="width:50%;" filterable placeholder="请选择" v-model="yz_notice.other_toggle_temp" @change="getSelect">
                                    <el-option
                                            v-for="item in temp.other_toggle_temp"
                                            :label="item.title"
                                            :value="item.id">
                                    </el-option>
                                </el-select>
                            </template>
                        </el-form-item>
                    </div>
            </div>
            <div class="confirm-btn">
                <el-button type="primary" @click="submit">提交</el-button>
            </div>
        </div>
        </el-form>
        <el-dialog :visible.sync="peopleShow" width="60%" center title="选择通知人">
            <div style="text-align:center;">
                <el-input  style="width:80%" v-model="keyword"></el-input>
                <el-button @click="search" style="margin-left:10px;" type="primary">搜索</el-button>
            </div>
            <el-table :data="people_list" style="width: 100%;height:500px;overflow:auto">
                <el-table-column label="通知人信息" align="center">
                    <template slot-scope="scope" >
                        <div v-if="scope.row" style="display:flex;align-items: center;">
                            <img v-if="scope.row.avatar" :src="scope.row.avatar"  style="width:50px;height:50px" />
                            <div style="margin-left:10px">[[scope.row.nickname]]</div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column label="手机" prop="mobile" align="center" ></el-table-column>
                <el-table-column label="会员ID" prop="uid" align="center" ></el-table-column>
                <el-table-column prop="refund_time" label="操作" align="center" >
                    <template slot-scope="scope">
                        <el-button @click="surePeople(scope.row)">
                            选择
                        </el-button>

                    </template>
                </el-table-column>
            </el-table>
        </el-dialog>
    </div>
    </div>
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    keyword:'',
                    people_list:[],
                    peopleShow:false,
                    set:{},
                    default_temp:{},
                    temp:{
                        point_change:[],
                        point_deficiency:[],
                        balance_change:[],
                        balance_deficiency:[],
                        withdraw_submit:[],
                        withdraw_success:[],
                        withdraw_fail:[],
                        withdraw_reject:[],
                        buy_goods_msg:[],
                        seller_order_create:[],
                        seller_order_pay:[],
                        seller_order_finish:[],
                        order_refund_apply_to_saler:[],
                        customer_upgrade:[],
                        other_toggle_temp:[],
                        order_submit_success:[],
                        order_cancel:[],
                        order_pay_success:[],
                        order_send:[],
                        order_finish:[],
                        order_refund_apply:[],
                        order_refund_success:[],
                        order_refund_reject:[],
                    },
                    yz_notice:{
                        notice_enable:{
                            created:false,
                            paid:false,
                            received:false,
                        },
                        salers:[],
                    },
                    sell:[],
                    activeName: 'one',
                }
            },
            mounted () {
                this.getData();

            },
            methods: {
                delPeople(item){
                    this.yz_notice.salers.forEach((list,index)=>{
                        if(list.uid==item.uid){
                            this.yz_notice.salers.splice(index,1)
                        }
                    })
                },
                surePeople(item) {
                    var status=0;
                    if(this.yz_notice.salers.length>0){
                        this.yz_notice.salers.some((list,index,key)=>{
                            if(list.uid==item.uid){
                                status=1
                                this.$message({message: '该通知人已被选中',type: 'error'});
                                return true
                            }
                        })
                    }
                    if(status==1){
                        return false
                    }
                    console.log(this.yz_notice.salers)
                    this.yz_notice.salers.push(item)
                },
                search(){
                    let that = this;
                    this.$http.post('{!! yzWebFullUrl('member.member.get-search-member-json') !!}',{keyword:this.keyword}).then(response => {
                        if (response.data.result) {
                            this.people_list=response.data.data.members
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }

                    },response => {
                        this.$message({message: response.data.msg,type: 'error'});
                    });
                },
                openPeople(){
                    this.peopleShow=true;
                },
                getSelect(val){
                    this.$forceUpdate()
                },
                changeVal(val){
                    let that=this;
                    if(this.default_temp[val]==0){
                        var url = "{!! yzWebUrl('setting.default-notice.cancel') !!}"
                        var postdata = {
                            notice_name: val,
                            setting_name: "shop.notice"
                        };
                    }else if(this.default_temp[val]==1){
                        var url = "{!! yzWebUrl('setting.default-notice.index') !!}"
                        var postdata = {
                            notice_name: val,
                            setting_name: "shop.notice"
                        };
                    }
                    this.$http.post(url,postdata).then(function (response){
                        if (response.data.result==1) {
                            if(this.default_temp[val]==1){
                                this.temp[val][0].id=Number(response.data.id)
                                this.yz_notice[val]=Number(response.data.id)
                                this.$forceUpdate()
                            }
                            location.reload();
                            this.$message({message: '操作成功',type: 'success'});
                        }else {
                            this.default_temp[val]=0
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.shop.notice') !!}').then(function (response){
                        if (response.data.result) {
                            if(response.data.data.set){
                                for(let i in response.data.data.set){
                                    this.set[i]=response.data.data.set[i]
                                }
                            }
                            for(let i in this.set){
                                this.yz_notice[i]=Number(this.set[i])
                            }
                            if(response.data.data.set.salers){
                                let a=this.set.salers.slice(0)
                                this.yz_notice.salers=a
                            }else{
                                this.yz_notice.salers=[]
                            }
                            if(this.set.notice_enable){
                                this.yz_notice.notice_enable=this.set.notice_enable
                            }
                            this.default_temp=response.data.data.default_temp

                            this.temp.point_change=response.data.data.temp_list.slice(0)
                            this.default_temp.point_change==1?this.temp.point_change.unshift({id:this.yz_notice.point_change,title:'默认消息模板'}):this.temp.point_change.unshift({id:0,title:'默认消息模板'})
                            this.temp.point_deficiency=response.data.data.temp_list.slice(0)
                            this.default_temp.point_deficiency==1?this.temp.point_deficiency.unshift({id:this.yz_notice.point_deficiency,title:'默认消息模板'}):this.temp.point_deficiency.unshift({id:0,title:'默认消息模板'})
                            this.temp.point_deficiency=response.data.data.temp_list.slice(0)
                            this.default_temp.point_deficiency==1?this.temp.point_deficiency.unshift({id:this.yz_notice.point_deficiency,title:'默认消息模板'}):this.temp.point_deficiency.unshift({id:0,title:'默认消息模板'})
                            this.temp.balance_change=response.data.data.temp_list.slice(0)
                            this.default_temp.balance_change==1?this.temp.balance_change.unshift({id:this.yz_notice.balance_change,title:'默认消息模板'}):this.temp.balance_change.unshift({id:0,title:'默认消息模板'})
                            this.temp.balance_deficiency=response.data.data.temp_list.slice(0)
                            this.default_temp.balance_deficiency==1?this.temp.balance_deficiency.unshift({id:this.yz_notice.balance_deficiency,title:'默认消息模板'}):this.temp.balance_deficiency.unshift({id:0,title:'默认消息模板'})
                            this.temp.withdraw_submit=response.data.data.temp_list.slice(0)
                            this.default_temp.withdraw_submit==1?this.temp.withdraw_submit.unshift({id:this.yz_notice.withdraw_submit,title:'默认消息模板'}):this.temp.withdraw_submit.unshift({id:0,title:'默认消息模板'})
                            this.temp.withdraw_success=response.data.data.temp_list.slice(0)
                            this.default_temp.withdraw_success==1?this.temp.withdraw_success.unshift({id:this.yz_notice.withdraw_success,title:'默认消息模板'}):this.temp.withdraw_success.unshift({id:0,title:'默认消息模板'})
                            this.temp.withdraw_fail=response.data.data.temp_list.slice(0)
                            this.default_temp.withdraw_fail==1?this.temp.withdraw_fail.unshift({id:this.yz_notice.withdraw_fail,title:'默认消息模板'}):this.temp.withdraw_fail.unshift({id:0,title:'默认消息模板'})
                            this.temp.withdraw_reject=response.data.data.temp_list.slice(0)
                            this.default_temp.withdraw_reject==1?this.temp.withdraw_reject.unshift({id:this.yz_notice.withdraw_reject,title:'默认消息模板'}):this.temp.withdraw_reject.unshift({id:0,title:'默认消息模板'})
                            this.temp.buy_goods_msg=response.data.data.temp_list.slice(0)
                            this.default_temp.buy_goods_msg==1?this.temp.buy_goods_msg.unshift({id:this.yz_notice.buy_goods_msg,title:'默认消息模板'}):this.temp.buy_goods_msg.unshift({id:0,title:'默认消息模板'})
                            this.temp.seller_order_create=response.data.data.temp_list.slice(0)
                            this.default_temp.seller_order_create==1?this.temp.seller_order_create.unshift({id:this.yz_notice.seller_order_create,title:'默认消息模板'}):this.temp.seller_order_create.unshift({id:0,title:'默认消息模板'})
                            this.temp.seller_order_pay=response.data.data.temp_list.slice(0)
                            this.default_temp.seller_order_pay==1?this.temp.seller_order_pay.unshift({id:this.yz_notice.seller_order_pay,title:'默认消息模板'}):this.temp.seller_order_pay.unshift({id:0,title:'默认消息模板'})
                            this.temp.seller_order_finish=response.data.data.temp_list.slice(0)
                            this.default_temp.seller_order_finish==1?this.temp.seller_order_finish.unshift({id:this.yz_notice.seller_order_finish,title:'默认消息模板'}):this.temp.seller_order_finish.unshift({id:0,title:'默认消息模板'})
                            this.temp.order_refund_apply_to_saler=response.data.data.temp_list.slice(0)
                            this.default_temp.order_refund_apply_to_saler==1?this.temp.order_refund_apply_to_saler.unshift({id:this.yz_notice.order_refund_apply_to_saler,title:'默认消息模板'}):this.temp.order_refund_apply_to_saler.unshift({id:0,title:'默认消息模板'})
                            this.temp.customer_upgrade=response.data.data.temp_list.slice(0)
                            this.default_temp.customer_upgrade==1?this.temp.customer_upgrade.unshift({id:this.yz_notice.customer_upgrade,title:'默认消息模板'}):this.temp.customer_upgrade.unshift({id:0,title:'默认消息模板'})
                            this.temp.other_toggle_temp=response.data.data.temp_list.slice(0)
                            this.default_temp.other_toggle_temp==1?this.temp.other_toggle_temp.unshift({id:this.yz_notice.other_toggle_temp,title:'默认消息模板'}):this.temp.other_toggle_temp.unshift({id:0,title:'默认消息模板'})
                            this.temp.order_submit_success=response.data.data.temp_list.slice(0)
                            this.default_temp.order_submit_success==1?this.temp.order_submit_success.unshift({id:this.yz_notice.order_submit_success,title:'默认消息模板'}):this.temp.order_submit_success.unshift({id:0,title:'默认消息模板'})
                            this.temp.order_cancel=response.data.data.temp_list.slice(0)
                            this.default_temp.order_cancel==1?this.temp.order_cancel.unshift({id:this.yz_notice.order_cancel,title:'默认消息模板'}):this.temp.order_cancel.unshift({id:0,title:'默认消息模板'})
                            this.temp.order_pay_success=response.data.data.temp_list.slice(0)
                            this.default_temp.order_pay_success==1?this.temp.order_pay_success.unshift({id:this.yz_notice.order_pay_success,title:'默认消息模板'}):this.temp.order_pay_success.unshift({id:0,title:'默认消息模板'})
                            this.temp.order_send=response.data.data.temp_list.slice(0)
                            this.default_temp.order_send==1?this.temp.order_send.unshift({id:this.yz_notice.order_send,title:'默认消息模板'}):this.temp.order_send.unshift({id:0,title:'默认消息模板'})
                            this.temp.order_finish=response.data.data.temp_list.slice(0)
                            this.default_temp.order_finish==1?this.temp.order_finish.unshift({id:this.yz_notice.order_finish,title:'默认消息模板'}):this.temp.order_finish.unshift({id:0,title:'默认消息模板'})
                            this.temp.order_refund_apply=response.data.data.temp_list.slice(0)
                            this.default_temp.order_refund_apply==1?this.temp.order_refund_apply.unshift({id:this.yz_notice.order_refund_apply,title:'默认消息模板'}):this.temp.order_refund_apply.unshift({id:0,title:'默认消息模板'})
                            this.temp.order_refund_success=response.data.data.temp_list.slice(0)
                            this.default_temp.order_refund_success==1?this.temp.order_refund_success.unshift({id:this.yz_notice.order_refund_success,title:'默认消息模板'}):this.temp.order_refund_success.unshift({id:0,title:'默认消息模板'})
                            this.temp.order_refund_reject=response.data.data.temp_list.slice(0)
                            this.default_temp.order_refund_reject==1?this.temp.order_refund_reject.unshift({id:this.yz_notice.order_refund_reject,title:'默认消息模板'}):this.temp.order_refund_reject.unshift({id:0,title:'默认消息模板'})
                            this.$forceUpdate()
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
                submit() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('setting.shop.notice') !!}',{'yz_notice':this.yz_notice}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: "提交成功",type: 'success'});
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        location.reload();
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
            },
        });
    </script>
@endsection
