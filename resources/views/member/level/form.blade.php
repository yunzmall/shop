@extends('layouts.base')
@section('会员等级设置')
@section('content')
<link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />
<style>
    .vue-title {
        display: flex;
        margin: 5px 0;
        line-height: 32px;
        /* font-size: 16px; */
        color: #333;
        font-weight: 600;
    }

    .vue-title-left {
        width: 4px;
        height: 18px;
        margin-top: 6px;
        background: #29ba9c;
        display: inline-block;
        margin-right: 10px;
    }

    .vue-title-content {
        font-size: 14px;
        font-family: "黑体";
        flex: 1;
    }


    .select-price {
        font-size: 13px;
        margin-top: 15px;
    }

    .el-form-item__label {
        font-size: 13px;
        font-family: "黑体";
        color: black;
        font-weight: 400;
    }

    /* 文本字体 */
    .el-form-item__content {
        font-size: 13px;
        color: #999;
        font-family: inherit;
        font-weight: 400;
    }

    /* 重点红色 */
    .sign-red {
        color: red;
    }

    /* 文本框 */
    .el-input {
        font-size: 13px;
    }

    /*上传图片 */
    .addImg-box {
        width: 700px;
        display: flex;
        flex-wrap: wrap;
        cursor: pointer;
    }

    /* 点击事件添加 */
    .addLevel {
        margin-left: 65px;
    }

    .el-icon-plus {
        font-size: 40px;
        margin-top: 45px;
    }

    .itemImg_box {}

    .addImg-list {
        height: 150px;
        width: 150px;
        margin-right: 20px;
        margin-bottom: 60px;
        position: relative;
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

    .cell {
        /* text-align: center; */
    }

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

    /* 表格居中 */
    .el-table tr .cell {
        text-align: center;
    }
</style>
<div class="all">
    <div id="app" v-cloak>
        <div class="total-head">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">会员等级设置</div>
            </div>
            <el-form>
                <el-form-item class="asterisk" label="等级权重" label-width="200px;" style="margin:10px 0 0 150px;">
                    <el-input clearable v-model.trim="form.weight" style="width:600px;" v-focus></el-input>
                    <div>等级权重，数字越大越高级</div>
                </el-form-item>
                <el-form-item class="asterisk" label="等级名称" label-width="200px;" style="margin:10px 0 0 150px;">
                    <el-input clearable v-model.trim="form.name" style="width:600px;"></el-input>
                </el-form-item>
                <el-form-item label="升级条件" label-width="200px;" style="margin:10px 0 0 150px;">
                    <div :class="[isUpLevel ? 'addLevel' : '' ]">
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
                            <el-dialog width="55%" center title="选择商品" :visible.sync="isUploadingGoos">
                                <el-input clearable v-model.trim="seekInput" placeholder="搜索id和商品名称" style="width:80%;margin-right:25px"></el-input>
                                <el-button @click="seekBtn" @keyup.enter="seekBtn" type="primary">搜索</el-button>
                                <el-table ref="singleTable" v-loading="loading" height="250" style=" width: 100%;" :data="gridData">
                                    <el-table-column header-align="center" property="id" label="ID"></el-table-column>
                                    <el-table-column header-align="center" label="商品信息">
                                        <template slot-scope="scope">
                                            <img v-if="scope.row.thumb!==null && scope.row.thumb" style="width:30px;height:30px;margin-right:5px" :src="scope.row.thumb" alt=""><span>[[scope.row.title]]</span>
                                        </template>
                                    </el-table-column>table
                                    <el-table-column label="操作" align="center">
                                        <template slot-scope="scope">
                                            <el-button @click="addGoods(scope.row,scope.$index)">操作</el-button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                                <div style="margin-top:50px">
                                    <el-pagination layout="prev, pager, next,jumper" background style="text-align:center" :page-size="pagesize" :current-page="currentPage" :total="total" @current-change="handleCurrentChange">
                                    </el-pagination>
                                </div>
                            </el-dialog>
                            <div style="margin:10px 0 0 0">可指定多件商品，只需购买其中一件</div>
                        </div>
                        <el-input clearable v-if="level_type==1" v-model.trim.number="form.count" style="width:600px;">
                            <div slot="prepend">订单数量满</div>
                            <div slot="append">个</div>
                        </el-input>
                        <el-input clearable v-else-if="level_type==2" v-model.trim="form.day" style="width:600px;">
                            <div slot="prepend">等级有效天数</div>
                            <div slot="append">天</div>
                        </el-input>
                        <el-input clearable v-else-if="level_type==3" v-model.trim="form.performance" style="width:600px;">
                            <div slot="prepend">团队业绩满(自购+一级+二级)</div>
                            <div slot="append">元</div>
                        </el-input>
                        <el-input clearable v-else-if="level_type==4" v-model.trim="form.recharge" style="width:600px;">
                            <div slot="prepend">一次性充满</div>
                            <div slot="append">元</div>
                        </el-input>
                        <el-input clearable v-else v-model.trim="form.money" style="width:600px;">
                            <div slot="prepend">订单金额满</div>
                            <div slot="append">元</div>
                        </el-input>
                        <div style="cursor: pointer;">会员升级条件，不填写默认为不自动升级, 设置
                            <a class="sign-red" href="{{ yzWebUrl('member.member-set.level') }}">【会员升级依据】</a>
                        </div>
                    </div>
                </el-form-item>
                <el-form-item v-if="level_discount_calculation == 0  || level_discount_calculation==null" label="折扣" label-width="200px;" style="margin:10px 0 0 150px;">
                    <el-input clearable v-model.trim="form.discount" style="width:600px;margin: 0 0 0 25px;">
                        <div slot="append">折</div>
                    </el-input>
                    <div style="margin: 0 0 0 65px;">请输入0~999之间的数字,计算方式以
                        <a class="sign-red" href="{!! yzWebUrl('member.member-set.level') !!}">【会员--会员设置--会员等级优惠计算方式】</a>
                        设置为准
                    </div>
                </el-form-item>
                <el-form-item v-else label="比例" label-width="200px;" style="margin:10px 0 0 150px;">
                    <el-input clearable v-model.trim="form.discount" style="width:600px;margin: 0 0 0 25px;">
                        <div slot="append">%</div>
                    </el-input>
                    <div style="margin: 0 0 0 65px;">请输入0~999之间的数字,计算方式以
                        <a class="sign-red" href="{!! yzWebUrl('member.member-set.level') !!}">【会员--会员设置--会员等级优惠计算方式】</a>
                        设置为准
                    </div>
                </el-form-item>
                <el-form-item label="运费减免" label-width="200px;" style="margin:10px 0 0 150px;">
                    <el-input clearable v-model.trim="form.freight" style="width:600px;">
                        <div slot="append">%</div>
                    </el-input>
                    <div>快递运费减免优惠%</div>
                </el-form-item>

                <el-form-item label="购买商品赠送消费积分比例" style="margin:10px 0 0 50px;" v-if="integral==1">
                    <el-input clearable v-model.trim="form.give_integral" style="width:600px;">
                        <div slot="append">%</div>
                    </el-input>
                    <div>例：购买2件，设置10%消费积分，赠送消费积分=商品实付金额 * 2 * 10%</div>
                </el-form-item>

                <el-form-item label="每天最高可获积分数量" style="margin:10px 0 0 75px;">
                    <el-input clearable v-model.trim="form.give_point_today" style="width:600px;">
                    </el-input>
					<div class="tip">填0或者不填即不限制,（商品赠送、订单赠送、后台充值、充值码充值、批量充值、转让-转入、返还、分销佣金转入）积分赠送类型不限制</div>
                </el-form-item>

                <el-form-item label="权益说明" label-width="200px;" style="margin:10px 0 0 150px;">
                    <!-- 富文本 -->
                    <tinymceee v-model.trim="form.instructions" style="width:70%" v-if="info"></tinymceee>
                    <div style="margin-left:63px;">权益说明在前端会员等级权益页面显示%</div>
                </el-form-item>
            </el-form>
        </div>
        <div class="fixed total-floo">
            <div class="fixed_box">
                <el-form>
                    <el-form-item>
                        <el-button type="primary" @click="submit">提交</el-button>
                        <el-button @click="returnList">返回列表</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</div>
<script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>
@include('public.admin.tinymceee')
@include('public.admin.uploadMultimediaImg')
<script>
    var vm = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        data() {
            let integral = '{!! $integral !!}';
            return {
                isUpLevel: false,
                integral:integral,
                form: {
                    weight: '',
                    name: '',
                    day: '',
                    discount: 0,
                    freight: 0,
                    instructions: '',
                    money: "",
                    count: "",
                    performance: "",
                    recharge: "",
                    give_integral:"",
                    give_point_today:"",
                },
                //上传商品
                isUploadingGoos: false,
                //商品
                //当前图片状态
                image: [],
                commodity_title: [],
                //商品的id
                goodsIdList: [],
                showimg: true,
                //判断富文本
                info: false,
                //判断购买的优惠
                level_type: "2",
                //判断折扣
                level_discount_calculation: "",
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
                //记录第几次请求
                isCount: 1,

            }
        },
        // 自定义组件
        directives: {
            // 注册一个局部的自定义指令 v-focus
            focus: {
                // 指令的定义
                inserted: function(el) {
                    // 聚焦元素
                    el.querySelector('input').focus()
                }
            }
        },
        watch: {
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
        created() {
            g = this
            //优化在不同设备固定定位挡住的现象设置父元素的内边距
            window.onload = function() {
                let all = document.querySelector(".all");
                let h = window.innerHeight * 0.03;
                all.style.paddingBottom = h + "px";
            }
            let i = window.location.href.indexOf('id=');
            if (i !== -1) {
                let id = Number(window.location.href.slice(i + 3))
                this.id = id;
                //添加和更新数据控制器
                this.isi = true;
                this.postUpdateLevel(id);
            } else {
                //控制富文本显示隐藏
                this.info = true;
                this.postAddLevel();
            }
            //全局监听searchBtnt enter事件
            document.onkeydown = (e) => {
                let key = window.event.keyCode;
                if (key == 13) {
                    g.seekBtn(1);
                }
            }
        },
        methods: {
            //回退
            hisGo(i) {
                //  console.log(i);
                history.go(i)
            },
            //添加会员等级
            postAddLevel() {
                this.$http.post("{!!yzWebFullUrl('member.member-level.store')!!}", {
                    level: this.isCount > 1 ? {
                        level: this.form.weight,
                        level_name: this.form.name,
                        order_money: this.form.money,
                        order_count: this.form.count,
                        goods_id: this.goodsIdList,
                        discount: this.form.discount,
                        freight_reduction: this.form.freight,
                        validity: this.form.day,
                        description: this.form.instructions,
                        team_performance: this.form.performance,
                        balance_recharge: this.form.recharge,
                        give_integral:this.form.give_integral,
                        give_point_today:this.form.give_point_today,
                    } : ""
                }).then(res => {
                    console.log(res);
                    if (this.isCount > 1) {
                        if (res.data.result == 1) {
                            this.$message.success(res.data.msg)
                            setTimeout(() => {
                                window.history.back(-1);
                            }, 500)
                        } else {
                            this.$message.error(res.data.msg)
                        }
                    } else {
                        let {
                            level_type,
                            level_discount_calculation
                        } = res.body.data.shopSet;
                        this.level_type = level_type;
                        //折扣
                        this.level_discount_calculation = Number(level_discount_calculation);
                        if (this.level_type == 2) {
                            this.isUpLevel = true;
                            this.showimg = true;
                        }
                    }
                })
            },

            //会员等级数据
            postUpdateLevel(id) {
                console.log(id, 56622621);
                this.$http.post("{!!yzWebFullUrl('member.member-level.update')!!}", {
                    id: id,
                    level: this.isCount > 1 ? {
                        level: this.form.weight,
                        level_name: this.form.name,
                        order_money: this.form.money,
                        order_count: this.form.count,
                        goods_id: this.goodsIdList,
                        discount: this.form.discount,
                        freight_reduction: this.form.freight,
                        validity: this.form.day,
                        description: this.form.instructions,
                        team_performance: this.form.performance,
                        balance_recharge: this.form.recharge,
                        give_integral:this.form.give_integral,
                        give_point_today:this.form.give_point_today,
                    } : ""
                }).then(res => {
                    console.log(res);
                    // console.log(545454);
                    if (this.isCount == 1) {
                        const {
                            levelModel: model,
                            shopSet: shopSet
                        } = res.body.data;
                        //等级权重
                        let {
                            goods
                        } = model;
                        this.form.weight = model.level;
                        //等级名称
                        this.form.name = model.level_name;
                        //等级有效天数
                        this.form.day = model.validity;
                        //折扣
                        if (model.discount == "") {
                            this.form.discount = 0;
                        } else {
                            this.form.discount = model.discount;
                        }
                        //运费免检
                        if (model.freight_reduction == "") {
                            this.form.freight = 0;
                        } else {
                            this.form.freight = model.freight_reduction;
                        }
                        //权益说明
                        this.form.give_integral = model.give_integral;
                        this.form.give_point_today = model.give_point_today;
                        this.form.instructions = model.description;
                        this.info = true;
                        // console.log(11111);
                        //升级商品图片
                        this.level_type = Number(shopSet.level_type);
                        // console.log(this.level_type,4465465);
                        if (this.level_type == 2) {
                            this.isUpLevel = true;
                            //控制显示状态
                            if (goods.length !== 0) {
                                // this.isUpLevel = true;
                                this.showimg = true;
                                for (item of goods) {
                                    this.image.push(item.thumb);
                                    this.goodsIdList.push(item.id);
                                    this.commodity_title.push(item.title);
                                }
                            }
                        }
                        //金额
                        this.form.money = model.order_money;
                        //订单
                        this.form.count = model.order_count;
                        //团队业绩自选
                        this.form.performance = model.team_performance;
                        //一次性充值
                        this.form.recharge = model.balance_recharge;
                        //折扣
                        this.level_discount_calculation = Number(shopSet.level_discount_calculation);
                    } else {
                        if (res.data.result == 1) {
                            this.$message.success(res.data.msg);
                            setTimeout(() => {
                                window.history.back(-1);
                            }, 300)
                        } else {
                            this.$message.error(res.data.msg)
                        }
                    }
                })
            },
            //更新会员等级
            postSetUpdateLevel(id) {
                this.$http.post("{!!yzWebFullUrl('member.member-level.update-datas')!!}", {
                    id: id,
                    level: {
                        level: this.form.weight,
                        level_name: this.form.name,
                        order_money: this.form.money,
                        order_count: this.form.count,
                        goods_id: this.goodsIdList,
                        discount: this.form.discount,
                        freight_reduction: this.form.freight,
                        validity: this.form.day,
                        description: this.form.instructions,
                        team_performance: this.form.performance,
                        balance_recharge: this.form.recharge,
                        give_integral:this.form.give_integral,
                        give_point_today:this.form.give_point_today,
                    }
                }).then(res => {
                    if (res.data.result == 1) {
                        this.$message.success(res.data.msg)
                        setTimeout(() => {
                            window.history.back(-1);
                        }, 500)
                    } else {
                        this.$message.error(res.data.msg)
                    }
                })
            },
            //点击显示
            upLevel() {},
            uploadingGoods(i, index) {
                //当前点击的东西
                this.isIndex = i;
                if (index !== undefined) {
                    //当前点击图片的下标
                    this.indexImg = index;
                }
                //点击打开搜索框
                this.isUploadingGoos = true;
                //打开加载图标
                this.loading = true;
                setTimeout(() => {
                    this.loading = false;
                }, 500)
                this.postSearch(1);
            },
            //请求商品信息
            postSearch(page) {
                this.$http.post("{!!yzWebFullUrl('member.member-level.search-goods')!!}", {
                    page
                }).then(res => {
                    console.log(res);
                    this.loading = true;
                    if (res.data.result == 1) {
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
                        // console.log(total, 222126464);
                        //总数
                        this.total = total;
                        //一页显示的数据
                        this.pagesize = size;
                        //页码数
                        this.currentPage = page;
                    } else {
                        this.$message.error(res.data.msg)
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
            //点击提交
            submit() {
                this.isCount++;
                //更新数据
                if (this.isi) {
                    //更新会员等级
                    console.log(this.id);
                    this.postUpdateLevel(this.id)
                } else {
                    //添加会员等级
                    this.postAddLevel();
                }

            },
            //返回列表
            returnList() {
                window.history.back(-1);
            },
            //点击切换分页
            handleCurrentChange(page) {
                if (this.seekInput) {
                    this.seekBtn(page)
                } else {
                    this.postSearch(page)
                }
            },
            //搜索商品
            seekBtn(page) {
                this.$http.post("{!!yzWebFullUrl('member.member-level.search-goods')!!}", {
                    page,
                    keyword: this.seekInput
                }).then(res => {
                    let {
                        data: data,
                        current_page,
                        per_page,
                        total
                    } = res.body.data.goods;
                    console.log(res);
                    this.loading = true;
                    if (res.data.result === 1) {
                        setTimeout(() => {
                            this.loading = false;
                        }, 300)
                        //总数
                        this.total = total;
                        //一页显示的数据
                        this.pagesize = per_page;
                        //页码数
                        this.currentPage = current_page;
                        //商品列表
                        this.gridData = data;
                    }
                })
            }
        }
    })
</script>
@endsection