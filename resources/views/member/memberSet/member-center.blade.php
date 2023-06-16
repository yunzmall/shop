    @extends('layouts.base')
    @section('title', '会员中心显示设置')
    @section('content')
    <link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style scoped>
        .main-panel{
            margin-top:50px;
        }
        .main-panel #re_content {
            padding: 10px;
        }
        .panel{
            margin-bottom:10px!important;
            border-radius: 10px;
            padding-left: 20px;
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
            padding:10px!important;
        }
        .con{
            padding-bottom:40px;
            position:relative;
            border-radius: 8px;
        }
        .con .setting .block{
            padding:10px;
            background-color:#fff;
            border-radius: 8px;
        }
        .block{
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
        }
        b{
            font-size:14px;
        }
        .el-checkbox__inner{
            border:solid 1px #56be69!important;
        }
        .upload-boxed .el-icon-close {
            position: absolute;
            top: -5px;
            right: -5px;
            color: #fff;
            background: #333;
            border-radius: 50%;
            cursor: pointer;
        }
    </style>
    <style scoped>
        /*上传图片 */
        .addImg-box {
            width: 700px;
            display: flex;
            flex-wrap: wrap;
            /* border: 1px solid red; */
            cursor: pointer;
        }

        /* 点击事件添加 */
        .addLevel {
            margin-left: 65px;
        }

        .el-icon-plus {
            margin-top: 28px;
        }

        .addImg-list {
            width: 250px;
            height: 130px;
            margin-right: 20px;
            margin-bottom: 20px;
            position: relative;
        }

        .select-price {
            margin-top: 20px;
        }

        /* 商品图片添加 */
        .add-price-img {
            width: 250px;
            height: 130px;
            text-align: center;
            line-height: 20px;
            cursor: pointer;
            position: relative;
            border: 1px solid #ccc;
        }

        .anew {
            width: 100%;
            color: whitesmoke;
            text-align: center;
            background: rgba(0, 0, 0, .5) !important;
            padding: 5px 0 5px 0;
            position: absolute;
            bottom: 0;
        }

        .cancel-box {
            width: 16px;
            height: 16px;
            background: black;
            position: absolute;
            top: -9px;
            right: -9px;
            border-radius: 50%;
        }

        .cancel {
            color: white;
            font-size: 17px;
            line-height: 19px;
            text-indent: 0px;
            position: relative;
            left: 2px;
            top: -10px;
        }


        .el-icon-plus:before {
            font-size: 35px;
        }

        .select-img {
            margin-left: 135px;
        }


        .el-form-item__label {
            /*font-weight: 600;*/
        }


        /* 多选框 */
        .checkLis_text {
            font-weight: 600;
            margin-right: 10px;
        }

        /* 文本 */
        .p_text {
            color: black;
            font-size: 12px;
            font-family: "微软雅黑";
            opacity: .9;
        }

        .select_text_box {
            width: 65%;
            color: black;
            text-align: left;
            opacity: .9;
            font-size: 12px;
            line-height: 20px;
            font-family: "微软雅黑";
            margin-left: 68px;
            margin-top: 20px;
        }

        .select_text_box>p:nth-child(1) {
            margin-bottom: 5px;
        }

        /* 提示文字居中 */
        .el-popper[x-placement^=top] {
            text-align: center;
        }

        .el-popover--plain {
            font-size: 10px;
        }

        .el-popper[x-placement^=top] {
            /* display: none; */
        }

        [v-cloak] {
            display: none;
        }

        .rectangle-txt {
            margin-top: 100px;
            color: black;
            font-weight: 600;
            opacity: .6;
            user-select: none;
            margin-left:15px;
        }
        .add-goods-box{display: flex;margin-left: 68px;align-items: center;justify-content: center;width: 120px;height: 120px;border: 1px dashed #ccc;}
        .img-box{width: 120px;height: 120px;position: relative;}
        .delete{position: absolute;right: 0;top: 0;width: 100%;height: 100%;background-color: rgba(0, 0, 0, .7);display: none;justify-content: center;align-items: center;}
        .img-box:hover .delete{display: flex;}
        .goods-info>img{width: 40px;height: 40px;}
        .el-table{height: 70vh;max-height: 700px;overflow-y: auto;}
        .goods-list .el-dialog {display: flex;flex-direction: column;margin: 0 !important;position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);}
        .goods-list .el-dialog .el-dialog__body {flex: 1;overflow: auto;}
    </style>
    <div style="padding: 10px">
        @include('layouts.newTabs')
        <div>
            <div id="app" v-cloak>
                <div class="total-head block" style="margin: 0 0 20px 0;padding: 0">
                    <!-- 基本信息 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">基本信息</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="会员中心显示余额">
                                <template>
                                    <el-switch
                                            v-model="form.show_balance"
                                            active-value="0"
                                            inactive-value="1"
                                    >
                                    </el-switch>
                                </template>
                                <div style="font-size:12px;">提示：会员中心是否显示会员余额字样</div>
                            </el-form-item>
                            <el-form-item label="会员中心显示积分">
                                <template>
                                    <el-switch
                                            v-model="form.show_point"
                                            active-value="0"
                                            inactive-value="1"
                                    >
                                    </el-switch>
                                </template>
                                <div style="font-size:12px;">提示：会员中心是否显示会员积分！</div>
                            </el-form-item>
                            <el-form-item label="会员中心显示会员ID">
                                <template>
                                    <el-switch
                                            v-model="form.show_member_id"
                                            active-value="0"
                                            inactive-value="1"
                                    >
                                    </el-switch>
                                </template>
                                <div style="font-size:12px;">提示：会员中心是否显示会员ID！</div>
                            </el-form-item>
                        </el-form>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <!-- 会员关系 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">会员关系</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="会员关系页面显示推荐人">
                                <el-switch active-value="1" inactive-value="0" v-model="form.is_referrer" active-color="#29BA9C" inactive-color="#ccc"></el-switch>
                            </el-form-item>
                            <el-form-item label="会员关系页面显示推荐人上级">
                                <el-switch active-value="1" inactive-value="0" v-model="form.parent_is_referrer" active-color="#29BA9C" inactive-color="#ccc"></el-switch>
                            </el-form-item>
                            <el-form-item label="推荐人电话微信号">
                                <el-switch active-value="1" inactive-value="0" v-model="form.is_recommend_wechat" active-color="#29BA9C" inactive-color="#ccc"></el-switch>
                            </el-form-item>
                            <el-form-item label="显示关系等级">
                                <el-checkbox-group v-model="form.relation_level">
                                    <el-checkbox label="1">
                                        <span class="checkLis_text">1级</span>
                                        <el-input clearable style="width:300px" v-model="form.name1" placeholder="一级关系等级"></el-input>
                                    </el-checkbox><br>
                                    <el-checkbox  label="2" style="margin-top:10px">
                                        <span class="checkLis_text">2级</span>
                                        <el-input clearable v-model="form.name2" style="width:300px" placeholder="二级关系等级"></el-input>
                                    </el-checkbox>
                                </el-checkbox-group>
                            </el-form-item>
                            <el-form-item label="显示按钮">
                                <el-checkbox-group style="line-height: 0; margin-top:10px" v-model="form.nameInfo">
                                    <el-checkbox :label="'wechat'">微信</el-checkbox>
                                    <el-checkbox :label="'phone'">手机</el-checkbox>
                                    <el-checkbox :label="'realname'">姓名</el-checkbox>
                                </el-checkbox-group>
                                <div class="select_text_box" style="margin: 0 0 10px;line-height: 1;">
                                    勾选后,页面将显示下级微信、手机号,如果个人资料中没有填写微信和绑定手机号,将不显示图标 !
                                </div>
                            </el-form-item>
                            <el-form-item label="统计商品">
                                <el-radio-group v-model="form.is_statistical_goods">
                                    <el-radio label="1">开启</el-radio>
                                    <el-radio label="0">关闭</el-radio>
                                </el-radio-group>
                                <div class="select_text_box" style="margin: 0 0 10px;line-height: 1;">开启后,前端会员中心-客户显示商品的购买数量(统计团队已完成订单的商品数量，包含自购)</div>
                                <div style="display: flex;" v-if="form.is_statistical_goods == 1">
                                    <div class="add-goods-box" @click="showGoodsList">
                                        <div style="text-align: center;line-height: 2;">
                                            <i class="el-icon-plus" style="font-size: 30px;color: #ccc;"></i>
                                            <div>添加商品</div>
                                        </div>
                                    </div>
                                    <div style="margin: 0 10px;display: flex;" v-for="(item,i) in form.statistical_goods" :key="i">
                                        <div>
                                            <div class="img-box">
                                                <img :src="item.thumb_url" style="width: 100%;height: 100%;">
                                                <div class="delete">
                                                    <i class="el-icon-delete" style="color: red;font-size: 20px;" @click="form.statistical_goods.splice(i,1)"></i>
                                                </div>
                                            </div>
                                            <div class="select_text_box" style="margin: 0;width: 120px;overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-line-clamp: 2;-webkit-box-orient: vertical;">
                                                [[item.title]]</div>
                                        </div>
                                        <div style="width: 260px;margin-left: 10px;">
                                            <el-input v-model="item.custom_name" placeholder="自定义名称"></el-input>
                                            <div style="line-height: 1.5;color: #8c8c8c;font-size: 12px;">自定义名称前端显示，默认为商品原来的名称</div>
                                        </div>
                                    </div>
                                </div>
                            </el-form-item>
                        </el-form>
                    </div>
                </div>
                <div class="goods-list">
                    <el-dialog title="提示" :visible.sync="show" width="800px" class="aaa">
                        <div style="display: flex;">
                            <el-input v-model="keyword" placeholder="请输入关键字搜索" style="margin-right: 20px;"></el-input>
                            <el-button type="primary" @click="getGoods">搜 索</el-button>
                        </div>
                        <el-table :data="tableData" style="width: 100%">
                            <el-table-column prop="id" label="ID" width="180"></el-table-column>
                            <el-table-column label="商品">
                                <template slot-scope="scope">
                                    <div class="goods-info">
                                        <img :src="scope.row.thumb_url" alt="">
                                        <div class="tips goods-title">[[scope.row.title]]</div>
                                    </div>
                                </template>
                            </el-table-column>
                            <el-table-column label="操作" width="100">
                                <template slot-scope="scope">
                                    <el-button @click="handleClick(scope.row)" type="text" size="small">[[queryGoods(scope.row)?"已":""]]选择</el-button>
                                </template>
                            </el-table-column>
                        </el-table>
                        <el-pagination @current-change="getGoods" :current-page.sync="page" :page-size="per_page" layout="total, prev, pager, next" :total="total" bachground></el-pagination>
                    </el-dialog>
                </div>
                <div class="confirm-btn">
                    <el-button type="primary" @click="submit">提交</el-button>
                </div>
            </div>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data() {
                let set = {!! json_encode(($set?:[])) !!};
                return {
                    type:'',
                    selNum:'',
                    form:{
                        ...set
                    },
                    tableData:[],
                    show:false,
                    keyword:"",
                    total:0,
                    page:0,
                    per_page:0,
                }
            },
            created() {
            },
            mounted () {
                console.log(this.form,111);
            },
            methods: {
                loading(text) {
                    return this.$loading({
                        lock: true,
                        text: text,
                        spinner: 'el-icon-loading',
                        background: 'rgba(0, 0, 0, 0.7)'
                    });
                },
                submit() {
                    let that = this;
                    let url = '{!! yzWebFullUrl('member.member-set.member-center-store') !!}';
                    let json = {
                        set : this.form
                    };
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(url,json).then(response => {
                        if (response.data.result) {
                            this.$message({type: 'success',message: response.data.msg});

                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        loading.close();
                        // location.reload();
                    },response => {
                        loading.close();
                    });
                },
                showGoodsList() {
                    if (!this.tableData || this.tableData.length <= 0) this.getGoods();
                    this.show = true;
                },
                getGoods() {
                    let loading = this.loading("正在获取数据中...");
                    this.$http.post("{!! yzWebFullUrl('goods.goods.get-search-goods-json') !!}", {keyword: this.keyword, page: this.page}).then(({data: {result,msg,data: {goods}}}) => {
                        loading.close();
                        if (result == 1) {
                            this.tableData = goods.data;
                            this.total = goods.total;
                            this.per_page = goods.per_page;
                        } else this.$message.error(msg);
                    })
                },
                queryGoods(row){
                    let statistical_goods = this.form.statistical_goods;
                    if(!statistical_goods) return false;
                    for(let i = 0;i<statistical_goods.length;i++){
                        if(statistical_goods[i].id == row.id) return true;
                    }
                    return false;
                },
                handleClick(row){
                    if(this.queryGoods(row)) return this.$message.error("已经选择此商品");
                    if(!this.form.statistical_goods) this.form.statistical_goods = [];
                    if ( this.form.statistical_goods.length >=2) return this.$message.error("最多只能选择两件商品");
                    this.form.statistical_goods.push(row);
                },
            }
        })
    </script>
    @endsection