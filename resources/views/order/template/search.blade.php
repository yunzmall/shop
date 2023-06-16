
<template id="shop_order_search">
    <div>
        <el-form :inline="true" :model="component_form" class="demo-form-inline">
            {{--<el-form-item label="" >--}}
               {{--<template>--}}
                {{--<div style="display: flex;">--}}
                {{--<el-select v-model="user_info"   style="width:200px" >--}}
                    {{--<el-option v-for="(v,k) in user_info_list" :label="v.label" :value="v.value" ></el-option>--}}
                {{--</el-select>--}}
                {{--<el-input v-model="component_form[user_info]" placeholder="请输入" style="width: 200px;margin-left:15px" clearable></el-input>--}}
                {{--</div>--}}
               {{--</template>--}}
            {{--</el-form-item>--}}
            @if (request()->input('route') == 'order.order-list.index')
                <el-form-item label="">
                    <el-select v-model="component_form.plugin_id" clearable placeholder="订单类型" style="width:150px" >
                        @foreach((new \app\backend\modules\order\services\OrderViewService)->getOrderType() as $order_type)
                            @if($order_type['name'] && $order_type['plugin_id'])
                                <el-option label="{{$order_type['name']}}" value="{{$order_type['plugin_id']}}"></el-option>
                            @endif
                        @endforeach
                    </el-select>
                </el-form-item>
            @endif

            <el-form-item label="">
                <el-input placeholder="请输入" v-model="component_form[user_info]">
                    <el-select style="width:180px;" v-model="user_info"  slot="prepend">
                        <el-option v-for="(v,k) in user_info_list" :label="v.label" :value="v.value" ></el-option>
                    </el-select>
                </el-input>
            </el-form-item>

            <el-form-item label="" v-if="extra_param.package_deliver">
                <el-input placeholder="请输入" v-model="component_form[package_deliver]">
                    <el-select style="width:120px;" v-model="package_deliver"  slot="prepend">
                        <el-option label="自提点名称" value="package_deliver_name"></el-option>
                        <el-option label="自提点ID" value="package_deliver_id"></el-option>
                    </el-select>
                </el-input>
            </el-form-item>
            <el-form-item label="">
                <el-input placeholder="请输入" v-model="component_form[express_infos]">
                    <el-select style="width:120px;" v-model="express_infos"  slot="prepend">
                        <el-option v-for="(v,k) in express_info_list" :label="v.label" :value="v.value" ></el-option>
                    </el-select>
                </el-input>
            </el-form-item>
            {{--<el-form-item label="" v-if="extra_param.package_deliver">--}}
               {{--<template>--}}
                {{--<div style="display: flex;">--}}
                {{--<el-select v-model="package_deliver"   style="width:200px" >--}}
                    {{--<el-option  label="自提点名称" value="package_deliver_name" ></el-option>--}}
                    {{--<el-option  label="自提点ID" value="package_deliver_id" ></el-option>--}}
                {{--</el-select>--}}
                {{--<el-input v-model="component_form[package_deliver]" placeholder="请输入" style="width: 200px;margin-left:15px" clearable></el-input>--}}
                {{--</div>--}}
               {{--</template>--}}
            {{--</el-form-item>--}}
            {{--<el-form-item label="" >--}}
               {{--<template>--}}
                {{--<div style="display: flex;">--}}
                {{--<el-select v-model="express_infos"   style="width:200px" >--}}
                    {{--<el-option v-for="(v,k) in express_info_list" :label="v.label" :value="v.value" ></el-option>--}}
                {{--</el-select>--}}
                {{--<el-input v-model="component_form[express_infos]" placeholder="请输入" style="width: 200px;margin-left:15px" clearable></el-input>--}}
                {{--</div>--}}
               {{--</template>--}}
            {{--</el-form-item>     --}}
            <el-form-item label="">
                <el-select v-model="component_form.order_status" multiple clearable  collapse-tags placeholder="订单状态" style="width:150px">
                    <el-option label="待支付" value="waitPay"></el-option>
                    <el-option label="待发货" value="1"></el-option>
                    <el-option label="待收货" value="2"></el-option>
                    <el-option label="已完成" value="3"></el-option>
                    <el-option label="已关闭" value="-1"></el-option>
                </el-select>
            </el-form-item>      
            <el-form-item label="">
                <el-select v-model="component_form.first_order" clearable placeholder="是否搜索首单" style="width:150px">
                    <el-option label="搜索首单" value="1"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="">
                <el-select v-model="component_form.pay_type" filterable clearable placeholder="支付方式" style="width:150px">
                    @foreach(\app\backend\modules\order\services\OrderViewService::searchablePayType() as $pay_type)
                        <el-option label="{{$pay_type['name']}}" value="{{$pay_type['value']}}"></el-option>
                    @endforeach
                </el-select>
            </el-form-item>
            <el-form-item label="">
                <el-select v-model="component_form.pay_type_group" clearable placeholder="支付方式组" style="width:150px">
                    @foreach(\app\backend\modules\order\services\OrderViewService::payTypeGroup() as $pay_type_group)
                        <el-option label="{{$pay_type_group['name']}}" value="{{$pay_type_group['id']}}"></el-option>
                    @endforeach
                </el-select>
            </el-form-item>     
            <el-form-item label="">
                <el-select v-model="component_form.dispatch_type_id" clearable placeholder="配送方式" style="width:150px">
                    <el-option v-for="(v,k) in this.dispatch_type_list" :label="v.name" :value="v.id" :key="v.id"></el-option>
                </el-select>
            </el-form-item>   
            @if(app('plugins')->isEnabled('package-delivery') || app('plugins')->isEnabled('shop-clerk'))  
            <el-form-item label="" >
               <template>
                <div style="display: flex;">
                <el-select v-model="employee_information"   style="width:200px" placeholder="平台核销员/pos收银员信息">
                @if(app('plugins')->isEnabled('package-delivery'))
                <el-option  label="平台核销员昵称/姓名/手机号" value="package_delivery_clerk_kwd" ></el-option>
                <el-option  label="平台核销员会员ID" value="package_delivery_clerk_uid" ></el-option>
                @endif
                @if(app('plugins')->isEnabled('shop-clerk'))
                <el-option  label="pos收银员昵称/姓名/手机号" value="shop_clerk_kwd" ></el-option>
                <el-option  label="pos收银员会员ID" value="shop_clerk_uid" ></el-option>
                @endif                                     
                </el-select>
                <el-input v-model="component_form[employee_information]" placeholder="请输入" style="width: 200px;margin-left:15px" clearable></el-input>
                </div>
               </template>
            </el-form-item>  
            @endif

            <el-form-item label="">
                <el-input placeholder="请输入" v-model="component_form[commodity_info]">
                    <el-select style="width:120px;" v-model="commodity_info"  slot="prepend">
                        <el-option  label="商品ID" value="goods_id" ></el-option>
                        <el-option  label="商品名称" value="goods_title" ></el-option>
                        <el-option  label="商品条码" value="product_sn" ></el-option>
                        <el-option  label="商品编号" value="goods_sn" ></el-option>
                        @if(app('plugins')->isEnabled('marketing-qr'))
                            <el-option  label="营销码标签" value="marketing_qr_label" ></el-option>
                        @endif
                    </el-select>
                </el-input>
            </el-form-item>

            {{--<el-form-item label="" >--}}
               {{--<template>--}}
                {{--<div style="display: flex;">--}}
                {{--<el-select v-model="commodity_info"   style="width:200px" >--}}
                   {{--<el-option  label="商品ID" value="goods_id" ></el-option>--}}
                   {{--<el-option  label="商品名称" value="goods_title" ></el-option>--}}
                   {{--<el-option  label="商品条码" value="product_sn" ></el-option>--}}
                   {{--<el-option  label="商品编号" value="goods_sn" ></el-option>--}}
                   {{--@if(app('plugins')->isEnabled('marketing-qr'))--}}
                   {{--<el-option  label="营销码标签" value="marketing_qr_label" ></el-option>--}}
                   {{--@endif--}}
                {{--</el-select>--}}
                {{--<el-input v-model="component_form[commodity_info]" placeholder="请输入" style="width: 200px;margin-left:15px" clearable></el-input>--}}
                {{--</div>--}}
               {{--</template>--}}
            {{--</el-form-item>--}}
            <el-form-item label="">
                <el-select v-model="component_form.sort" clearable placeholder="排序" style="width:150px">
                    <el-option label="会员排序" value="1"></el-option>
                </el-select>
            </el-form-item>     
            @if(app('plugins')->isEnabled('electronics-bill'))
                <el-form-item label="">
                    <el-select v-model="component_form.bill_print" clearable placeholder="电子面单打印状态">
                        <el-option label="已打印" value="print"></el-option>
                        <el-option label="未打印" value="not_print"></el-option>
                    </el-select>
                </el-form-item>
            @endif    
            @if(app('plugins')->isEnabled('invoice') && \Setting::get('plugin.invoice.is_open')==1)
                <el-form-item label="">
                    <el-select v-model="component_form.is_invoice" clearable placeholder="是否需要开票">
                        <el-option label="是" value="1"></el-option>
                        <el-option label="否" value="0"></el-option>
                    </el-select>
                </el-form-item>
            @endif                                                                                                                              
            {{--供应商订单列表才显示--}}
            <el-form-item v-if="viewReturn.all_supplier">
                <el-select v-model="component_form.supplier" clearable placeholder="选择供应商账号" style="width:150px">
                    <el-option v-for="(supplier,supplierkey) in viewReturn.all_supplier" :key="supplierkey" :value="supplier.id">
                        [[supplier.username ]]【[[supplier.id]]】
                    </el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="">
                <el-select v-model="component_form.time_field" clearable placeholder="不搜索时间" style="width:150px">
                    <el-option label="下单时间" value="create_time"></el-option>
                    <el-option label="支付时间" value="pay_time"></el-option>
                    <el-option label="发货时间" value="send_time"></el-option>
                    <el-option label="完成时间" value="finish_time"></el-option>
                    <el-option label="关闭时间" value="cancel_time"></el-option>
                    <el-option label="退款申请时间" value="refund_create_time"></el-option>
                    <el-option label="退款完成时间" value="refund_finish_time"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="">
                <el-date-picker
                        v-model="times"
                        type="datetimerange"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        range-separator="至"
                        start-placeholder="开始日期"
                        end-placeholder="结束日期"
                        style="margin-left:5px;"
                        align="right">
                </el-date-picker>
            </el-form-item>
            <el-form-item label="">
                <el-select v-model="component_form.source_id" clearable placeholder="请选择商品来源" v-if="is_source_open==1">
                    <el-option v-for="(v,k) in source_list" :label="v.source_name" :value="v.id" :key="v.id"></el-option>
                </el-select>
            </el-form-item>
            <el-form-item label="">
                <el-button type="primary" @click="childSearch()">搜索</el-button>
                <el-button  @click="agentExport(1,1)">导出(旧)</el-button>
                <el-button  @click="agentExport(1,2)">导出(新)</el-button>
                <el-button v-if="extra_param.team_dividend"  @click="agentExport(2)">导出直推（经销商）</el-button>
            </el-form-item>
        </el-form>
    </div>
</template>


<script>
    Vue.component('shopOrderSearch', {
        style:``,
        name:"shopOrderSearch",
        template: `#shop_order_search`,
        props: {
            viewReturn:{
                type:Object|String,
                default:{},
            },
            searchForm:{
                type:Object|String,
                default:{},
            },
            otherData:{
                type:Object|String,
                default:{},
            },
        },
        delimiters: ['[[', ']]'],
        data() {
            return {
                component_form:{},

                extra_param:{},

                dispatch_type_list:[],//配送方式

                times:[], //时间搜索
                is_source_open: 0,
                source_list: [],
                user_info_list:[
                    {
                        value:'member_info',
                        label:'购买昵称/姓名/手机号'
                    },
                    {
                        value:'member_id',
                        label:'购买会员ID'
                    },     
                    {
                        value:'address_name',
                        label:'收货人姓名'
                    },
                    {
                        value:'address_mobile',
                        label:'收货人手机号'
                    },  
                    {
                        value:'address',
                        label:'收货地址'
                    },
                    {
                        value:'parent_id',
                        label:'上级ID'
                    },                                                                                             
                ],
                express_info_list:[
                    {
                        value:'order_sn',
                        label:'订单编号'
                    },
                    {
                        value:'pay_sn',
                        label:'支付单号'
                    },
                    {
                        value:'express',
                        label:'快递单号'
                    },    
                    {
                        value:'note',
                        label:'订单备注'
                    }                                                                              
                ],
                user_info:'member_info',
                package_deliver:'package_deliver_name',
                express_infos:'order_sn',
                employee_information:'',
                commodity_info:'goods_id'
            }
        },
        watch: {
            times:{
                handler(val) {
                    this.updateSearchTime();
                },
            },
            user_info:{
                handler(val,oldVal){
                    delete  this.component_form[oldVal] 
                }
            },
            package_deliver:{
                handler(val,oldVal){
                    delete  this.component_form[oldVal] 
                }
            },
            express_infos:{
                handler(val,oldVal){
                 delete  this.component_form[oldVal] 
                }                
            },
            employee_information:{
                handler(val,oldVal){
                 delete  this.component_form[oldVal] 
                }                  
            },
            commodity_info:{
                handler(val,oldVal){
                 delete  this.component_form[oldVal] 
                }                  
            }
        },
        created() {},
        mounted: function () {
            this.__childInitial();

        },
        methods: {
            //初始化页面数据，请求链接
            __childInitial() {
                if (this.viewReturn.extraParam) {
                    this.extra_param = this.viewReturn.extraParam;
                }
                this.is_source_open = this.viewReturn.is_source_open;
                this.source_list = this.viewReturn.source_list;
                //为了能在子组件里监听搜索参数值的变动，把参数赋值给当前组件定义的参数
                this.component_form = this.searchForm;


                this.$set(this.component_form,'order_sn','{!! $_REQUEST['order_sn']?:'' !!}');
                if (this.getParam('o_status')) {
                    this.$set(this.component_form, 'order_status', [this.getParam('o_status')]);
                }

                this.$set(this.component_form,'member_id',this.getParam('member_id'));

                this.$set(this.component_form,'time_field',this.getParam('o_time'));

                this.times = [
                    this.formatTime(new Date(new Date(new Date().toLocaleDateString()).getTime())),
                    this.formatTime(new Date(new Date(new Date().toLocaleDateString()).getTime() + (24 * 60 * 60 * 1000 - 1)))
                ];

                this.$set(this.component_form,'start_time',this.times[0]);
                this.$set(this.component_form,'end_time',this.times[1]);


                this.dispatchTypeList();
            },

            //更新搜索时间到搜索参数里
            updateSearchTime () {

                if(this.times && this.times.length>0) {
                    this.$set(this.component_form,'start_time',this.times[0]);
                    this.$set(this.component_form,'end_time',this.times[1]);
                }
            },

            //子组件搜索传参到父级
            syncSearchForm() {

                let mainData = this.component_form;
                let other = {};
                this.$emit('sync-form', mainData, other);
            },

            //订单搜索搜索
            childSearch() {
                this.$emit('search');
            },

            //订单导出导出
            agentExport(export_type,template,exportUrl = '') {

                this.$emit('export', export_type,template,exportUrl);
            },

            //配送方式
            dispatchTypeList(){
                this.$http.post('{!! yzWebFullUrl('dispatch.dispatch-type.get-data') !!}', {}).then(response => {
                    if (response.data.result) {
                        this.dispatch_type_list = response.data.data;
                    } else {
                        this.$message({message: response.data.msg, type: 'error'});
                    }
                }, response => {
                    this.$message({message: response.data.msg, type: 'error'});
                })
            },


            //时间转化
            formatTime(date) {
                let y = date.getFullYear()
                let m = date.getMonth() + 1
                m = m < 10 ? '0' + m : m
                let d = date.getDate()
                d = d < 10 ? '0' + d : d
                let h = date.getHours()
                h = h < 10 ? '0' + h : h
                let minute = date.getMinutes()
                minute = minute < 10 ? '0' + minute : minute
                let second = date.getSeconds()
                second = second < 10 ? '0' + second : second
                return y + '-' + m + '-' + d + ' ' + h + ':' + minute + ':' + second
            },

            // 字符转义
            escapeHTML(a) {
                a = "" + a;
                return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");;
            },
            getParam(name) {
                let reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
                let r = window.location.search.substr(1).match(reg);
                if (r != null) return unescape(r[2]);
                return null;
            },
        },
    });

</script>
