@extends('layouts.base')
@section('title', '满件优惠设置')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        /*上传图片 */
        .addImg-box {
            /*width: 700px;*/
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
            /*height: 150px;*/
            width: 150px;
            margin-right: 20px;
            margin-bottom: 100px;
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
    </style>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-nav" style="margin-bottom:15px">
                <el-tabs v-model="active_name" @tab-click="handleClick">
                    <el-tab-pane label="满额优惠设置" name="1"></el-tab-pane>
                    <el-tab-pane label="包邮分类设置" name="2"></el-tab-pane>
                    <el-tab-pane label="满件优惠设置" name="3"></el-tab-pane>
                </el-tabs>
            </div>

            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">满件优惠设置</div>
                </div>
                <div class="vue-main-form">
                    <el-form ref="form" :rules="rules" :model="form" label-width="17%">
                        <el-form-item label="开启满件优惠">
                            <el-radio v-model.bool="form.open" :label=true>开启</el-radio>
                            <el-radio v-model.bool="form.open" :label=false>关闭</el-radio>
                        </el-form-item>

                        <el-form-item label="">
                                <el-button @click="add">增加满件优惠</el-button>
                        </el-form-item>

                        <template v-for="(fullPiece,index) in form.fullPiece">
                            <el-form-item label="优惠类型">
                                <el-radio v-model.bool="fullPiece.discount_type" :label="0">立减</el-radio>
                                <el-radio v-model.bool="fullPiece.discount_type" :label="1">打折</el-radio>
                                <el-button class="btn btn-danger" @click="del(index)">删除优惠</el-button>
                            </el-form-item>

                            <template v-for="(rule,rule_index) in fullPiece.rules">
                                <el-form-item label="满件减">
                                    <el-form-item>
                                        <el-row :gutter="3">
                                            <el-col :span="6">
                                                <el-form-item v-bind:prop="index+'-'+rule_index">
                                                    <el-input placeholder="商品数量"
                                                              v-model.number="rule.enough" size="medium">
                                                        <template slot="prepend">满</template>
                                                        <template slot="append">件</template>
                                                    </el-input>
                                                </el-form-item>
                                            </el-col>

                                            <el-col :span="6" v-if="fullPiece.discount_type == 0">
                                                <el-form-item v-bind:prop="index+'-'+rule_index">
                                                    <el-input @input="limitInput(index,rule_index, rule.reduce)" placeholder="金额"
                                                              v-model="rule.reduce" size="medium">
                                                        <template slot="prepend">减</template>
                                                        <template slot="append">元</template>
                                                    </el-input>
                                                </el-form-item>
                                            </el-col>

                                            <el-col :span="6" v-if="fullPiece.discount_type == 1">
                                                <el-form-item v-bind:prop="index+'-'+rule_index">
                                                    <el-input @input="limitInput(index,rule_index, rule.reduce)" placeholder="折扣"
                                                              v-model="rule.reduce" size="medium">
                                                        <template slot="prepend">打</template>
                                                        <template slot="append">折</template>
                                                    </el-input>
                                                </el-form-item>
                                            </el-col>

                                            <el-col :span="3">
                                                <el-button plain size="mini" @click="remove(index,rule_index)">x</el-button>
                                            </el-col>
                                        </el-row>
                                    </el-form-item>
                                </el-form-item>
                            </template>

                            <el-form-item label="">
                                <el-row>
                                    <el-button @click="addRule(index)">增加满件规则</el-button>
                                </el-row>
                            </el-form-item>

                            <el-form-item label="满件减商品" prop="">
                                <!-- 上传商品 -->
                                <div class="addImg-box">
                                    <div class="addImg-list" v-for="(tag,goods_index) in fullPiece.goods" :key="goods_index" >
                                        <div class="itemImg_box">
                                            <img style="width:150px;height:150px" :src="tag.thumb" alt="">
                                            <div class="middenTitle" style="line-height: 22px;height:66px;overflow: hidden;text-overflow: ellipsis;">
                                                【ID:[[ tag.id ]]】[[ tag.title ]]
                                            </div>
                                        </div>
                                        <div class="cancel-box" @click.stop="clearGoods(index,goods_index)"><i class="cancel">×</i></div>
                                        {{--<div class="anew">点击重新上传</div>--}}
                                    </div>
                                    <div @click.stop="openGoods(index)" class="add-price-img" style="margin-bottom: 100px">
                                        <i class="el-icon-plus"></i>
                                        <div class="select-price">选择商品</div>
                                    </div>
                                </div>
                            </el-form-item>
                        </template>

                    </el-form>
                </div>
            </div>

            <el-dialog width="55%" center title="选择商品" :visible.sync="goods_show">
                <el-input clearable v-model.trim="goods_keyword" placeholder="搜索id和商品名称" style="width:80%;margin-right:25px"></el-input>
                <el-button @click="getGoods" @keyup.enter="getGoods(1)" type="primary">搜索</el-button>
                <el-table ref="singleTable" v-loading="loading" height="250" style=" width: 100%;" :data="goods_list">
                    <el-table-column header-align="center" property="id" label="ID"></el-table-column>
                    <el-table-column header-align="center" label="商品信息">
                        <template slot-scope="scope">
                            <img v-if="scope.row.thumb!==null && scope.row.thumb" style="width:30px;height:30px;margin-right:5px" :src="scope.row.thumb" alt=""><span>[[scope.row.title]]</span>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" align="center">
                        <template slot-scope="scope">
                            <el-button @click="chooseGoods(scope.row)">选择</el-button>
                        </template>
                    </el-table-column>
                </el-table>
                <div style="margin-top:50px">
                    <el-pagination layout="prev, pager, next,jumper" background style="text-align:center" :page-size="pagesize" :current-page="currentPage" :total="total" @current-change="handleCurrentChange">
                    </el-pagination>
                </div>
            </el-dialog>

            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click.native.prevent="onSubmit" v-loading="formLoading">提交</el-button>
                    <el-button @click="goBack()">返回</el-button>
                </div>
            </div>
        </div>
    </div>
    <div id="test-vue">
    </div>
    <script>
        var app = new Vue({
            el: '#app',
            delimiters: ['[[', ']]'],
            data() {
                // 默认数据
                let temp = {!! $setting !!};
                if (!temp || temp.length === 0) {
                    temp = {
                        open:false,
                        fullPiece:[{'discount_type':0,'enough': '','reduce': '','rules':[{'enough': '','reduce': ''}],'goods':[]}],
                    }
                }
                //验证规则
                let amountRules = {
                    type: 'number',
                    min: 0,
                    max: 999999999,
                    message: '请输入正确金额',
                    transform(value) {
                        console.log(value);
                        return Number(value)
                    }
                };
                let rules = {
                    'freeFreight.enough': [amountRules],
                };
                return {
                    form: temp,
                    props: {
                        label: 'areaname',
                        children: 'children',
                        isLeaf: 'isLeaf'
                    },
                    goods_show:false,
                    goods_keyword:"",
                    goods_list:[],
                    pagesize:1,
                    currentPage:1,
                    total:1,
                    per_page:1,
                    loading: false,
                    formLoading: false,
                    centerDialogVisible: false,
                    treeData: [],
                    rules: rules,
                    active_name: '3',
                    goods_index:null,
                }
            },
            mounted: function () {
                console.log(this.form)
            },
            methods: {
                limitInput(index,rule_index, value) {
                    if (!this.form.fullPiece[index] || !this.form.fullPiece[index].rules[rule_index]) {
                        return '';
                    }


                    this.form.fullPiece[index].rules[rule_index].reduce = ("" + value).match(/^\d*(\.?\d{0,2})/g)[0] || "";
                },

                add() {
                    this.form.fullPiece.push({'discount_type':0,'enough': '','reduce': '','rules':[{'enough': '','reduce': ''}],'goods':[]});
                },
                del(index) {
                    this.form.fullPiece.splice(index,1);
                    this.$forceUpdate();
                },
                addRule(fullPiece_index) {
                    this.form.fullPiece[fullPiece_index].rules.push({'enough': '','reduce': ''});
                },
                remove(full_index,itemIndex) {
                    this.form.fullPiece[full_index].rules.splice(itemIndex, 1)
                },
                onSubmit() {

                    if (this.formLoading) {
                        return;
                    }
                    this.formLoading = true;

                    this.$refs.form.validate((valid) => {
                        console.log(valid)
                    });
                    this.$http.post("{!! yzWebUrl('enoughReduce.full-piece.store') !!}", {'setting': this.form}).then(response => {
                        //console.log(response.data);
                        // return;
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                        this.formLoading = false;
                    }, response => {
                        console.log(response);
                    });
                },
                handleClick(val) {
                    if (val.name == 1) {
                        window.location.href = `{!! yzWebFullUrl('enoughReduce.index.index') !!}`;
                    } else if (val.name == 2) {
                        window.location.href = `{!! yzWebFullUrl('enoughReduce.postage-included-category.index') !!}`;
                    } else if (val.name == 3) {
                        window.location.href = `{!! yzWebFullUrl('enoughReduce.full-piece.index') !!}`;
                    }
                },
                openGoods(index) {
                    this.goods_show = true;
                    this.goods_index = index;
                },
                getGoods(page){
                    this.$http.post("{!! yzWebUrl('enoughReduce.full-piece.get-goods') !!}",{keyword:this.goods_keyword,page:page}).then(response => {
                        if (response.data.result) {
                            this.goods_list = response.data.data.data;
                            this.current_page=response.data.data.current_page;
                            this.total=response.data.data.total;
                            this.pagesize=response.data.data.per_page;
                        }else{
                            this.$message({type: 'error',message: response.data.msg});
                        }
                    }, response => {
                        this.$message({type: 'error',message: response.data.msg});
                        console.log(response);
                    });
                },
                //点击切换分页
                handleCurrentChange(page) {
                    this.getGoods(page);
                },
                chooseGoods(row) {
                    let that = this;
                    let has = false;
                    if (!this.form.fullPiece[this.goods_index]) {
                        return;
                    }
                    this.form.fullPiece.forEach((fullPiece, index) => {
                        fullPiece.goods.forEach((item, goods_index) => {
                            if(row.id == item.id) {
                                has = true;
                            }
                        });
                    });
                    if (has) {
                        this.$message.error("该商品已选择，请勿重复选择！");
                        return;
                    }
                    this.form.fullPiece[this.goods_index].goods.push(row);
                },
                clearGoods(goods_index,index) {
                    // console.log(goods_index,index)
                    this.form.fullPiece[goods_index].goods.splice(index,1);
                    this.$forceUpdate();
                },
            }
        });
    </script>
@endsection

