<script>
Vue.component('orderOperation', {
    props: {
        operationType:{
            type:Number|String,
            default:'',
        },
        operationOrder:{
            type:Object|String,
            default:{},
        },
        synchro:{
            type:Number|String,
            default:0,
        },
        expressCompanies:{
            type:Array|Object,
            default:[],
        },
        dialog_show:{
            type:Number,
            default:0,
        },
        pay_skip:{
            type:Number,
            default:0,
        },
    },
    delimiters: ['[[', ']]'],
    data(){
        return{
            url_open : "{!! yzWebUrl('order.waybill.waybill') !!}",
            readonly:false,
            cancel_send_show:false,// 取消发货弹窗
            cancel_send_con:"",//取消发货原因
            cancel_send_id:'',
            confirm_send_show:false,// 确认发货弹窗
            confirm_send_id:"",

            address_info:{},

            //发货提交信息
            send:{
                dispatch_type_id:1,
                express_code:"",
                express_sn:"",
            },
            send_rules:{

            },
            // 多包裹发货
            more_send_show:false,
            order_goods_send_list:[],
            order_goods_send_list2:[],// 临时
            send_order_goods_ids:[],

            readonly:false,

            web_url: "{!! yzWebUrl('') !!}",
        }
    },
    watch:{
        dialog_show(val) {
            if (this.operationType == 1) {
                this.confirmPay(this.operationOrder.id);
            } else if (this.operationType == 2) {
                this.confirmSend(this.operationOrder.id, this.operationOrder);
            } else if (this.operationType == 3) {
                this.confirmReceive(this.operationOrder.id);
            } else if (this.operationType == 'cancel_send') {
                this.cancelSend(this.operationOrder.id);
            } else if (this.operationType == 'separate_send') {
                this.separateSend(this.operationOrder.id, this.operationOrder);
            } else if (this.operationType == 'city_delivery_push') {
                this.cityDeliveryPush(this.operationOrder);
            } else if (this.operationType == 'city_delivery_cancel') {
                this.cityDeliveryCancel(this.operationOrder);
            } else if (this.operationType == 'city_delivery_refresh') {
                this.cityDeliveryRefresh(this.operationOrder);
            } else if (this.operationType == 'public_request') {
                this.public_request(this.operationOrder);
            }
        },
    },
    mounted: function(){


    },
    methods:{
        public_request(order) {
            let operationType = this.operationType;
            let obj = this.operationOrder.backend_button_models.find(item => {
                return item.value == operationType;
            });
            let loading = this.$loading({target: document.querySelector(".content"), background: 'rgba(0, 0, 0, 0)'});
            this.$http.post(this.web_url + obj.api, {order_id: order.id}).then(function (response) {
                    if (response.data.result) {
                        this.$message({type: 'success', message: response.data.msg});
                        location.reload();
                    } else {
                        this.$message({type: 'error', message: response.data.msg});
                    }
                    loading.close();
                    this.$emit('search');
                }, function (response) {
                    this.$message({type: 'error', message: response.data.msg});
                    loading.close();
                }
            );

        },
        cityDeliveryRefresh(order) {
            let obj = this.operationOrder.backend_button_models.find(item => {
                return item.value == 'city_delivery_refresh'
            });
            let loading = this.$loading({target: document.querySelector(".content"), background: 'rgba(0, 0, 0, 0)'});
            this.$http.post(this.web_url + obj.api, {order_id: order.id}).then(function (response) {
                    if (response.data.result) {
                        this.$message({type: 'success', message: '操作成功'});
                        location.reload();
                    } else {
                        this.$message({type: 'error', message: response.data.msg});
                    }
                    loading.close();
                    this.$emit('search');
                }, function (response) {
                    this.$message({type: 'error', message: response.data.msg});
                    loading.close();
                }
            );

        },
        cityDeliveryCancel(order){
            let obj = this.operationOrder.backend_button_models.find(item => {
                return item.value == 'city_delivery_cancel'
            });
            this.$confirm('确定取消第三方订单吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post(this.web_url+obj.api,{order_id:order.id}).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '操作成功'});
                            location.reload();
                        }
                        else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                        this.$emit('search');
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                    }
                );
            }).catch(() => {
                this.$message({type: 'info',message: '已取消操作'});
            });
        },
        //同城配送推送订单到第三方
        cityDeliveryPush(order){
            let obj = this.operationOrder.backend_button_models.find(item => {
                return item.value == 'city_delivery_push'
            });
            this.$confirm('确定推送此订单到第三方吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post(this.web_url+obj.api,{order_id:order.id}).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '操作成功'});
                            location.reload();
                        }
                        else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                        this.$emit('search');
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                    }
                );
            }).catch(() => {
                this.$message({type: 'info',message: '已取消操作'});
            });
        },
        // 确认付款
        confirmPay(id) {
            let obj = this.operationOrder.backend_button_models.find(item => {
                return item.value == 1
            });
            this.$confirm('确认此订单已付款吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                let loading = this.$loading({background: 'rgba(0, 0, 0, 0)'});
                this.$http.post(this.web_url+obj.api,{order_id:id}).then(function (response) {
                    if (response.data.result) {
                        this.$message({type: 'success',message: '操作成功'});
                        if(this.pay_skip == 1) {
                            location.reload();
                        }
                    }
                    else{
                        this.$message({type: 'error',message: response.data.msg});
                    }
                    loading.close();
                    this.$emit('search');
                },function (response) {
                    this.$message({type: 'error',message: response.data.msg});
                    loading.close();
                }
            );
            }).catch(() => {
                this.$message({type: 'info',message: '已取消操作'});
            });
        },

        // 取消发货
        cancelSend(id) {
            this.cancel_send_show = true;
            this.cancel_send_con = "";
            this.cancel_send_id = id;
            // console.log(id)
        },
        // 确认取消发货
        sureCancelSend() {
            let json = {
                // route:'order.operation.manualRefund',
                order_id:this.cancel_send_id,
                cancelreson:this.cancel_send_con,
            };
            let obj = this.operationOrder.backend_button_models.find(item => {
                return item.value == 'cancel_send'
            })
            // console.log(json);
            let loading = this.$loading({target:document.querySelector("#cancel-send"),background: 'rgba(0, 0, 0, 0)'});
            this.$http.post(this.web_url+obj.api,json).then(function (response) {
                if (response.data.result) {
                    this.$message({type: 'success',message: '取消发货成功!'});
                }
                else{
                    this.$message({type: 'error',message: response.data.msg});
                }
                loading.close();
                this.close_order_show = false;
                this.$emit('search');
            },function (response) {
                this.$message({type: 'error',message: response.data.msg});
                loading.close();
                this.close_order_show = false;
            })
        },
        // 确认收货
        confirmReceive(id) {
            let obj = this.operationOrder.backend_button_models.find(item => {
                return item.value == 3
            })
            this.$confirm('确认订单收货吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                let loading = this.$loading({background: 'rgba(0, 0, 0, 0)'});
                this.$http.post(this.web_url+obj.api,{order_id:id}).then(function (response) {
                    if (response.data.result) {
                        this.$message({type: 'success',message: '操作成功'});
                    }
                    else{
                        this.$message({type: 'error',message: response.data.msg});
                    }
                    loading.close();
                    this.$emit('search');

                },function (response) {
                    this.$message({type: 'error',message: response.data.msg});
                    loading.close();
                }
            );
            }).catch(() => {
                this.$message({type: 'info',message: '已取消操作'});
            });
        },
        // 确认发货
        async confirmSend(id,item) {

            let synchro = this.getSessionStore('synchro');

            if (!synchro) {
                synchro =await this.$http.post("{!! yzWebUrl('order.order-list.get-synchro') !!}").then(({ data:{ data } })=>{
                    this.setSessionStore('synchro',data);
                    return data;
                })
            }
            this.readonly = false;
            if (synchro){
                this.$http.post(this.url_open,{id:id}).then(function (response) {
                        if (response.data.result  && response.data.result != 'error') {
                            this.send.express_code = response.data.resp.shipper_code;
                            this.send.express_sn = response.data.resp.logistic_code;
                            this.readonly = true;

                        }
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});

                    }
                );
            }
            this.confirm_send_show = true;
            this.confirm_send_con = "";
            this.send = {
                dispatch_type_id :1,
                express_code:"",
                express_sn:""
            }
            this.confirm_send_id = id;
            this.address_info = item.address || {};
        },
        setSessionStore (name, content) {
            if (!name) return
            // if (typeof content !== 'string') {
            //     content = JSON.stringify(content)
            // }
            window.sessionStorage.setItem(name, content)
        },
        getSessionStore (name) {
            if (!name) return;
            var content = window.sessionStorage.getItem(name);
            // if (typeof content == 'string') {
            //     content = JSON.parse(content)
            // }
            return content;
        },

        // 确认确认发货
        sureconfirmSend() {
            let json = {
                dispatch_type_id:this.send.dispatch_type_id,
                express_code:this.send.express_code,
                express_sn:this.send.express_sn,
                order_id:this.confirm_send_id,
            };

            let obj = this.operationOrder.backend_button_models.find(item => {
                return item.value == 2
            })
            // console.log(obj);
            // console.log(json);
            // if(this.send.express_sn == "") {
            //     this.$message.error("快递单号不能为空！");
            //     return;
            // }
            let loading = this.$loading({target:document.querySelector("#cancel-send"),background: 'rgba(0, 0, 0, 0)'});
            this.$http.post(this.web_url+obj.api,json).then(function (response) {
                if (response.data.result) {
                    this.$message({type: 'success',message: '确认发货成功!'});
                }
                else{
                    this.$message({type: 'error',message: response.data.msg});
                }
                loading.close();
                this.confirm_send_show = false;
                this.$emit('search');
            },function (response) {
                this.$message({type: 'error',message: response.data.msg});
                loading.close();
                this.confirm_send_show = false;
            })
        },
        //多包裹发货
        separateSend(id,item) {
            this.more_send_show = true;
            this.confirm_send_id = id;
            this.send = {
                dispatch_type_id :1,
                express_code:"",
                express_sn:"",
            };

            this.address_info = item.address || {};
            this.getSeparateSendOrderGoods(id);
        },
        // 多包裹确认发货 选择商品
        moreSendChange(selection) {
            let arr = [];
            for (let value of selection){
                arr.push(value.id);
            }
            this.send_order_goods_ids = arr;
        },
        getOneOrderPackage()
        {
            let arr = [];
            for (let value of this.send_order_goods_ids){
                for (let item of this.order_goods_send_list2){
                    if(item.id == value){
                        arr.push({
                            order_goods_id:value,
                            total:item.total
                        });
                    }
                }
            }

            return arr ? arr : [];
        },
        // 获取可选择的商品 多包裹发货
        getSeparateSendOrderGoods(id) {

            this.$http.post('{!! yzWebFullUrl('order.multiple-packages-order-goods.get-order-goods') !!}', {order_id:id}).then(function (response) {
                if (response.data.result) {
                    this.order_goods_send_list = response.data.data;
                    this.order_goods_send_list2 = JSON.parse(JSON.stringify(response.data.data));
                } else{
                    this.$message({type: 'error',message: response.data.msg});
                }
            },function (response) {
                this.$message({type: 'error',message: response.data.msg});
            });
        },
        //多包裹确认发货
        confirmMoreSend() {

            if(this.send_order_goods_ids == undefined || this.send_order_goods_ids.length <= 0) {
                this.$message.error("请选择分批发货订单商品！");
                return;
            }

            if(this.send.express_sn == "") {
                this.$message.error("快递单号不能为空！");
                return;
            }

            let json = {
                dispatch_type_id:this.send.dispatch_type_id,
                express_code:this.send.express_code,
                express_sn:this.send.express_sn,
                order_id:this.confirm_send_id,
                order_goods_ids:this.send_order_goods_ids,
                order_package:this.getOneOrderPackage()
            };
            // console.log(json);
            let obj = this.operationOrder.backend_button_models.find(item => {
                return item.value == 'separate_send'
            })
            // console.log(obj.api)
            let loading = this.$loading({target:document.querySelector("#separate-send"),background: 'rgba(0, 0, 0, 0)'});
            this.$http.post(this.web_url+obj.api,json).then(function (response) {
                if (response.data.result) {
                    this.$message({type: 'success',message: '发货成功!'});
                } else{
                    this.$message({type: 'error',message: response.data.msg});
                }
                loading.close();
                this.more_send_show = false;
                location.reload(); //刷新页面
            },function (response) {
                this.$message({type: 'error',message: response.data.msg});
                loading.close();
                this.more_send_show = false;
            })
        },

    },
    template: `
        <div>
            <!-- 取消发货 -->
            <el-dialog :visible.sync="cancel_send_show" width="750px"  title="取消发货">
                <div style="height:300px;overflow:auto" id="cancel-send">
                    <div style="color:#000;font-weight:500">取消发货原因</div>
                    <el-input v-model="cancel_send_con" :rows="10" type="textarea"></el-input>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="cancel_send_show = false">取 消</el-button>
                    <el-button type="primary" @click="sureCancelSend">取消发货 </el-button>
                </span>
            </el-dialog>
            <!-- 确认发货 -->
            <el-dialog :visible.sync="confirm_send_show" width="750px"  title="确认发货">
                <div style="height:400px;overflow:auto" id="confirm-send">
                    <el-form ref="send" :model="send" :rules="send_rules" label-width="15%">
                        <el-form-item label="收件人信息" prop="aggregation">
                                <div>收 件 人: [[address_info.realname]] / [[address_info.mobile]]</div>
                                <div>收货地址: [[address_info.address]]</div>
                        </el-form-item>
                        <el-form-item label="配送方式" prop="">
                            <el-radio v-model="send.dispatch_type_id" :label="1">快递</el-radio>
                        </el-form-item>
                        <el-form-item label="快递公司">
                            <el-select v-model="send.express_code" clearable filterable placeholder="快递公司" :disabled='readonly' style="width:70%;">
                                <el-option label="其他快递" value=""></el-option>
                                <el-option v-for="(item,index) in expressCompanies" :key="index" :label="item.name" :value="item.value"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="快递单号" prop="">
                            <el-input v-model="send.express_sn" :disabled='readonly' style="width:70%;"></el-input>
                        </el-form-item>
                    </el-form>

                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="confirm_send_show = false">取 消</el-button>
                    <el-button type="primary" @click="sureconfirmSend()">确认发货 </el-button>
                </span>
            </el-dialog>
            <!-- 多包裹确认发货 -->
            <el-dialog :visible.sync="more_send_show" width="750px"  title="分批发货">
                <div style="" id="separate-send">
                    <el-form ref="send" :model="send" label-width="15%">
                        <el-form-item label="收件人信息" prop="aggregation">
                            <div>收 件 人: [[address_info.realname]] / [[address_info.mobile]]</div>
                            <div>收货地址: [[address_info.address]]</div>
                        </el-form-item>
                        <el-form-item label="快递公司">
                            <el-select v-model="send.express_code" clearable filterable placeholder="快递公司" style="width:70%;">
                                <el-option label="其他快递" value=""></el-option>
                                <el-option v-for="(v1,k1) in expressCompanies" :key="k1" :label="v1.name" :value="v1.value"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="快递单号" prop="">
                            <el-input v-model="send.express_sn" style="width:70%;"></el-input>
                        </el-form-item>
                    </el-form>
                    <el-table ref="multipleTable"  :data="order_goods_send_list" tooltip-effect="dark"  height="250" style="width: 100%" @selection-change="moreSendChange">
                        <el-table-column type="selection" width="55"></el-table-column>
                        <el-table-column width="550">
                            <template slot-scope="scope">
                                <div style="display:flex;">
                                    <div style="display:flex;flex:1;">
                                        <div style="width:50px;height:50px">
                                            <img :src="scope.row.thumb" alt="" style="width:50px;height:50px">
                                        </div>
                                        <div style="margin:0 20px;display: flex;flex-direction: column;justify-content: space-between;">
                                            <div style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:5;overflow: hidden;text-overflow: ellipsis;">[[scope.row.title]]</div>
                                            <div style="color:#999">[[scope.row.goods_id]]</div>
                                        </div>
                                    </div>
                                    <div>
                                        <el-input-number size="small" controls-position="right" v-model="order_goods_send_list2[scope.$index].total" :min="1" :max="scope.row.total" label="数量"></el-input-number>
                                        <div style="color:#999">最大数量999 * [[scope.row.total]]</div>
                                    </div>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column>
                            <template slot-scope="scope">
                                <div style="color:#999">[[scope.row.goods_option_title]]</div>
                            </template>
                        </el-table-column>

                    </el-table>

                </div>
                <span slot="footer" class="dialog-footer">
                        <el-button @click="more_send_show = false">取 消</el-button>
                        <el-button type="primary" @click="confirmMoreSend()">确认发货 </el-button>
                    </span>
            </el-dialog>


        </div>
    `,



});

</script>
