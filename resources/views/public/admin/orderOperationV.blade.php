<script>
Vue.component('orderOperationV', {
    props: {
        operationType:{
            type:Number|String,
            default:'',
        },
        operationOrder:{
            type:Object|String,
            default:{},
        },
        dialog_show:{
            type:Number,
            default:0,
        },
    },
    delimiters: ['[[', ']]'],
    data(){
        return{
            cancel_send_show:false,// 取消发货弹窗
            cancel_send_con:"",//取消发货原因
        }
    },
    watch:{
        dialog_show(val) {
            if (this.operationType == 5) {
                this.cancelSend(this.operationOrder.id);
            }
            
        },
    },
    mounted: function(){
        
        
    },
    methods:{
        // 取消发货
        cancelSend(id) {
            this.cancel_send_show = true;
            this.cancel_send_con = "";
            this.cancel_send_id = id;
            console.log(id)
        },
        // 确认取消发货
        sureCancelSend() {
            let json = {
                // route:'order.operation.manualRefund',
                order_id:this.cancel_send_id,
                cancelreson:this.cancel_send_con,
            };
            console.log(json);
            let loading = this.$loading({target:document.querySelector("#cancel-send"),background: 'rgba(0, 0, 0, 0)'});
            this.$http.post('{!! yzWebFullUrl('order.vue-operation.cancel-send') !!}',json).then(function (response) {
                if (response.data.result) {
                    this.$message({type: 'success',message: '关闭订单成功!'});
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
        </div>
    `,
    

    
});
    
</script>
