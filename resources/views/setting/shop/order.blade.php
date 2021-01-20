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
            padding-bottom:20px;
            border-radius: 8px;
            position:relative;
            min-height:100vh;
            background-color:#fff;
        }
        .con .setting .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
            margin-bottom:10px;
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
        }
        b{
            font-size:14px;
        }
        .add-goods{
            width: 120px;
            height: 120px;
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
                <el-form ref="form" :model="form" label-width="15%">
                    <div class="block">
                        <div class="title"> <span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>基础设置</b></div>
                        <el-form-item label="按商品拆单">
                            <template>
                                <el-switch
                                        v-model="form.order_apart"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size:12px;color:#ccc;">开启有用户购买平台自营商品,每一个商品拆成一个订单,订单原有逻辑不变</div>
                        </el-form-item>
                        <el-form-item label="首单商品">
                            <div style="display:flex;">
                                <div class="good" v-for="(item,index,key) in thumbList" style="width:120px;display:flex;margin-right:20px;flex-direction: column">
                                    <div class="img" style="position:relative;">
                                        <a style="color:#333;"><div style="width: 20px;height: 20px;background-color: #dde2ee;display:flex;align-items:center;justify-content:center; #999999;position:absolute;right:-10px;top:-10px;border-radius:50%;" @click="delGoods(item)">X</div></a>
                                        <img :src="item.thumb_url" style="width:120px;height:120px;">
                                    </div>
                                    <div style="display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 2;overflow: hidden;font-size:12px;">[[item.title]][ID:[[item.id]]]</div>
                                </div>
                                <div class="add-goods" @click="openGoods()" >
                                    <a style="font-size:32px;color: #999999;"><i class="el-icon-plus" ></i></a>
                                    <div style="color: #999999;">选择商品</div>
                                </div>
                            </div>
                        </el-form-item>
                        <el-form-item label="催发货">
                            <template>
                                <el-switch
                                        v-model="form.expediting_delivery"
                                        active-value="1"
                                        inactive-value="0"
                                >
                                </el-switch>
                            </template>
                            <div style="font-size:12px;color:#ccc;">开启后平台自营/供应商待发货的订单有催发货的功能。</div>
                        </el-form-item>
                    </div>

            </div>
            <el-dialog :visible.sync="goodsShow" width="60%" center title="选择商品">
                <div>
                    <div style="text-align: center">
                        <el-input v-model="search_form.keyword" style="width:80%"></el-input>
                        <el-button type="primary" @click="searchGoods()" style="margin-left:20px;">搜索</el-button>
                    </div>
                    <el-table :data="goods_list" style="width: 100%;height:500px;overflow:auto">
                        <el-table-column label="ID" prop="id" align="center" ></el-table-column>
                        <el-table-column label="商品信息"  align="center">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center;justify-content:center;">
                                    <img v-if="scope.row.thumb_url" :src="scope.row.thumb_url" style="width:50px;height:50px"></img>
                                    <div style="margin-left:10px">[[scope.row.title]]</div>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column prop="refund_time" label="操作" align="center" >
                            <template slot-scope="scope">
                                <el-button @click="sureGoods(scope.row)">
                                    选择
                                </el-button>

                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <el-row>
                    <el-col :span="24" align="right" migra style="padding:15px 5% 15px 0" v-loading="loading">
                        <el-pagination background layout="prev, pager, next" @current-change="currentChange" :total="page_total"
                                       :page-size="page_size" :current-page="current_page"></el-pagination>
                    </el-col>
                </el-row>
            </el-dialog>
            <div class="confirm-btn">
                <el-button  type="primary" @click="submit">提交</el-button>
            </div>
            </el-form>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    loading:false,
                    selectGoods:[],
                    activeName: 'first',
                    goods_list:[],
                    goodsShow:false,
                    arr:[],
                    page_total:0,
                    page_size:0,
                    current_page:0,
                    search_form:{
                        keyword:'',
                    },
                    real_search_form:"",
                    old_edit_form_coupon:'',
                    active:0,
                    thumbList:[],
                    titleList:[],
                    form:{
                        order_apart:'0',
                        goods:[],
                        expediting_delivery:'0',
                    },
                }
            },
            mounted () {
                this.getData();

            },
            methods: {
                openGoods() {
                    this.goodsShow = true;
                },
                delGoods(item){
                    this.thumbList.forEach((list,index)=>{
                        if(list.id==item.id){
                            this.thumbList.splice(index,1)
                            this.form.goods.splice(index,1)
                        }
                    })

                },
                currentChange(val) {
                    this.loading = true;
                    this.$http.post('{!! yzWebFullUrl('goods.goods.getSearchGoodsJson') !!}',{page:val,keyword:this.search_form.keyword}).then(function (response){
                            let datas = response.data.data.goods;
                            this.page_total = datas.total;
                            this.goods_list = datas.data;
                            this.page_size = datas.per_page;
                            this.current_page = datas.current_page;
                            this.loading = false;
                        },function (response) {
                            console.log(response);
                            this.loading = false;
                        }
                    );
                },
                searchGoods() {
                    let that = this;
                    this.$http.post('{!! yzWebFullUrl('goods.goods.getSearchGoodsJson') !!}',{keyword:this.search_form.keyword}).then(response => {
                        if (response.data.result) {
                            let data = response.data.data.goods;
                            this.page_total = data.total;
                            this.goods_list = data.data;
                            this.page_size = data.per_page;
                            this.current_page = data.current_page;
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }

                    },response => {
                        this.$message({message: response.data.msg,type: 'error'});
                    });
                },
                sureGoods(item) {
                    var status=0;
                    if(this.form.goods.length>0){
                        this.form.goods.some((list,index,key)=>{
                            if(list==item.id){
                                status=1
                                this.$message({message: '该商品已被选中',type: 'error'});
                                return true
                            }
                        })
                    }
                    if(status==1){
                        return false
                    }

                    this.thumbList.push(item)
                    this.form.goods.push(item.id)
                },
                getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.shop.order') !!}').then(function (response){
                        if (response.data.result) {
                            if(response.data.data.set){
                                for(let i in response.data.data.set){
                                    this.form[i]=response.data.data.set[i]
                                }
                                if(!this.form.goods){
                                    this.form.goods=[]
                                }
                                if(response.data.data.goods){
                                    this.selectGoods=response.data.data.goods
                                    this.selectGoods.forEach((item,index,key)=>{
                                        this.thumbList.push(item)
                                    })
                                }
                            }
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
                submit() {
                    if(this.form.goods.length>0){
                        this.form.goods.sort(function (x,y) {
                            return x-y;
                        });
                    }
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('setting.shop.order') !!}',{'order':this.form}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: response.data.msg,type: 'success'});
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
