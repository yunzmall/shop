    @extends('layouts.base')
    @section('title', '关系链设置')
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
    <style>
        .el-checkbox__label {
            line-height: 40px;
        }

        /* 提示文本 */
        .text-des {
            margin-left: 0px;
        }

        .text {
            color: #999;
            font-variant: small-caps;
            font-weight: 400;
            font-size: 12px;
            font-family: arial, verdana;
        }

        .text-des-jur,
        .text-des {
            color: #999;
            line-height: 20px;
        }

        /* 选择框修改边框颜色 */
        .el-radio__inner,
        .el-checkbox__inner {
            border: 1px solid #999;
        }

        /* 提示头文本 */
        .el-form-item__content {
            /* border: 1px solid red; */
            line-height: 40px !important;
        }

        .el-form-item__label,
        .el-radio {
            /*font-weight: 600;*/
        }

        /* 上传商品图片列表 */
        .el-upload-list--picture-card .el-upload-list__item {
            width: 110px;
            height: 110px;
            margin-right: 20px;
        }

        .el-upload--picture-card:hover .el-icon-plus {
            color: #29BA9C;
        }

        .el-upload--picture-card {
            width: 110px;
            height: 110px;
            line-height: 100px;
            position: relative;
            overflow: hidden;
        }

        .upload-text {
            display: block;
            line-height: 0;
            margin-top: 28px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .des-text {
            margin-top: 10px;
        }

        .vue-upload {
            width: 556.5px;
            margin-left: 162px;
        }

        .el-form-item__content {
            line-height: 20px;
            font-size: 13px;

        }

        /* 上传商品部分*/
        .addLevel {
            margin-left: 65px;
        }

        /*上传图片 */
        .addImg-box {
            width: 700px;
            display: flex;
            flex-wrap: wrap;
            cursor: pointer;
        }

        .addImg-list {
            height: 150px;
            width: 150px;
            margin-right: 20px;
            margin-bottom: 60px;
            position: relative;
        }

        .cancel-box {
            width: 16px;
            height: 16px;
            user-select: none;
            background: black;
            position: absolute;
            top: -9px;
            right: -9px;
            border-radius: 50%;
        }

        .cancel {
            color: white;
            font-size: 18px;
            line-height: 19px;
            text-indent: 0px;
            position: relative;
            left: 2px;
            top: -10px;
        }

        .anew {
            width: 100%;
            color: whitesmoke;
            text-align: center;
            font-size: 13px !important;
            font-weight: 400;
            padding: 5px 0 5px 0;
            position: absolute;
            bottom: 0;
            background: rgba(0, 0, 0, .5) !important;
        }

        /* 商品图片添加 */
        .add-price-img {
            height: 150px;
            width: 150px;
            text-align: center;
            line-height: 20px;
            cursor: pointer;
            position: relative;
            border: 1px solid #ccc;
        }

        .el-icon-plus {
            font-size: 40px;
            margin-top: 45px;
        }

        .select-price {
            font-size: 13px;
            margin-top: 15px;
        }

        .cell {
            text-align: center;
        }

        .el-checkbox-group {
            font-size: 1px;
        }

        /* 优化显示效果 */
        [v-cloak] {
            display: none;
        }

        /* 文字溢出隐藏 */
        .middenTitle {
            width: 100%;
            font-size: 12px;
            white-space: normal;
            text-overflow: -o-ellipsis-lastline;
            overflow: hidden;
            text-overflow:
                    ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            line-height: 20px;
            text-indent: -6px;
            margin-top: 6px;
        }
    </style>
    <div style="padding: 10px">
        @include('layouts.newTabs')
        <div>
            <div id="app" v-cloak>
                <div class="total-head block" style="margin: 0 0 20px 0;padding: 0">
                    <!-- 关系链设置 -->
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">关系链设置</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="启用关系链">
                                <el-switch active-value="1" inactive-value="0" v-model="form.status" active-color="#29BA9C" inactive-color="#ccc">
                                </el-switch>
                                <div class="text-des-jur text">开启后首页也需要授权登录</div>
                            </el-form-item>
                            <el-form-item label="获得推广权益的条件">
                                <el-radio-group v-model="form.become">
                                    <div style="margin-top:10px;">
                                        <el-radio :label="0" :disabled = "form.become_child != 0 ? true : false">无条件</el-radio>
                                    </div>
                                    <div style="margin:10px 0;">
                                        <el-radio :label="1">申请</el-radio>
                                    </div>
                                    <el-radio :label="2">或</el-radio>
                                    <el-radio :label="3">与</el-radio>
                                    <div class="text-des text">[或]满足以下任意条件都可以成为推广员</div>
                                    <div class="text-des text">[与]满足以下所有条件才可以成为推广员</div>
                                </el-radio-group>
                            </el-form-item>
                            <!-- 控制显示隐藏块 -->
                            <div v-show="isTermShow">
                                <el-form-item>
                                    <el-checkbox-group v-model="form.become_term">
                                        <el-checkbox :label="2">
                                            消费达到 &nbsp;<el-input clearable v-model="form.become_ordercount" style="width:100%">
                                                <template slot="append">次</template>
                                            </el-input>
                                        </el-checkbox></br>
                                        <el-checkbox :label="3">
                                            消费达到 &nbsp;<el-input clearable v-model="form.become_moneycount" style="width:100%">
                                                <template slot="append">元</template>
                                            </el-input>
                                        </el-checkbox>
                                        <div>
                                            <el-checkbox :label="4">购买商品</el-checkbox>
                                        </div>
                                        <!-- 上传商品部分 -->
                                        <div>
                                            <div>
                                                <!-- 上传商品 -->
                                                <div class="addImg-box">
                                                    <div class="addImg-list" v-for="(good,i) in form.goods" :key="i" @click="uploadingGoods(0,i)">
                                                        <div class="itemImg_box">
                                                            <img style="width:150px;height:150px" :src="good.thumb" alt="">
                                                            <div class="middenTitle">【ID:[[ good.id ]]】[[ good.title ]]</div>
                                                        </div>
                                                        <div class="cancel-box" @click.stop="cancel(i)"><i class="cancel">×</i></div>
                                                        <div class="anew">点击重新上传</div>
                                                    </div>
                                                    <div @click.stop="uploadingGoods(1)" class="add-price-img">
                                                        <i class="el-icon-plus"></i>
                                                        <div class="select-price">选择商品</div>
                                                    </div>
                                                </div>
                                                <p class="text">可指定多件商品，只需购买其中一件就可以成为推广员，选择重复的商品保存后会只保留一个</p>
                                                <el-dialog width="55%" center title="选择商品" :visible.sync="isUploadingGoods">
                                                    <el-input clearable v-model="seekInput" placeholder="搜索商品名称" style="width:80%;margin-right:25px"></el-input>
                                                    <el-button @click="seekBtn(1)" @keyup.enter="seekBtn" type="primary">搜索</el-button>
                                                    <el-table ref="singleTable" v-loading="loading" height="400" :data="gridData">
                                                        <el-table-column property="id" label="ID"></el-table-column>
                                                        <el-table-column label="商品信息">
                                                            <template slot-scope="scope">
                                                                <img style="width:30px;height:30px;margin-right:5px" v-if="scope.row.thumb!==null && scope.row.thumb" :src="scope.row.thumb">
                                                                <span>[[scope.row.title]]</span>
                                                            </template>
                                                        </el-table-column>
                                                        <el-table-column label="操作">
                                                            <template slot-scope="scope">
                                                                <el-button @click="addGoods(scope.row,scope.$index)">操作</el-button>
                                                            </template>
                                                        </el-table-column>
                                                    </el-table>
                                                    <div style="margin-top:50px">
                                                        [[currentPage]]
                                                        <el-pagination layout="prev, pager, next,jumper" background style="text-align:center" :page-size="pagesize" :current-page="currentPage" :total="total" @current-change="handleCurrentChange">
                                                        </el-pagination>
                                                    </div>
                                                </el-dialog>
                                            </div>
                                            <div v-if="is_sales_commission == 1">
                                                <el-checkbox :label="5" style="margin-top:10px">
                                                    自购销售佣金累计达到 &nbsp;
                                                    <el-input clearable v-model="form.become_selfmoney" style="width:400px;"><template slot="append">元</template></el-input>
                                                </el-checkbox>
                                                <div style="margin-left:176px;">该条件只针对销售佣金插件使用</div>
                                            </div>
                                        </div>
                                    </el-checkbox-group>
                                    <el-form-item style=" width:600px;">
                                        <el-radio-group v-model="form.become_order">
                                            <el-radio :label="0">付款后</el-radio>
                                            <el-radio :label="1">完成后</el-radio>
                                        </el-radio-group>
                                        <div class="text">消费条件统计的方式</div>
                                    </el-form-item>
                                </el-form-item>
                            </div>
                            <el-form-item label="锁定客户的标准">
                                <el-radio-group v-model="form.become_child">
                                    <el-radio :label="0">点击首次分享链接</el-radio>
                                    <el-radio :label="1" :disabled = "form.become == 0 ? true : false">首次下单</el-radio>
                                    <el-radio :label="2" :disabled = "form.become == 0 ? true : false">首次付款</el-radio>
                                </el-radio-group>
                                <div class="text">首次下单/首次付款：无条件不可用</div>
                            </el-form-item>
                            <el-form-item label="是否需要审核才能推广">
                                <el-radio-group v-model="form.become_check">
                                    <el-radio :label="1">需要</el-radio>
                                    <el-radio :label="0">不需要</el-radio>
                                </el-radio-group>
                                <div class="text">以上条件达到后,是否需要审核才能推广客户</div>
                            </el-form-item>
                        </el-form>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">关系链</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="导入会员">
                                <el-button type="primary" @click="btnImport">重置</el-button>
                                <div class="select_text_box">
                                    <p>旧会员同步新关系链，如果不同步，除经销商团队业绩升级、统计团队业绩功能外，其他功能不影响使用。<br>
                                        如会员数据量过大，建议关闭站点再导入
                                    </p>
                                </div>
                            </el-form-item>
                        </el-form>
                    </div>
                    <div class="block">
                        <div style="background: #eff3f6;width:100%;height:15px;"></div>
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">奖励设置</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="推荐一个人奖励积分">
                                <el-input clearable v-model="form.reward_points" style="width:360px;"></el-input>
                                <div class="text">提示：只奖励给第一次锁定的上级会员</div>
                            </el-form-item>
                            <el-form-item label="赠送积分最大人数">
                                <el-input clearable v-model="form.maximum_number" style="width:360px;"></el-input>
                                <div class="text">不填或为0则不限制</div>
                            </el-form-item>
                        </el-form>
                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="vue-title">
                            <div class="vue-title-left"></div>
                            <div class="vue-title-content">页面设置</div>
                        </div>
                        <el-form ref="form" :model="form" label-width="15%">
                            <el-form-item label="推广中心跳转">
                                <el-switch active-value="1" inactive-value="0" v-model="form.is_jump" active-color="#29BA9C" inactive-color="#ccc" />
                            </el-form-item>
                            <div v-show="isSkipShow">
                                <el-form-item label="链接">
                                    <div>
                                        <el-input clearable v-model="form.jump_link" style="width:50%;margin-right:5px;"></el-input>
                                        <el-button @click="showLink('link','link_two')">选择链接</el-button>
                                        <div class="text">当会员没有获得推广资格的时候，点击推广中心跳转到指定的页面的，默认进入推广中心</div>
                                    </div>
                                </el-form-item>
                                <el-form-item label="小程序链接">
                                    <div>
                                        <el-input clearable v-model="form.small_jump_link" style="width:50%;margin-right:5px;"></el-input>
                                        <el-button @click="showLink('mini','min_link_two')">选择小程序链接</el-button>
                                    </div>
                                </el-form-item>
                            </div>
                            <el-form-item label="我的收入页面">
                                <el-switch active-value="1" inactive-value="0" v-model="form.share_page" active-color="#29BA9C" inactive-color="#ccc" />
                            </el-form-item>
                            <el-form-item label="收入明细购买者信息">
                                <el-switch active-value="1" inactive-value="0" v-model="form.share_page_deail" active-color="#29BA9C" inactive-color="#ccc" />
                            </el-form-item>
                            <pop :show="show" @replace="changeLink" @add="parHref"></pop>
                            <program :pro="pro" @replacepro="changeprogram" @addpro="parpro"></program>
                        </el-form>
                    </div>
                </div>
                <el-dialog width="60%" :visible.sync="showProgress" center :close-on-click-modal="false">
                    <h3 style="">清除旧数据进度,请勿刷新当前页面</h3>
                    <el-progress :percentage="del_process" ></el-progress>
                    <h3 style="">同步新数据进度,请勿刷新当前页面</h3>
                    <el-progress :percentage="process" ></el-progress>
                </el-dialog>
                <div class="confirm-btn">
                    <el-button type="primary" @click="submit">提交</el-button>
                </div>
            </div>
        </div>
    </div>
    @include('public.admin.pop')
    @include('public.admin.program')
    <script>
        var vm = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data() {
                let set = {!! json_encode(($set?:[])) !!};
                return {
                    uploadShow:false,
                    chooseImgName:'',
                    uploadListShow:false,
                    chooseImgListName:'',
                    type:'',
                    selNum:'',
                    form:{
                        ...set
                    },
                    //页面设置
                    page: {
                        promotion_skip: 0,
                        link: "",
                        applet_link: "",
                        income_page: 0,
                        buyer_info: 0
                    },
                    is_sales_commission:set.is_sales_commission?1:0,
                    isUploadingGoods:false,
                    goodsIndex:null,
                    seekInput: "",
                    //商品列表
                    gridData: [],
                    //页码数
                    currentPage: 1,
                    //一页显示数据
                    pagesize: 1,
                    //总页数
                    total: 1,
                    //当前响应的图标
                    loading: true,

                    process: 0,
                    showProgress: false,
                    del_process: 0,

                    show: false, //是否开启公众号弹窗
                    pro: false, //是否开启小程序弹窗
                    chooseLink:"",
                    chooseMiniLink:"",
                }
            },
            created() {
            },
            computed: {
                //判断支付佣金部分显示隐藏
                isTermShow() {
                    if (this.form.become === 2 || this.form.become === 3) {
                        return true
                    } else {
                        return false
                    }
                },
                //判断跳转部分显示隐藏
                isSkipShow() {
                    if (this.form.is_jump == "0") {
                        return false
                    } else {
                        return true
                    }
                }
            },
            methods: {
                submit() {
                    let that = this;
                    let url = '{!! yzWebFullUrl('member.member-set.relation-store') !!}';
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
                cancel(i) {
                    this.form.goods.splice(i, 1);
                },
                uploadingGoods(i, index) {
                    if (index !== undefined) {
                        //当前点击图片的下标
                        this.goodsIndex = index;
                    } else {
                        this.goodsIndex = null;
                    }
                    //点击打开搜索框
                    this.isUploadingGoods = true;
                    //请求当前商品的数据
                    this.postSearch(1);
                },
                //请求商品信息
                postSearch(page) {
                    this.$http.post("{!!yzWebFullUrl('member.member-level.search-goods')!!}", {
                        page
                    }).then(res => {
                        if (res.data.result === 1) {
                            this.loading = true;
                            setTimeout(() => {
                                this.loading = false;
                            }, 500)
                            let {
                                data: data,
                                total,
                                current_page: page,
                                per_page: size,
                            } = res.body.data.goods;
                            //商品列表
                            this.gridData = data;
                            //总数
                            this.total = total;
                            //一页显示的数据
                            this.pagesize = size;
                            //页码数
                            this.currentPage = page;
                        } else {
                            this.$message.error(res.data.msg);
                        }
                    })
                },
                seekBtn(page) {
                    this.$http.post("{!!yzWebFullUrl('member.member-relation.query')!!}", {
                        page,
                        keyword: this.seekInput
                    }).then(res => {
                        let {
                            data: data,
                            total,
                            current_page: page,
                            per_page: size,
                        } = res.body.data;
                        this.loading = true;
                        if (res.data.result == 1) {
                            setTimeout(() => {
                                this.loading = false;
                            }, 300)
                            // 商品列表
                            this.gridData = data;
                            //总数
                            this.total = total;
                            //一页显示的数据
                            this.pagesize = size;
                            //页码数
                            this.currentPage = page;
                        }
                    })
                },
                addGoods(val, i) {
                    if (this.goodsIndex == null) {
                        this.form.goods.push(val);
                    } else {
                        this.form.goods[this.goodsIndex] = val;
                        this.isUploadingGoods = false;
                    }
                },
                handleCurrentChange(page) {
                    if (this.seekInput) {
                        this.seekBtn(page);
                    } else {
                        this.postSearch(page);
                    }
                },
                //导入
                btnImport() {
                    this.count = 0;
                    this.process = 0;
                    this.showProgress = true;
                    this.$http.post("{!! yzWebFullUrl('member.member.exportRelation') !!}",{})
                        .then(response => {
                            if (response.data.result) {
                                this.process = parseInt(response.data.data.process);
                                this.del_process = parseInt(response.data.data.del_process);
                                this.loop(
                                    response.data.data.page,
                                    response.data.data.del_page,
                                    response.data.data.del_total,
                                    response.data.data.max_parent_id,
                                    response.data.data.max_child_id,
                                    response.data.data.max_member_id);
                            } else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                        },response => {

                        }
                    );
                },
                loop (page,del_page,del_total,max_parent_id,max_child_id,max_member_id) {
                    this.$http.post("{!! yzWebFullUrl('member.member.exportRelation') !!}",{
                        page:page,
                        del_page:del_page,
                        del_total:del_total,
                        max_parent_id:max_parent_id,
                        max_child_id:max_child_id,
                        max_member_id:max_member_id
                    })
                        .then(response => {
                            if (response.data.result) {
                                if (!this.showProgress) {
                                    this.$message({type: 'error',message: '终止同步!'});
                                    return;
                                }
                                if (response.data.data) {
                                    this.process = parseInt(response.data.data.process);
                                    this.del_process = parseInt(response.data.data.del_process);
                                }
                                if (response.data.data.status == true) {
                                    this.showProgress = false;
                                    this.$message({type: 'success',message: '同步成功!'});
                                } else {
                                    this.loop( response.data.data.page,
                                        response.data.data.del_page,
                                        response.data.data.del_total,
                                        response.data.data.max_parent_id,
                                        response.data.data.max_child_id,
                                        response.data.data.max_member_id);
                                }
                            } else {
                                this.$message({type: 'error',message: response.data.msg});
                                this.showProgress = false;
                            }
                        },response => {
                            this.$message({type: 'error',message: response.data.msg});
                            this.showProgress = false;
                        });
                },
                showLink(type, name) {
                    if (type == "link") {
                        this.chooseLink = name;
                        this.show = true;
                    } else {
                        this.chooseMiniLink = name;
                        this.pro = true;
                    }
                },
                //弹窗显示与隐藏的控制
                changeLink(item) {
                    this.show = item;
                },
                //当前链接的增加
                parHref(child, confirm) {
                    this.show = confirm;
                    this.form.jump_link = child;
                },
                //小程序链接
                changeprogram(item) {
                    this.pro = item;
                },
                parpro(child, confirm) {
                    this.pro = confirm;
                    this.form.small_jump_link = child;
                },
            }
        })
    </script>
    @endsection