<style>
  .dialog-box {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2001;
    background-color: rgba(0, 0, 0, .1);
  }
</style>
<template id="printme">
  <div v-show="show" class="dialog-box" @click="clone">
    <div style="border-radius:10px;background-color: #fff;width:900px;max-height: 90vh;overflow-y: auto;" @click.stop="dialogVisible = true" v-loading="loading">
      <div ref="edit">
        <div style="color:#101010;padding: 10px 20px;">
          <div style="margin:10px 0;font-size: 18px;">发票明细</div>
          <div style="margin:10px;display:flex;flex-wrap:wrap;font-size: 14px;">
            <div style="font-weight:700;width:100%;">购货单位</div>
            <div style="padding: 0 10px;width: 100%;display:flex;flex-wrap:wrap;">
              <item l="名称" :c="purchase.name"></item>
              <item l="纳税人识别号" :c="purchase.tax_number"></item>
              <item l="地址/电话" :c="(purchase.register_address || '') + (purchase.register_address&&purchase.register_mobile?' / ':'') + (purchase.register_mobile || '')"></item>
              <item l="开户行/账号" :c="(purchase.bank || '') +' ' + (purchase.bank_admin || '')"></item>
              <item l="手机" :c="purchase.mobile"></item>
              <item l="邮箱" :c="purchase.email"></item>
            </div>

          </div>

          <div style="padding:10px;display:flex;flex-wrap:wrap;">
            <div style="font-weight:600;width:100%;">销货单位</div>
            <div style="padding: 0 10px;width: 100%;display:flex;flex-wrap:wrap;">
              <item l="名称" :c="sale.enterprise_name"></item>
              <item l="纳税人识别号" :c="sale.taxpayer_number"></item>
              <item l="地址/电话" :c="sale.sale_address" t="1" :c1="sale.sale_mobile"></item>
              <item l="开户行/账号" :c="(sale.sale_bank || '') + ' ' + (sale.sale_bank_admin || '')"></item>
              <div style="width:33.3%;margin: 10px 0;">收款人：[[sale.billing_payee]]</div>
              <div style="width:33.3%;margin: 10px 0;">复核人：[[sale.invoice_reviewer]]</div>
              <div style="width:33.4%;margin: 10px 0;">开票人：[[sale.invoice_drawer]]</div>
            </div>
          </div>

          <div style="padding-left:10px;margin:20px 10px;">
            <div style="display:flex;flex-wrap:wrap;">
              <div style="width:25%;">价税合计：[[total_tax_price]]</div>
              <div style="width:25%;">金额：[[total_amount]]</div>
              <div style="flex:1;">税额：[[total_tax_amount]]</div>
            </div>
          </div>

          <div style="text-align:center;margin-bottom:40px;">
            <div style="height:40px;line-height:40px;display:flex;background:#f0f0f0 !important;border-radius:4px;-webkit-print-color-adjust: exact;print-color-adjust: exact;color-adjust: exact;">
              <div style="flex:1.2;">商品</div>
              <div style="flex:1;">规格</div>
              <div style="flex:1;">数量</div>
              <div style="flex:1;">单价(含税)</div>
              <div style="flex:1;">金额(含税)</div>
              <div style="flex:1;">税率</div>
              <div style="flex:1;">税额</div>
            </div>
            <div style="height: 80px;display:flex;border-radius:4px;border-bottom: 1px solid #ECECEC;align-items: center;text-align: center;padding: 0 4px;" v-for="(item,i) in goods" :key="i">
              <div style="flex:1.2;overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 3;-webkit-box-orient: vertical;">
                <span>[[item.title]]</span><br>
                <span v-if="item.is_refund&&item.order_goods&&item.order_goods.belongs_to_refund_goods_log&&item.order_goods.belongs_to_refund_goods_log.belongs_to_order_fund" style="color: #ff1717">
                  [[ item.order_goods.belongs_to_refund_goods_log.belongs_to_order_fund.refund_type_name ]]
                </span>
              </div>
              <div style="flex:1;">[[item.spec]]</div>
              <div style="flex:1;">[[item.total]]</div>
              <div style="flex:1;">[[item.unit_price]]</div>
              <div style="flex:1;">[[item.money]]</div>
              <div style="flex:1;">[[item.tax_rate]]</div>
              <div style="flex:1;">[[item.tax_amount]]</div>
            </div>
          </div>
        </div>
      </div>
      <div style="text-align:center;margin:10px 0;">
        <el-button @click.stop="clone">取消</el-button>
        <el-button type="primary" @click.stop="invoicing">确认开票</el-button>
      </div>
    </div>
  </div>
</template>

<script>
  Vue.component("invoice", {
    template: "#printme",
    model: {
      prop: 'checked',
      event: 'click'
    },
    props: {
      value: {
        type: Boolean,
        default: false
      },
      show:{
        type: Boolean,
        default: false
      },
      url:{
        type: String
      },
      order_id:{
        default:""
      },
      type:{
        type: String,
        default:""
      },
      form:{
        type: Object
      }
    },
    delimiters: ["[[", ']]'],
    data() {
      return {
        dialogVisible: false,
        purchase: {},
        sale: {},
        goods: [],
        total_amount: "",
        total_tax_amount: "",
        total_tax_price: "",
        loading:false,
        invoice_type:""
      }
    },
    watch:{
      show(nVal){
        if(nVal) this.getdata();
      }
    },
    methods: {
      clone(){
        this.$emit("click",false)
      },
      open(){
        this.$emit("click",true)
      },
      getdata() {
        let json = {id: this.order_id};
        if (this.form) json.data = this.form;
        this.loading = true;
        this.$http.post("{!! yzWebFullUrl('plugin.invoice.admin.invoicing_order.preview') !!}", json).then(({data:{data, result,msg} }) => {
          this.loading = false;
          if (result == 1) {
            this.purchase = data.purchase;
            this.sale = data.sale;
            this.goods = data.goods || [];
            this.total_amount = data.total_amount;
            this.total_tax_amount = data.total_tax_amount;
            this.total_tax_price = data.total_tax_price;
            this.invoice_type = data.invoice_type;
          }else this.$message.error(msg)
        })
      },
      printme() {
        global_Html = document.body.innerHTML;
        document.body.innerHTML = this.$refs.edit.innerHTML;
        document.body.style.overflow = "hidden";
        window.print();
        window.location.href = this.url;
      },
      electronicsInvoice(){
        this.clone();
        this.$message.success("提交开票成功");
      },
      getInitJson(){
        let json ={order_id:this.order_id};
        return json;
      },
      invoicing() {
        this.loading = true;
        // let json = this.getInitJson();
        let json = this.form?{data:this.form}:{id: this.order_id};
        this.$http.post("{!! yzWebFullUrl('plugin.invoice.admin.invoicing_order.confirm-invoicing') !!}",json).then(({data:{result,msg,data}})=>{
          this.loading = false;
          if(result==1){
            if(this.invoice_type == 0){
              this.electronicsInvoice();
            }
            else this.printme();
          }else{
            this.$message.error(msg);
            if (msg=="请前往税收商品配置") {
              setTimeout(()=>{
                window.location.href = "{!! yzWebFullUrl('plugin.invoice.admin.invoicing-goods.edit') !!}"
              },500)
            }
          }

        })
      }
    },
    components: {
      item: {
        delimiters: ['[[', ']]'],
        template: `
        <div style="width:50%;display:flex;line-height:1;margin:10px 0;" v-if="t==1">
            <div>[[l]]：</div>
            <div>
              <div>[[c]]</div>
              <div style="margin-top:10px;" v-if="c1">[[c1]]</div>
            </div>
        </div>
        <div style="width:50%;margin:10px 0;" v-else>[[l]]：[[c]]</div>`,
        props: ["l", "c", "c1", "t"]
      }
    },
  })
</script>