    @extends('layouts.base')
    @section('title', '会员关系设置')
    @section('content')

    <link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
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
            font-weight: 600;
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
    <div class="all">
        <div id="app" v-cloak>
            <!-- 关系设置 -->
            <div class="total-head">
                <div class="vue-title">
                    <div class="vue-title-left"></div>
                    <div class="vue-title-content">关系设置</div>
                </div>
                <el-form label-width="300px">
                    <el-form-item label="启用关系链">
                        <el-switch active-value="1" inactive-value="0" v-model="setdata.status" active-color="#29BA9C" inactive-color="#ccc">
                        </el-switch>
                        <div class="text-des-jur text">开启后首页也需要授权登录</div>
                    </el-form-item>
                    <el-form-item label="获得推广权益的条件">
                        <el-radio-group v-model="setdata.become">
                            <div style="margin-top:10px;">
                                <el-radio :label="0">无条件</el-radio>
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
                            <el-checkbox-group v-model="setdata.become_term">
                                <el-checkbox :label="2">
                                    消费达到 &nbsp;<el-input clearable v-model="setdata.become_ordercount" style="width:100%">
                                        <template slot="append">次</template>
                                    </el-input>
                                </el-checkbox></br>
                                <el-checkbox :label="3">
                                    消费达到 &nbsp;<el-input clearable v-model="setdata.become_moneycount" style="width:100%">
                                        <template slot="append">元</template>
                                    </el-input>
                                </el-checkbox>
                                <div>
                                    <el-checkbox :label="4">购买商品</el-checkbox>
                                </div>
                                <!-- 上传商品部分 -->
                                <div>
                                    <div v-show="isUpLevel">
                                        <!-- 上传商品 -->
                                        <div class="addImg-box">
                                            <div class="addImg-list" v-for="(itemImg,i) in image" :key="i" @click="uploadingGoods(0,i)">
                                                <div class="itemImg_box">
                                                    <img style="width:150px;height:150px" :src="itemImg" alt="">
                                                    <div class="middenTitle">【ID:[[ goodsIdList[i] ]]】[[ commodity_title[i] ]]</div>
                                                </div>
                                                <div class="cancel-box" @click.stop="cancel(i)"><i class="cancel">×</i></div>
                                                <div class="anew">点击重新上传</div>
                                            </div>
                                            <div @click.stop="uploadingGoods(1)" class="add-price-img">
                                                <i class="el-icon-plus"></i>
                                                <div class="select-price">选择商品</div>
                                            </div>
                                        </div>
                                        <p class="text">可指定多件商品，只需购买其中一件就可以成为推广员</p>
                                        <el-dialog width="55%" center title="选择商品" :visible.sync="isUploadingGoos">
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
                                            <el-input clearable v-model="setdata.become_commission" style="width:400px;"><template slot="append">元</template></el-input>
                                        </el-checkbox>
                                        <div style="margin-left:176px;">该条件只针对销售佣金插件使用</div>
                                    </div>
                            </el-checkbox-group>
                        </el-form-item>
                        <el-form-item style=" width:600px;">
                            <el-radio-group v-model="setdata.payment">
                                <el-radio :label="0">付款后</el-radio>
                                <el-radio :label="1">完成后</el-radio>
                            </el-radio-group>
                            <div class="text">消费条件统计的方式</div>
                        </el-form-item>
                    </div>
            </div>
            <el-form-item label="锁定客户的标准">
                <el-radio-group v-model="setdata.firstOrder">
                    <el-radio :label="0">点击首次分享链接</el-radio>
                    <el-radio :label="1">首次下单</el-radio>
                    <el-radio :label="2">首次付款</el-radio>
                </el-radio-group>
                <div class="text">首次下单/首次付款：无条件不可用</div>
            </el-form-item>
            <el-form-item label="是否需要审核才能推广">
                <el-radio-group v-model="setdata.rights">
                    <el-radio :label="1">需要</el-radio>
                    <el-radio :label="0">不需要</el-radio>
                </el-radio-group>
                <div class="text">以上条件达到后,是否需要审核才能推广客户</div>
            </el-form-item>
            </el-form>
        </div>
        <!-- 奖励设置 -->
        <div class="total-sect">
            <el-form label-width="300px">
                <div class="vue-title">
                    <div class="vue-title-left"></div>
                    <div class="vue-title-content">奖励设置</div>
                </div>
                <el-form-item label="推荐一个人奖励积分">
                    <el-input clearable v-model="reward.integral" style="width:360px;"></el-input>
                    <div class="text">提示：只奖励给第一次锁定的上级会员</div>
                </el-form-item>
                <el-form-item label="赠送积分最大人数">
                    <el-input clearable v-model="reward.integral_people" style="width:360px;"></el-input>
                    <div class="text">不填或为0则不限制</div>
                </el-form-item>
            </el-form>
        </div>
        <!-- 页面设置 -->
        <div class="total-floo">
            <el-form label-width="300px">
                <div class="vue-title">
                    <div class="vue-title-left"></div>
                    <div class="vue-title-content">页面设置</div>
                </div>
                <el-form-item label="推广中心跳转">
                    <el-switch active-value="1" inactive-value="0" v-model="page.promotion_skip" active-color="#29BA9C" inactive-color="#ccc" />
                </el-form-item>
                <div v-show="isSkipShow">
                    <el-form-item label="链接">
                        <div>
                            <el-input clearable v-model="page.link" style="width:50%;margin-right:5px;"></el-input>
                            <el-button @click="showLink('link','link_two')">选择链接</el-button>
                            <div class="text">当会员没有获得推广资格的时候，点击推广中心跳转到指定的页面的，默认进入推广中心</div>
                        </div>
                    </el-form-item>
                    <el-form-item label="小程序链接">
                        <div>
                            <el-input clearable v-model="page.applet_link" style="width:50%;margin-right:5px;"></el-input>
                            <el-button @click="showLink('mini','min_link_two')">选择小程序链接</el-button>
                        </div>
                    </el-form-item>
                </div>
                <el-form-item label="我的收入页面">
                    <el-switch active-value="1" inactive-value="0" v-model="page.income_page" active-color="#29BA9C" inactive-color="#ccc" />
                </el-form-item>
                <el-form-item label="收入明细购买者信息">
                    <el-switch active-value="1" inactive-value="0" v-model="page.buyer_info" active-color="#29BA9C" inactive-color="#ccc" />
                </el-form-item>
                <pop :show="show" @replace="changeLink" @add="parHref"></pop>
                <program :pro="pro" @replacepro="changeprogram" @addpro="parpro"></program>
            </el-form>
        </div>
        <div class="fixed total-floo">
            <div class="fixed_box">
                <el-form>
                    <el-form-item>
                        <el-button type="primary" @click="submit" size="default">提交</el-button>
                    </el-form-item>
                </el-form>
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
                return {
                    show: false, //是否开启公众号弹窗
                    pro: false, //是否开启小程序弹窗
                    //关系设置
                    setdata: {
                        status: "0",
                        become: 0,
                        become_term: [],
                        become_ordercount: "",
                        become_moneycount: "",
                        become_commission: "",
                        become_goods_id: "0",
                        become_goods: "0",
                        //单选按钮
                        payment: 1,
                        firstOrder: 0,
                        rights: 1,

                    },
                    //奖励设置
                    reward: {
                        integral: "",
                        integral_people: 30
                    },
                    //页面设置
                    page: {
                        promotion_skip: 0,
                        link: "",
                        applet_link: "",
                        income_page: 0,
                        buyer_info: 0
                    },
                    //判断显示隐藏
                    isShow: false,
                    //上传商品
                    isUpLevel: true,
                    //上传商品
                    isUploadingGoos: false,
                    //商品
                    //当前图片状态
                    image: [],
                    commodity_title: [],
                    //商品的id
                    goodsIdList: [],
                    showimg: true,
                    //接收的id
                    id: "",
                    //判断点击的东西
                    isIndex: 1,
                    //点击当前的图片index
                    indexImg: 0,
                    //判断是否有isCreated
                    isCreated: false,
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
                    isi: false,
                    //搜索
                    seekInput: "",
                    isEvent: true,
                    //判断自购插件是否开启
                    is_sales_commission: 0,
                }
            },
            created() {
                g = this;
                //优化在不同设备固定定位挡住的现象设置父元素的内边距
                window.onload = function() {
                    let all = document.querySelector(".all");
                    let h = window.innerHeight * 0.03;
                    all.style.paddingBottom = h + "px";
                }
                //当前请求的网络数据
                this.postRelation();
                //全局监听searchBtnt enter事件
                document.onkeydown = (e) => {
                    let key = window.event.keyCode;
                    if (key == 13) {
                        g.seekBtn(1);
                    }
                }
            },
            watch: {
                "setdata.become_term"(newVal) {
                    console.log(newVal);
                    //关闭点击事件
                    this.isEvent = true
                    newVal.forEach(item => {
                        if (item === 4) {
                            //打开点击事件
                            this.isEvent = false
                        }
                    })
                },
                "gridData": {
                    handler() {
                        // console.log(this.$refs);
                        if (this.$refs.singleTable) {
                            this.$nextTick(() => {
                                this.$refs.singleTable.doLayout(); // 解决表格错位
                            })
                        }
                    },
                    deep: true
                }
            },
            computed: {
                //判断支付佣金部分显示隐藏
                isTermShow() {
                    if (this.setdata.become === 2 || this.setdata.become === 3) {
                        this.isShow = true
                    } else {
                        this.isShow = false
                    }
                    return this.isShow
                },
                //判断跳转部分显示隐藏
                isSkipShow() {
                    if (this.page.promotion_skip == "0") {
                        return false
                    } else {
                        return true
                    }
                }
            },
            methods: {
                //请求数据
                postRelation() {
                    this.$http.post("{!!yzWebFullUrl('member.member-relation.show')!!}").then(res => {
                        let {
                            relationship,
                            reward,
                            page
                        } = res.body.data;
                        let {
                            is_sales_commission,
                            status,
                            become,
                            become_term2,
                            become_term3,
                            become_term4,
                            become_term5,
                            goods,
                            become_ordercount,
                            become_moneycount,
                            become_selfmoney,
                            become_order,
                            become_child,
                            become_check,
                        } = relationship;
                        console.log(res);
                        console.log(relationship);
                        if (this.isObject(relationship)) {
                            //插件开关
                            // console.log(sales,45456);
                            this.is_sales_commission = is_sales_commission;
                            //启用关系链
                            this.setdata.status = String(status);
                            //推广条件
                            this.setdata.become = become;
                            let become_term = {
                                become_term2,
                                become_term3,
                                become_term4,
                                become_term5
                            }
                            console.log(become_term, 999894);
                            //消费条件
                            this.setdata.become_term = [];
                            for (let item in become_term) {
                                if (become_term[item] !== "") {
                                    this.setdata.become_term.push(Number(become_term[item]));
                                }
                                this.isEvent = false;
                                // console.log(become_term[item] == "4",45456);
                                if (become_term[item] == "4") {
                                    if (goods !== null && goods.length !== 0) {
                                        for (let i in goods) {
                                            // 购买商品
                                            if (goods[i].thumb) {
                                                this.image.push(goods[i].thumb);
                                            }
                                            if (goods[i].title) {
                                                this.commodity_title.push(goods[i].title);
                                            }
                                            if (goods[i].id) {
                                                this.goodsIdList.push(goods[i].id);
                                            }
                                        }
                                    }
                                }
                            }
                            //消费达到多少次
                            this.setdata.become_ordercount = become_ordercount;
                            //消费达到多少元
                            this.setdata.become_moneycount = become_moneycount;
                            //自购
                            this.setdata.become_commission = become_selfmoney;
                            //付款后
                            this.setdata.payment = become_order;
                            //首单付款
                            this.setdata.firstOrder = become_child;
                            //是否审核
                            this.setdata.rights = become_check;
                        }
                        let {
                            reward_points,
                            maximum_number
                        } = reward
                        if (this.isObject(reward)) {
                            console.log(454);
                            //推荐一个人奖励积分
                            this.reward.integral = reward_points;
                            //赠送积分最大人数
                            this.reward.integral_people = maximum_number;
                        }
                        let {
                            is_jump,
                            share_page,
                            share_page_deail,
                            jump_link,
                            small_jump_link
                        } = page
                        if (this.isObject(page))
                            //推荐中心跳转
                            this.page.promotion_skip = String(is_jump);
                        //收入页面
                        this.page.income_page = String(share_page);
                        //收入明细
                        this.page.buyer_info = String(share_page_deail);
                        //跳转链接
                        this.page.link = jump_link;
                        //小程序链接
                        this.page.applet_link = small_jump_link;
                    })
                },
                //弹窗显示与隐藏的控制
                changeLink(item) {
                    this.show = item;
                },
                //当前链接的增加
                parHref(child, confirm) {
                    this.show = confirm;
                    this.page.link = child;
                },
                //小程序链接
                changeprogram(item) {
                    this.pro = item;
                },
                parpro(child, confirm) {
                    this.pro = confirm;
                    this.page.applet_link = child;
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
                isObject(obj) {
                    if (obj !== null && JSON.stringify(obj) !== "{}") {
                        return true;
                    }
                },
                //上传商品部分
                uploadingGoods(i, index) {
                    //判断点击
                    if (this.isEvent) {
                        return
                    }
                    //当前点击的东西
                    this.isIndex = i;
                    if (index !== undefined) {
                        //当前点击图片的下标
                        this.indexImg = index;
                    }
                    //点击打开搜索框
                    this.isUploadingGoos = true;
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
                            console.log(res);
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
                cancel(i) {
                    //删除当前图片
                    this.image.splice(i, 1);
                    //删除图片文字
                    this.commodity_title.splice(i, 1);
                    //删除图片id
                    this.goodsIdList.splice(i, 1);
                },
                //提交商品信息
                addGoods(val, i) {
                    if (this.isIndex == 1) {
                        //商品的图片
                        this.image.push(val.thumb);
                        //商品id
                        // become_goods_id
                        this.goodsIdList.push(val.id)
                        //商品名称
                        this.commodity_title.push(val.title)
                    } else {
                        //替换当前图片
                        //商品的图片
                        this.image[this.indexImg] = val.thumb;
                        //商品id
                        this.goodsIdList[this.indexImg] = val.id;
                        //商品名称
                        this.commodity_title[this.indexImg] = val.title;
                        //替换商品关闭搜索
                        this.isUploadingGoos = false;
                    }
                },
                handleCurrentChange(page) {
                    if (this.seekInput) {
                        this.seekBtn(page);
                    } else {
                        this.postSearch(page);
                    }
                },
                //搜索商品
                seekBtn(page) {
                    console.log(page, 9999);
                    this.$http.post("{!!yzWebFullUrl('member.member-relation.query')!!}", {
                        page,
                        keyword: this.seekInput
                    }).then(res => {
                        // console.log(res);
                        // console.log(res.body.data.data);
                        let {
                            data: data,
                            total,
                            current_page: page,
                            per_page: size,
                        } = res.body.data;
                        // console.log(data);
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
                            console.log(this.currentPage, 98622);
                        }
                    })
                },
                //保存数据请求
                postSave() {
                    let become_term = {};
                    this.setdata.become_term.forEach(item => {
                        become_term[item] = item;
                    })
                    this.$http.post("{!!yzWebFullUrl('member.member-relation.save')!!}", {
                        setdata: {
                            status: this.setdata.status,
                            become: this.setdata.become,
                            become_goods_id: this.goodsIdList,
                            become_order: this.setdata.payment,
                            become_child: this.setdata.firstOrder,
                            become_ordercount: this.setdata.become_ordercount,
                            become_moneycount: this.setdata.become_moneycount,
                            become_check: this.setdata.rights,
                            become_selfmoney: this.setdata.become_commission,
                            become_term: become_term,
                            reward_points: this.reward.integral,
                            maximum_number: this.reward.integral_people,
                            become_goods: this.goodsTurnJson(),
                            share_page: this.page.income_page,
                            share_page_deail: this.page.buyer_info
                        },
                        setting: {
                            is_jump: this.page.promotion_skip,
                            jump_link: this.page.link,
                            small_jump_link: this.page.applet_link,
                        },
                    }).then((res) => {
                        console.log(res);
                        if (res.data.result === 1) {
                            this.$message.success("设置成功");
                            history.go(0);
                        } else {
                            this.$message.error(res.data.msg)
                        }
                    })
                },
                //转换json格式
                goodsTurnJson() {
                    let Arr = [];
                    let Obj = null;
                    this.image.forEach((item, i) => {
                        Obj = {
                            goods_id: this.goodsIdList[i],
                            title: this.commodity_title[i],
                            thumb: item
                        }
                        Arr.push(Obj)
                    })
                    let Json = {}
                    Arr.map((e, i) => {
                        Json[i] = e;
                    });
                    return Json;
                },
                //提交保存
                submit() {
                    if (this.setdata.become == 0 && this.setdata.firstOrder == 2) {
                        this.$message.error("无条件和首次付款不能同时选择!  重新选择!")
                    } else if (this.setdata.become == 0 && this.setdata.firstOrder == 1) {
                        this.$message.error("无条件和首次下单不能同时选择!  重新选择!")
                    } else {
                        //保存当前的设置
                        this.postSave()
                    }
                }
            }
        })
    </script>
    @endsection