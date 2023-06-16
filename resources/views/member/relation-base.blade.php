@extends('layouts.base')
@section('title', '会员设置')
@section('content')
<link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />

<!-- 引入样式 -->
<!-- 引入组件库 -->
<link rel="stylesheet" href="{{static_url('../resources/views/goods/assets/css/common.css?time='.time())}}">
@include('public.admin.uploadMultimediaImg')

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
        font-weight: 600;
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
<div class="all">
    <div id="app" v-cloak>
        <!-- 头部 -->
        <div class="total-head">
            <el-form>
                <el-form-item>
                    <div class="vue-title">
                        <div class="vue-title-left"></div>
                        <div class="vue-title-content">会员设置</div>
                    </div>
                </el-form-item>
                <!-- 上传头像 -->
                <el-form-item class="select-img" label="Banner">
                    <!-- 上传图片 -->
                    <!--
                              selNum  参数
                                 one 上传一张图片
                                 more 上传多张图片
                         -->
                         <!-- :class="[isUpLevel ? 'addLevel' : '' ]" -->
                        <div v-show="isUpLevel">
                            <!-- 上传图片 -->
                            <div class="addImg-box">
                                <div v-show="showimg" @click="uploadPictures('thumb',1,'1')" class="addImg-list">
                                    <div v-show="showimg" style="width:100%;height:100%">
                                        <img style="width:100%;height:100%" v-show="showimg" :src="image" alt="">
                                    </div>
                                    <div v-show="showimg" class="cancel-box"><i class="cancel" @click.stop="DeletePictures">×</i></div>
                                    <div v-show="showimg" class="anew">点击重新上传</div>
                                </div>
                                <div v-show="!showimg" class="add-price-img" @click="uploadPictures('thumb',1,'2')">
                                    <i class="el-icon-plus"></i>
                                    <div class="select-price">选择图片</div>
                                </div>
                                <span class="rectangle-txt">长方形图片</span>
                            </div>
                            <!-- 上传图片 -->
                            <!--
                              selNum  参数
                                 one 上传一张图片
                                 more 上传多张图片
                         -->
                            <upload-multimedia-img :upload-show="showSelectMaterialPopup" :type="materialType" :name="formFieldName" :sel-num="isSelNum" @replace="showSelectMaterialPopup = !showSelectMaterialPopup" @sure="selectedMaterial">
                            </upload-multimedia-img>

                        </div>
                </el-form-item>
            </el-form>
        </div>
        <!-- 内容 -->
        <div class="total-sect">
            <el-form>
                <el-form-item>
                    <div class="vue-title">
                        <div class="vue-title-left"></div>
                        <div class="vue-title-content">通知设置</div>
                    </div>
                </el-form-item>
                <!-- 推广客户 -->
                <el-form-item style="margin-left:100px" label="获得推广权限通知">
                    <el-select clearable style="width:500px;margin-right:10px" v-model="putVal" filterable placeholder="请选择">
                        <el-option v-for="item in putList" :key="item.id" :label="item.title" :value="item.id">
                        </el-option>
                    </el-select>
                    <!-- 提示文字 -->
                    <el-tooltip v-model="isPut_istop_tool" :manual="true" effect="dark" :content="isPut_title_tool" placement="top">
                        <el-switch v-model="isPut" @click.native="isOpenWeChatTemp(isPut,1)" active-color="#29BA9C" inactive-color="#ccc">
                        </el-switch>
                    </el-tooltip>
                </el-form-item>
                <!-- 新增客户 -->
                <el-form-item style="margin-left:128px" label="新增客户通知">
                    <el-select clearable style="width:500px;margin-right:10px" v-model="clientVal" filterable placeholder="请选择">
                        <el-option v-for="item in clientList" :key="item.id" :label="item.title" :value="item.id">
                        </el-option>
                    </el-select>
                    <el-tooltip v-model="isClient_istop_tool" :manual="true" effect="dark" :content="isClient_title_tool" placement="top">
                        <el-switch v-model="isClient" @click.native="isOpenWeChatTemp(isClient,2)" active-color="#29BA9C" inactive-color="#ccc">
                        </el-switch>
                    </el-tooltip>
                </el-form-item>
            </el-form>
        </div>
        <div class="total-sect">
            <el-form>
                <!-- 会员设置 -->
                <el-form-item>
                    <div class="vue-title">
                        <div class="vue-title-left"></div>
                        <div class="vue-title-content">会员关系</div>
                    </div>
                </el-form-item>
                <el-form-item style="margin-left:60px" label="会员关系页面显示推荐人">
                    <el-switch active-value="1" inactive-value="0" v-model="relation.refe" active-color="#29BA9C" inactive-color="#ccc"></el-switch>
                </el-form-item>
                <el-form-item style="margin-left:32px" label="会员关系页面显示推荐人上级">
                    <el-switch active-value="1" inactive-value="0" v-model="relation.refe_boss" active-color="#29BA9C" inactive-color="#ccc"></el-switch>
                </el-form-item>
                <el-form-item style="margin-left:103px" label="推荐人电话微信号">
                    <el-switch active-value="1" inactive-value="0" v-model="relation.refe_phone" active-color="#29BA9C" inactive-color="#ccc"></el-switch>
                </el-form-item>
                <el-form-item style="margin-left:135px" label="显示关系等级">
                    <el-checkbox-group v-model="info">
                        <el-checkbox label="1">
                            <span class="checkLis_text">1级</span>
                            <!-- :disabled="isDisabled('1级')" -->
                            <el-input clearable style="width:300px" v-model="relation.oneEvel" placeholder="一级关系等级"></el-input>
                        </el-checkbox><br>
                        <el-checkbox  label="2" style="margin-left: 96px; margin-top:10px">
                            <span class="checkLis_text">2级</span>
                            <el-input clearable v-model="relation.twoEvel" style="width:300px" placeholder="二级关系等级"></el-input>
                        </el-checkbox>
                    </el-checkbox-group>
                </el-form-item>
                <el-form-item style="margin-left:164px;" label="显示按钮">
                    <el-checkbox-group style="line-height: 0; margin-top:10px" v-model="relation.nameInfo">
                        <el-checkbox :label="'wechat'">微信</el-checkbox>
                        <el-checkbox :label="'phone'">手机</el-checkbox>
                        <el-checkbox :label="'realname'">姓名</el-checkbox>
                    </el-checkbox-group>
                    <div class="select_text_box">
                        <p>勾选后,页面将显示下级微信、手机号,如果个人资料中没有填写微信和绑定手机号,将不显示图标 !</p>
                    </div>
                </el-form-item>
                <el-form-item style="margin-left:164px;" label="统计商品">
                    <el-radio-group v-model="relation.is_statistical_goods">
                        <el-radio label="1">开启</el-radio>
                        <el-radio label="0">关闭</el-radio>
                    </el-radio-group>
                    <div class="select_text_box" style="margin: 0 0 10px;line-height: 1;">开启后,前端会员中心-客户显示商品的购买数量(统计团队已完成订单的商品数量，包含自购)</div>
                    <div style="display: flex;" v-if="relation.is_statistical_goods == 1">
                        <div class="add-goods-box" @click="showGoodsList">
                            <div style="text-align: center;line-height: 2;">
                                <i class="el-icon-plus" style="font-size: 30px;color: #ccc;"></i>
                                <div>添加商品</div>
                            </div>
                        </div>
                        <div style="margin: 0 10px;display: flex;" v-for="(item,i) in relation.statistical_goods" :key="i">
                            <div>
                                <div class="img-box">
                                    <img :src="item.thumb_url" style="width: 100%;height: 100%;">
                                    <div class="delete">
                                        <i class="el-icon-delete" style="color: red;font-size: 20px;" @click="relation.statistical_goods.splice(i,1)"></i>
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
        <div class="total-sect">
            <el-form>
                <el-form-item>
                    <div class="vue-title">
                        <div class="vue-title-left"></div>
                        <div class="vue-title-content">关系链</div>
                    </div>
                </el-form-item>
                <el-form-item style="margin-left:166px;" label="导入会员">
                    <el-button type="primary" @click="btnImport">重置</el-button>
                    <div class="select_text_box">
                        <p>旧会员同步新关系链，如果不同步，除经销商团队业绩升级、统计团队业绩功能外，其他功能不影响使用。<br>
                            如会员数据量过大，建议关闭站点再导入
                        </p>
                    </div>
                </el-form-item>
            </el-form>
        </div>
        <div class="total-floo">
            <el-form>
                <el-form-item>
                    <div class="vue-title">
                        <div class="vue-title-left"></div>
                        <div class="vue-title-content">会员合并</div>
                    </div>
                </el-form-item>
                <el-form-item style="margin-left:165px;" label="会员合并">
                    <el-radio-group style="margin-top:5px;" v-model="merge.allMergeRadio">
                        <el-radio :label="1">全自动合并</el-radio>
                        <el-radio :label="0">新会员自动合并</el-radio>
                    </el-radio-group>
                    <div class="select_text_box">
                        <p>新会员自动合并：合并规则上，如果被合并的会员是新注册的会员，则自动合并；如果不是，在提示会员数据异常，请联系客服处理，由后台选择需要保留的会员ID。</p>
                        <p>全自动合并：按照会员保留优先级保留会员，其他会员登录信息自动同步到对应的会员ID上，除保留的会员登录凭证外，其他被同步的会员数据 （订单、积分、余额、下级、佣金等）全部无法合并。</p>
                    </div>
                </el-form-item>
                <el-form-item style="margin-left: 122px;" label="会员保留优先级">
                    <el-radio-group style="margin-top:5px;" v-model="merge.priority">
                        <el-radio :label="0">注册时间</el-radio>
                        <el-radio :label="1">手机号</el-radio>
                        <el-radio :label="2">公众号</el-radio>
                        <el-radio :label="3">微信小程序</el-radio>
                        <el-radio :label="4">APP微信登录</el-radio>
                        <el-radio :label="5">支付宝登录</el-radio>
                    </el-radio-group>
                    <div class="select_text_box" style="margin-left:109px;">
                        <p>会员合并时，保留所选优先级对应的会员ID，其他的会员登录平台将合并到该会员ID上；</p>
                        <p> 如果不存在优先级设置的会员，则按注册时间优先合并。</p>
                    </div>
                </el-form-item>
            </el-form>
        </div>
        <div class="fixed total-floo">
            <div class="fixed_box">
                <el-form>
                    <el-form-item>
                        <el-button type="primary" @click="submit">提交</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
        <el-dialog width="60%" :visible.sync="showProgress" center :close-on-click-modal="false">
            <h3 style="">清除旧数据进度,请勿刷新当前页面</h3>
            <el-progress :percentage="del_process" ></el-progress>
            <h3 style="">同步新数据进度,请勿刷新当前页面</h3>
            <el-progress :percentage="process" ></el-progress>
        </el-dialog>
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
    </div>
    <script>
        const getGoods = "{!! yzWebFullUrl('goods.goods.get-search-goods-json') !!}";
        const vm = new Vue({
            el: "#app",
            name: "blade",
            // 防止后端冲突,修改ma语法符号
            delimiters: ['[[', ']]'],
            data() {
                return {
                    tableData:[],
                    show:false,
                    keyword:"",
                    total:0,
                    page:0,
                    per_page:0,
                    info: [],
                    // 上传图片
                    showSelectMaterialPopup: false,
                    materialType: "",
                    formFieldName: "",
                    goodsIdList: "",
                    //当前图片状态
                    image: "",
                    showimg: false,
                    index: null,
                    isSelNum: "one",
                    isUpLevel: true,
                    putList: [],
                    clientList: [],
                    putVal: "",
                    clientVal: "",
                    isPut: false,
                    isClient: false,
                    showProgress: false,
                    del_process: 0,
                    process: 0,
                    //微信模板
                    weChatTemp: "",
                    //会员关系
                    relation: {
                        refe: "1",
                        refe_boss: "1",
                        refe_phone: "1",
                        refe_checkList: [],
                        oneEvel: "",
                        twoEvel: "",
                        nameInfo: [],
                        is_statistical_goods:"0",
                        statistical_goods:[]
                    },
                    //会员合并
                    merge: {
                        allMergeRadio: 0,
                        priority: 0
                    },
                    //显示的状态
                    isPut_istop_tool: false,
                    //显示的文字
                    isPut_title_tool: "",
                    //显示的状态
                    isClient_istop_tool: false,
                    //显示的文字
                    isClient_title_tool: "",
                    //保存多选数据
                    checkboxLevel: [],
                    //开启微信多选数据
                    checkboxBtn: []
                }
            },
            created() {
                //优化在不同设备固定定位挡住的现象设置父元素的内边距
                window.onload = function() {
                    let all = document.querySelector(".all");
                    let h = window.innerHeight * 0.03;
                    all.style.paddingBottom = h + "px";
                }
                this.postRelation();
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
                showGoodsList() {
                    if (!this.tableData || this.tableData.length <= 0) this.getGoods();
                    this.show = true;
                },
                getGoods() {
                    let loading = this.loading("正在获取数据中...");
                    this.$http.post(getGoods, {keyword: this.keyword, page: this.page}).then(({data: {result,msg,data: {goods}}}) => {
                        loading.close();
                        if (result == 1) {
                            this.tableData = goods.data;
                            this.total = goods.total;
                            this.per_page = goods.per_page;
                        } else this.$message.error(msg);
                    })
                },
                queryGoods(row){
                    let statistical_goods = this.relation.statistical_goods;
                    if(!statistical_goods) return false;
                    for(let i = 0;i<statistical_goods.length;i++){
                        if(statistical_goods[i].id == row.id) return true;
                    }
                    return false;
                },
                handleClick(row){
                    if(this.queryGoods(row)) return this.$message.error("已经选择此商品");
                    if(!this.relation.statistical_goods) this.relation.statistical_goods = [];
                    if ( this.relation.statistical_goods.length >=2) return this.$message.error("最多只能选择两件商品");
                    this.relation.statistical_goods.push(row);
                },
                //请求回显网络数据
                postRelation() {
                    this.$http.post("{!!yzWebFullUrl('member.member-relation.relation-base')!!}", {}).then(res => {
                        console.log(res.body.data);
                        let {
                            banner,
                            temp_list: list,
                            member_relation,
                            member_merge,
                            notice
                        } = res.body.data;
                        //banner 选择图片
                        if (banner!==null && banner) {
                            //显示图片
                            this.showimg = true;
                            this.image = banner
                        }
                        if(list!==null && list){
                        //获得推广权限通知列表
                        list.unshift({
                            id: 0,
                            title: '默认消息模板'
                        })
                        this.putList = list;
                        this.clientList = list;
                        }
                        if(member_relation!==null){
                            //会员关系显示推荐人 is_referrer
                            this.relation.refe = member_relation.is_referrer;
                            //会员关系显示推荐人上级 parent_is_referrer
                            this.relation.refe_boss =member_relation.parent_is_referrer;
                            // console.log(this.relation.refe_boss,4545153);
                            // 推荐人电话微信号 is_recommend_wechat
                            this.relation.refe_phone = member_relation.is_recommend_wechat;
                        //  console.log(base.relation_level);
                        //一级关系等级保存多选
                        if (member_relation.one_level == 1 ) {
                            this.info.push('1')
                        }
                        if (member_relation.two_level == 2) {
                            this.info.push('2')
                        }
                        if (member_relation.phone == 1) {
                            this.relation.nameInfo.push('phone')
                        }
                        if (member_relation.realname == 1) {
                            this.relation.nameInfo.push('realname')
                        }
                        if (member_relation.wechat == 1) {
                            this.relation.nameInfo.push('wechat')
                        }
                        //一级关系等级自定义
                        this.relation.oneEvel = member_relation.name1
                        //二级关系等级自定义
                        this.relation.twoEvel =member_relation.name2

                        this.relation.statistical_goods = member_relation.statistical_goods;
                        this.relation.is_statistical_goods = member_relation.is_statistical_goods?member_relation.is_statistical_goods:"0";
                        }
                        if(notice!==null && notice){
                        if(notice.member_agent) {
                            this.postRelationID(notice.member_agent, 1)
                        }else {
                            this.putVal = 0;
                        }
                        if(notice.member_new_lower) {
                            this.postRelationID(notice.member_new_lower, 2)
                        }else {
                            this.clientVal = 0;
                        }
                     }
                        //全自动合并 is_member_merge
                        if(member_merge!==null && member_merge){
                            this.merge.allMergeRadio = Number(member_merge.is_member_merge);
                            //会员保留优先级 is_merge_save_level
                            this.merge.priority = Number(member_merge.is_merge_save_level);
                        }

                    })
                },
                //上传图片
                uploadPictures(fieldName, type, i) {
                    this.formFieldName = fieldName;
                    this.showSelectMaterialPopup = !this.showSelectMaterialPopup;
                    this.materialType = String(type);
                    this.index = i;
                },
                selectedMaterial(name, image, imageUrl) {
                    this.showimg = image;
                    //判断是否为点击添加
                    if (this.index == '2') {
                        this.image = imageUrl[0].url;
                        this.goodsIdList = imageUrl[0].id;
                    } else {
                        // console.log(11);
                        this.image = imageUrl[0].url;
                    }
                },
                //点击删除当前图片
                DeletePictures() {
                    //删除当前的图片
                    this.image = "";
                    this.showimg = false;
                },
                //请求id 是否开启默认模板
                isOpenWeChatTemp(isVal, id) {
                    // console.log(isVal, id)
                    var postdata ={
                        notice_name: '',
                        setting_name: "relation_base"
                    }
                    var url = "";

                    if (id === 1) {
                        //推广客户
                        postdata.notice_name = 'member_agent';
                    }else if (id === 2) {
                        //新增客户
                        postdata.notice_name = 'member_new_lower';
                    }

                    if(!isVal) {
                        url = "{!!yzWebFullUrl('setting.default-notice.cancel')!!}";
                    }else {
                        url = "{!!yzWebFullUrl('setting.default-notice.index')!!}";
                    }

                    this.$http.post(url, postdata).then(res => {
                        // console.log(res)
                        if (id === 1) {
                            if(!this.isPut) {
                                this.isPut_istop_tool = true;
                                this.isPut_title_tool = "关闭成功";
                                setTimeout(() => {
                                    this.isPut_istop_tool = false;
                                }, 2000)
                            }else {
                                this.isPut_istop_tool = true;
                                if(res.body.result == 1) {
                                    this.isPut_title_tool = "开启成功";
                                    this.isPut = true;
                                    setTimeout(() => {
                                        this.isPut_istop_tool = false;
                                    }, 2000)
                                }else {
                                    this.isPut_title_tool = "开启失败,请检查微信模板";
                                    this.isPut = false;
                                    setTimeout(() => {
                                        this.isPut_istop_tool = false;
                                    }, 2000)
                                }
                            }
                        }else {
                            if(!this.isClient) {
                                this.isClient_istop_tool = true;
                                this.isClient_title_tool = "关闭成功";
                                setTimeout(() => {
                                    this.isClient_istop_tool = false;
                                }, 2000)
                            }else {
                                this.isClient_istop_tool = true;
                                if(res.body.result == 1) {
                                    this.isClient_title_tool = "开启成功";
                                    this.isClient = true;
                                    setTimeout(() => {
                                        this.isClient_istop_tool = false;
                                    }, 2000)
                                }else {
                                    this.isClient_title_tool = "开启失败,请检查微信模板";
                                    this.isClient = false;
                                    setTimeout(() => {
                                        this.isClient_istop_tool = false;
                                    }, 2000)
                                }
                            }
                        }
                    })
                },

                //检查是否开启默认模板
                postRelationID(id, i) {
                    this.$http.post("{!!yzWebFullUrl('member.member-relation.get-is-default-by-id')!!}", {
                        id: id
                    }).then(res => {
                        // console.log(res)
                        if(res.body.result != 1) {
                            // result=0的情况
                            if (i == 1) {
                                this.isPut = false;
                                this.putVal = Number(id) || 0;
                            } else {
                                this.isClient = false;
                                this.clientVal = Number(id) || 0;
                            }
                            return;
                        }else {
                            if (i == 1) {
                                this.isPut = res.body.data.data;
                                if(this.isPut) {
                                    this.putList.map((item=> {
                                        if(item.title == '默认消息模板') {
                                            item.id = Number(id)
                                        }
                                    }))
                                    this.putList.reverse().reverse();
                                }
                                this.putVal = Number(id);
                            } else {
                                this.isClient = res.body.data.data;
                                if(this.isClient) {
                                    this.clientList.map((item=> {
                                        if(item.title == '默认消息模板') {
                                            item.id = Number(id)
                                        }
                                    }))
                                    this.clientList.reverse().reverse();
                                }
                                this.clientVal = Number(id);
                            }
                        }

                    })
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

                    });
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
                //提交保存
                submit() {
                    let statistical_goods = this.relation.statistical_goods;
                    if(statistical_goods&&statistical_goods.length>0){
                        var goods = {statistical_goods}
                    }
                    this.$http.post("{!!yzWebFullUrl('member.member-relation.relation-base')!!}", {
                        base: {
                            ...goods,
                            is_statistical_goods:this.relation.is_statistical_goods,
                            banner: this.image,
                            member_agent: this.putVal,
                            member_new_lower: this.clientVal,
                            is_referrer: this.relation.refe,
                            parent_is_referrer: this.relation.refe_boss,
                            is_recommend_wechat: this.relation.refe_phone,
                            relation_level: {
                                "0": this.info.indexOf('1') > -1 ? 1 : 0,
                                "1": this.info.indexOf('2') > -1 ? 2 : 0,
                                "name1": this.relation.oneEvel,
                                "name2": this.relation.twoEvel,
                                "wechat": this.relation.nameInfo.indexOf('wechat') > -1 ? 1 : 0,
                                "phone": this.relation.nameInfo.indexOf('phone') > -1 ? 1 : 0,
                                "realname": this.relation.nameInfo.indexOf('realname') > -1 ? 1 : 0,
                            },
                            is_member_merge: this.merge.allMergeRadio,
                            is_merge_save_level: this.merge.priority
                        }
                    }).then((res) => {
                        console.log(res, 123);
                        if (res.data.result === 1) {
                            this.$message.success(res.data.msg)
                            //成功刷新页面
                            history.go(0);
                        } else {
                            this.$message.error(res.data.msg)
                        }
                    })
                }
            }
        })
    </script>@endsection