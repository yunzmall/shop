<script>
Vue.component('refundOrderOperationBase', {
    template: `
<div>
        <!--//驳回售后申请-->
        <el-dialog :visible.sync="refund_reject_show" width="750px"  title="驳回售后申请">
            <div style="height:300px;overflow:auto" id="refund-reject">
                <div style="color:#000;font-weight:500">驳回原因</div>
                <el-input v-model="refund_reject_reason" :rows="10" type="textarea"></el-input>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="refund_reject_show = false">取 消</el-button>
                <el-button type="primary" @click="confirmRefundReject()">确 认</el-button>
            </span>
        </el-dialog>
         <!-- //通过申请 -->
        <el-dialog :visible.sync="refund_pass_show" width="650px"  title="通过申请">
            <el-form ref="refund_pass_form" :model="refund_pass_form">
                <el-form-item label="">
                    退货地址：
                    <el-select v-model="refund_pass_form.refund_address" clearable placeholder="请选择退货地址" style="width:150px">
                        <el-option v-for="(vr, kr) in refund_pass_address" :label="vr.address_name" :key="kr" :value="vr.id">
                            <span v-if="vr.is_default">[[vr.address_name]](默认地址)</span>
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="">
                    留言：
                    <el-input v-model="refund_pass_form.message" :rows="8" type="textarea"></el-input>
                </el-form-item>
            </el-form>

            <span slot="footer" class="dialog-footer">
                <el-button @click="refund_pass_show = false">取 消</el-button>
                <el-button type="primary" @click="confirmRefundPass()">确 认</el-button>
            </span>
        </el-dialog>
          <!-- //换货确认发货 -->
        <el-dialog :visible.sync="refund_resend_show" width="650px"  title="确认发货">
            <div style="height:350px;overflow:auto" id="refund-resend">
                <el-form ref="refund_resend" :model="refund_resend" label-width="15%">
                    <el-form-item label="快递公司">
                        <el-select v-model="refund_resend.express_code" clearable filterable placeholder="快递公司" style="width:70%;">
                            <el-option label="其他快递" value="其他快递"></el-option>
                            <el-option v-for="(vr1,kr1) in expressCompanies" :key="kr1" :label="vr1.name" :value="vr1.value"></el-option>
                        </el-select>
                    </el-form-item>
                        <el-form-item label="快递单号" prop="">
                            <el-input v-model="refund_resend.express_sn" style="width:70%;"></el-input>
                        </el-form-item>
                </el-form>
            </div>
            <span slot="footer" class="dialog-footer">
                <el-button @click="refund_resend_show = false">取 消</el-button>
                <el-button type="primary" @click="confirmRefundResend()">确认发货 </el-button>
            </span>
        </el-dialog>

        <!-- //换货分批发货 -->
        <el-dialog :visible.sync="refund_batch_resend_show" width="750px"  title="分批发货">
                <div style="" id="refund-batch-resend">
                    <el-form ref="refund_resend" :model="refund_resend" label-width="15%">
                        <el-form-item label="快递公司">
                            <el-select v-model="refund_resend.express_code" clearable filterable placeholder="快递公司" style="width:70%;">
                                <el-option label="其他快递" value="其他快递"></el-option>
                                <el-option v-for="(vr1,kr1) in expressCompanies" :key="kr1" :label="vr1.name" :value="vr1.value"></el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="快递单号" prop="">
                            <el-input v-model="refund_resend.express_sn" style="width:70%;"></el-input>
                        </el-form-item>
                    </el-form>
                    <el-table ref="multipleTable"  :data="refund_order_goods" tooltip-effect="dark"  height="250" style="width: 100%" @selection-change="batchResendChange">
                        <el-table-column type="selection" width="55"></el-table-column>
                        <el-table-column width="550">
                            <template slot-scope="scope">
                                <div style="display:flex;">
                                    <div style="display:flex;width: 100%;">
                                        <div style="width:50px;height:50px">
                                            <img :src="scope.row.goods_thumb" alt="" style="width:50px;height:50px">
                                        </div>
                                        <div style="margin-left:20px;display: flex;flex-direction: column;justify-content: space-between;">
                                            <div style="white-space: nowrap;overflow: hidden;text-overflow: ellipsis;">[[scope.row.goods_title]]</div>
                                            <div style="color:#999">[[scope.row.goods_id]]</div>
                                        </div>
                                    </div>
                                    <div style="margin-right:20px">
                                        <el-input-number size="small"  v-model="scope.row.num" :min="1" :max="scope.row.send_num" label="数量"></el-input-number>
                                        <div style="color:#999">最大数量 [[scope.row.send_num]]</div>
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
                        <el-button @click="refund_batch_resend_show = false">取 消</el-button>
                        <el-button type="primary" @click="confirmBatchResend()">确认发货 </el-button>
                </span>
            </el-dialog>


</div>
    `,
    props: {
        refundOperationType: {
            type: Number | String,
            default: '',
        },
        operationRefund: {
            type: Object | String,
            default: {},
        },
        refundOrder: {
            type: Object | String,
            default: {},
        },
        refund_dialog_show: {
            type: Number,
            default: 0,
        },
    },
    delimiters: ['[[', ']]'],
    data() {
        return {
            refund_order_goods:[],
            //退款申请拒绝
            refund_reject_show: false,
            refund_reject_reason:'',

            //通过退款申请
            refund_pass_show: false,
            refund_pass_address: [],
            refund_pass_form:{},

            //换货确认发货
            expressCompanies:[],
            refund_resend_show:false, //全部发货
            refund_batch_resend_show:false,//分批发货
            refund_resend:{
                express_code:"",
                express_sn:"",
                pack_goods:[],
            },


            web_url: "{!! yzWebUrl('') !!}", //地址
        }
    },
    watch: {
        refund_dialog_show(val) {
            console.log(this.refundOperationType);
            if (this.refundOperationType == -1) {
                return this.refundReject();
            } else if (this.refundOperationType == 1) {
                return this.refundPay();
            } else if (this.refundOperationType == 2) {
                return this.refundConsensus()
            } else if (this.refundOperationType == 3) {
                return this.refundPass()
            } else if (this.refundOperationType == 5) {
                return this.refundResend()
            } else if (this.refundOperationType == 'batch_resend') {
                return this.batchResend()
            }  else if (this.refundOperationType == 10) {
                return this.refundClose()
            }
        },
    },
    mounted: function () {
    },
    methods: {
        //同意退款
        refundPay() {

            let obj = this.operationRefund.backend_button_models.find(item => {
                return item.value == 1
            });

            this.$confirm('是否同意此订单退款？', '提示', {confirmButtonText: '同意',cancelButtonText: '取消',type: 'warning'}).then(() => {
                let url = this.web_url+obj.api;
                url += '&refund_id=' + this.operationRefund.id;
                window.location.href = url;
            }).catch(() => {
                this.$message({type: 'info',message: '已取消操作'});
            });
        },
        //手动退款
        refundConsensus() {

            let obj = this.operationRefund.backend_button_models.find(item => {
                return item.value == 2
            });

            this.$confirm('确认此订单手动退款完成吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post(this.web_url+obj.api,{refund_id:this.operationRefund.id}).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '操作成功'});
                            location.reload(); //重新获取数据刷新页面
                        } else {
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                    }
                );
            }).catch(() => {
                this.$message({type: 'info',message: '已取消操作'});
            });
        },
        //驳回退款申请
        refundReject() {
            this.refund_reject_show = true;
            this.refund_reject_reason = "";
        },
        //驳回退款申请
        confirmRefundReject() {

            let json = {
                'refund_id':this.operationRefund.id,
                'reject_reason':this.refund_reject_reason,
            };

            let obj = this.operationRefund.backend_button_models.find(item => {
                return item.value == -1
            });

            let loading = this.$loading({target:document.querySelector("#refund-reject"),background: 'rgba(0, 0, 0, 0)'});
            this.$http.post(this.web_url+obj.api,json).then(function (response) {
                if (response.data.result) {
                    this.$message({type: 'success',message: '操作成功!'});
                    location.reload(); //重新获取数据刷新页面

                } else{
                    this.$message({type: 'error',message: response.data.msg});
                }
                loading.close();
                this.refund_reject_show = false;
            },function (response) {
                this.$message({type: 'error',message: response.data.msg});
                loading.close();
                this.refund_reject_show = false;
            });
        },

        //通过申请
        refundPass() {
            this.refund_pass_show = true;
            this.getRefundAddress();
        },
        //通过申请
        confirmRefundPass() {

            if(!this.refund_pass_form.refund_address) {
                this.$message.error("请选择退货地址！");
                return false;
            }
            let json = {
                'refund_id':this.operationRefund.id,
                'message':this.refund_pass_form.message,
                'refund_address': this.refund_pass_form.refund_address,
            };


            let obj = this.operationRefund.backend_button_models.find(item => {
                return item.value == 3
            });


            let loading = this.$loading({target:document.querySelector("#refund-pass"),background: 'rgba(0, 0, 0, 0)'});
            this.$http.post(this.web_url+obj.api,json).then(function (response) {
                if (response.data.result) {
                    this.$message({type: 'success',message: '操作成功!'});
                    location.reload(); //重新获取数据刷新页面
                } else{
                    this.$message({type: 'error',message: response.data.msg});
                }
                loading.close();
                this.refund_pass_show = false;
            },function (response) {
                this.$message({type: 'error',message: response.data.msg});
                loading.close();
                this.refund_pass_show = false;
            });
        },
        //获取退款地址
        getRefundAddress() {

            let json = {};

            this.$http.post('{!! yzWebFullUrl('goods.return-address.ajax-all-address') !!}', json).then(function (response) {
                if (response.data.result) {
                    this.refund_pass_address = response.data.data;
                    console.log(this.refund_pass_address);
                } else{
                    this.$message({type: 'error',message: response.data.msg});
                }
            },function (response) {
                this.$message({type: 'error',message: response.data.msg});
            });
        },
        //换货分批发货
        batchResend() {
            if (this.expressCompanies.length <= 0) {
                this.getExpressCompanies();
            }
            if (this.refund_order_goods.length <= 0) {
                this.setResendOrderGoods();
            }

            this.refund_batch_resend_show = true;
        },
        setResendOrderGoods() {

            let goodsArr = [];
            this.operationRefund.refund_order_goods.forEach((item,index) => {
                if (item.send_num > 0) {
                    item.num = item.send_num;
                    goodsArr.push(item);
                }
            });
            this.refund_order_goods =  JSON.parse(JSON.stringify(goodsArr));
            console.log(this.refund_order_goods);
        },

        // 多包裹确认发货 选择商品
        batchResendChange(selection) {
            let arr = [];
            for (let value of selection){
                arr.push(value.order_goods_id);
            }
            this.refund_resend.pack_goods = arr;
        },

        //换货分批发货
        confirmBatchResend() {

            if (this.refund_resend.pack_goods == undefined || this.refund_resend.pack_goods.length <= 0) {
                this.$message({type: 'error',message: '未勾选商品!'});
                return false;
            }
            if(this.refund_resend.express_sn == "") {
                this.$message({type: 'error',message: '快递单号不能为空!'});
                return false;
            }


            let pack_goods = [];
            for (let value of this.refund_resend.pack_goods) {
                for (let item of this.refund_order_goods) {
                    if (item.order_goods_id == value) {
                        pack_goods.push({
                            id: item.order_goods_id,
                            num: item.num,
                        });
                    }
                }
            }


            let json = {
                refund_id:this.operationRefund.id,
                express_code:this.refund_resend.express_code,
                express_sn:this.refund_resend.express_sn,
                pack_goods:pack_goods,
            };


            let obj = this.operationRefund.backend_button_models.find(item => {
                return item.value == 'batch_resend'
            });

            let loading = this.$loading({target:document.querySelector("#refund-pass"),background: 'rgba(0, 0, 0, 0)'});
            this.$http.post(this.web_url+obj.api,json).then(function (response) {
                if (response.data.result) {
                    this.$message({type: 'success',message: '操作成功!'});
                    location.reload(); //重新获取数据刷新页面
                } else{
                    this.$message({type: 'error',message: response.data.msg});
                }
                loading.close();
                this.refund_resend_show = false;

            },function (response) {
                this.$message({type: 'error',message: response.data.msg});
                loading.close();
                this.refund_resend_show = false;
            });
        },

        //获取快递信息
        getExpressCompanies() {
            this.$http.post('{!! yzWebFullUrl('address.get-ajax-express') !!}').then(function (response) {
                if (response.data.result) {
                    this.expressCompanies = response.data.data;

                } else{
                    this.$message({type: 'error',message: response.data.msg});
                }
            },function (response) {
                this.$message({type: 'error',message: response.data.msg});
            });
        },

        //换货确认发货
        refundResend() {
            if (this.expressCompanies.length <= 0) {
                this.getExpressCompanies();
            }
            this.refund_resend_show = true;
        },


        //换货确认发货
        confirmRefundResend() {
            let json = {
                refund_id:this.operationRefund.id,
                express_code:this.refund_resend.express_code,
                express_sn:this.refund_resend.express_sn,
            };

            let obj = this.operationRefund.backend_button_models.find(item => {
                return item.value == 5
            });

            let loading = this.$loading({target:document.querySelector("#refund-pass"),background: 'rgba(0, 0, 0, 0)'});
            this.$http.post(this.web_url+obj.api,json).then(function (response) {
                if (response.data.result) {
                    this.$message({type: 'success',message: '操作成功!'});
                    location.reload(); //重新获取数据刷新页面
                } else{
                    this.$message({type: 'error',message: response.data.msg});
                }
                loading.close();
                this.refund_resend_show = false;

            },function (response) {
                this.$message({type: 'error',message: response.data.msg});
                loading.close();
                this.refund_resend_show = false;
            });
        },

        //换货完成关闭申请
        refundClose() {

            let obj = this.operationRefund.backend_button_models.find(item => {
                return item.value == 10
            });

            this.$confirm('确认此订单换货完成吗？', '提示', {confirmButtonText: '确定',cancelButtonText: '取消',type: 'warning'}).then(() => {
                let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                this.$http.post(this.web_url+obj.api,{refund_id:this.operationRefund.id}).then(function (response) {
                        if (response.data.result) {
                            this.$message({type: 'success',message: '操作成功'});
                            location.reload(); //重新获取数据刷新页面
                        } else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                        loading.close();
                    },function (response) {
                        this.$message({type: 'error',message: response.data.msg});
                        loading.close();
                    }
                );
            }).catch(() => {
                this.$message({type: 'info',message: '已取消操作'});
            });
        },

    },

});

</script>
