@extends('layouts.base')
@section('title', '满额优惠设置')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>

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
                    <div class="vue-main-title-content">满额优惠设置</div>
                </div>
                <div class="vue-main-form">
                    <el-form ref="form" :rules="rules" :model="form" label-width="17%">
                        <el-form-item label="开启满额优惠">
                            <el-radio v-model.bool="form.open" :label=true>开启</el-radio>
                            <el-radio v-model.bool="form.open" :label=false>关闭</el-radio>
                        </el-form-item>
                        <template v-for="(enoughReduce,index) in form.enoughReduce">

                            <el-form-item label="满额减">

                                <el-form-item>

                                    <el-row :gutter="3">

                                        <el-col :span="6">
                                            <el-form-item v-bind:prop="'enoughReduce.enough-'+index">
                                                <el-input placeholder="金额"
                                                          v-model.number="enoughReduce.enough" size="medium">
                                                    <template slot="prepend">满</template>
                                                    <template slot="append">元</template>
                                                </el-input>
                                            </el-form-item>
                                        </el-col>

                                        <el-col :span="6">

                                            <el-form-item v-bind:prop="'enoughReduce.reduce-'+index">
                                                <el-input placeholder="金额"
                                                          v-model.number="enoughReduce.reduce" size="medium">
                                                    <template slot="prepend">减</template>
                                                    <template slot="append">元</template>
                                                </el-input>
                                            </el-form-item>
                                        </el-col>

                                        <el-col :span="3">
                                            <el-button plain size="mini" @click="remove(index)">x</el-button>
                                        </el-col>
                                    </el-row>
                                </el-form-item>


                            </el-form-item>
                        </template>
                        <el-form-item label="">
                            <el-row>
                                <el-button @click="add">增加满减规则</el-button>
                            </el-row>
                        </el-form-item>

                        <el-form-item label="订单满额包邮">

                            <el-radio v-model.bool="form.freeFreight.open" :label=true>开启</el-radio>
                            <el-radio v-model.bool="form.freeFreight.open" :label=false>关闭</el-radio>
                            <div>订单总金额超过多少可以包邮,与商品单品满额包邮互不影响</div>
                            <el-form-item prop="freeFreight.enough">
                                <el-input placeholder="金额"
                                          v-model.number="form.freeFreight.enough" size="medium"
                                          style="width: 27%">
                                    <template slot="prepend">满</template>
                                    <template slot="append">元包邮</template>
                                </el-input>
                            </el-form-item>
                        </el-form-item>
                        <el-form-item label="订单满额包邮计算金额">
                            <el-radio v-model.bool="form.freeFreight.amount_type" :label=0>订单折扣后价格</el-radio>
                            <el-radio v-model.bool="form.freeFreight.amount_type" :label=1>订单抵扣后价格</el-radio>
                            <p style="color: RGB(169, 169, 169);">折扣后价格(包含商品满减，会员折扣等)；抵扣后价格(积分抵扣，余额抵扣等)。使用订单抵扣后价格, 如果开启积分抵扣运费等抵扣运费方式，满额包邮无效</p>
                        </el-form-item>
                        <el-form-item label="不参与地区">
                            <el-row>
                                <el-tag
                                        v-for="city in form.freeFreight.cities"
                                        :key="city">
                                    [[city]]
                                </el-tag>
                            </el-row>

                            <el-button @click="centerDialogVisible = true">编辑不参加包邮地区</el-button>
                            <el-dialog
                                    title="请选择地区"
                                    :visible.sync="centerDialogVisible"
                                    center>
                                <el-tree
                                        v-loading="loading"
                                        :props="props"
                                        node-key="id"
                                        :default-checked-keys="form.freeFreight.city_ids"
                                        :default-expanded-keys="form.freeFreight.province_ids"
                                        show-checkbox
                                        lazy
                                        accordion
                                        @check-change="checkAreas"
                                        ref="addressTree"
                                        :data="treeData"
                                        :load="loadNode">
                                </el-tree>

                                <span slot="footer" class="dialog-footer">
                            <el-button @click="centerDialogVisible = false">取 消</el-button>
                            <el-button type="primary" @click="saveAreas">确 定</el-button>
                        </span>

                            </el-dialog>
                        </el-form-item>

                        <el-form-item label="订单包邮商品推荐">
                            <el-radio-group v-model="form.freeFreight.postage_included_category_open" style="padding: 10px;">
                                <el-radio :label="1">开启</el-radio>
                                <el-radio :label="0">关闭</el-radio>
                            </el-radio-group>
                            <p style="color: RGB(169, 169, 169);">开启后, 如果未达到包邮条件, 则在预下单页显示满额包邮商品推荐 (开启后需到包邮分类页面进行分类设置)</p>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
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
                let temp = JSON.parse('{!! $setting !!}');
                if (!temp || temp.length === 0) {
                    temp = {
                        enoughReduce: [],
                        freeFreight: {
                            'open': false,
                            'amount_type': 0,
                            'enough': 0,
                            'cities': [],
                            'city_ids': [],
                            'province_ids': [],
                            postage_included_category_open: 0
                        },
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
//                 for(enoughReduceIndex in temp.enoughReduce){
//                     rules['enoughReduce.reduce-'+enoughReduceIndex] = [amountRules];
//                     rules['enoughReduce.enough-'+enoughReduceIndex] = [amountRules];
//                 }
// console.log(rules);
                return {
                    form: temp,
                    props: {
                        label: 'areaname',
                        children: 'children',
                        isLeaf: 'isLeaf'
                    },
                    loading: false,
                    formLoading: false,
                    centerDialogVisible: false,
                    treeData: [],
                    rules: rules,
                    active_name: '1'
                }
            },
            mounted: function () {
                console.log(this.form)
            },
            methods: {
                add() {
                    this.form.enoughReduce.push(
                        {
                            'enough': '',
                            'reduce': ''
                        }
                    )
                },
                remove(itemIndex) {
                    // let i = this.form.enoughReduce.indexOf(item)
                    // console.log(this.form.enoughReduce,i,item);
                    this.form.enoughReduce.splice(itemIndex, 1)
                },
                onSubmit() {
                    if (this.formLoading) {
                        return;
                    }
                    this.formLoading = true;

                    this.$refs.form.validate((valid) => {
                        console.log(valid)
                    });
                    this.$http.post("{!! yzWebUrl('enoughReduce.store') !!}", {'setting': this.form}).then(response => {
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
                handleClose(area) {
                    this.form.areas.splice(this.form.areas.indexOf(area), 1);
                },
                loadNode(node, resolve) {
                    this.loading = true;
                    if (!node.data.id) {
                        //省份
                        node.data.id = 0;
                        this.$http.get("{!! yzWebUrl('area.list', ['parent_id'=> 0]) !!}").then(response => {
                            response.data.data.forEach(function (province) {
                                province.isLeaf = false;
                            });
                            resolve(response.data.data);

                            this.loading = false;
                        }, response => {
                            console.log(response);
                        });
                    } else {
                        //城市
                        this.$http.get("{!! yzWebUrl('area.list', ['parent_id'=> '']) !!}" + node.data.id).then(response => {
                            //城市没有子节点
                            response.data.data.forEach(function (city) {
                                city.isLeaf = true;
                            })
                            resolve(response.data.data);
                            // 载入数据后,刷新已选中
                            this.loading = false;
                        }, response => {
                            console.log(response);
                        });
                    }
                },
                checkAreas(node, checked, children) {
                    if (node.isLeaf) {
                        return;
                    }
                    if (checked) {
                        this.form.freeFreight.province_ids.push(node.id)
                    }
                    console.log(node, checked, children, this.form, 123);
                },
                saveAreas() {
                    let cities = [];
                    let city_ids = [];
                    let province_ids = [];
                    this.$refs.addressTree.getCheckedNodes().forEach(function (node) {
                        console.log(node, 'node');
                        if (node.level == 1) {
                            province_ids.push(node.id);
                        } else if (node.level == 2) {
                            city_ids.push(node.id);
                            cities.push(node.areaname)
                        }
                    });
                    this.form.freeFreight.city_ids = city_ids;
                    this.form.freeFreight.cities = cities;
                    this.form.freeFreight.province_ids = province_ids;
                    this.centerDialogVisible = false
                    console.log(this.form, 'this.form');
                },
                handleClick(val) {
                    if (val.name == 1) {
                        window.location.href = `{!! yzWebFullUrl('enoughReduce.index.index') !!}`;
                    } else if (val.name == 2) {
                        window.location.href = `{!! yzWebFullUrl('enoughReduce.postage-included-category.index') !!}`;
                    } else if (val.name == 3) {
                        window.location.href = `{!! yzWebFullUrl('enoughReduce.full-piece.index') !!}`;
                    }
                }
            }
        });
    </script>
@endsection

