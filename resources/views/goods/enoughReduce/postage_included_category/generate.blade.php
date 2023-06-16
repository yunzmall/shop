@extends('layouts.base')
@section('title', trans('包邮分类设置'))
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>

    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-nav" style="margin-bottom:15px">
                <el-tabs v-model="active_name" @tab-click="handleClick">
                    <el-tab-pane label="满额优惠设置" name="1"></el-tab-pane>
                    <el-tab-pane label="包邮分类设置" name="2"></el-tab-pane>
                </el-tabs>
            </div>

            <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                <div class="vue-main">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">包邮分类编辑</div>
                        <el-button type="primary" class="createCategory" @click="historyClick">返回包邮分类列表</el-button>
                    </div>

                    <div class="vue-main-form">
                        <el-form-item label="排序" prop="sort">
                            <el-input type="age" maxlength="15" v-model.number="form.sort" clearable
                                      style="width:50%"></el-input>
                        </el-form-item>

                        <el-form-item label="分类名称" prop="name">
                            <el-input type="text" maxlength="15" v-model="form.name" clearable
                                      style="width:50%"></el-input>
                        </el-form-item>

                        <el-form-item label="是否显示" prop="delivery">
                            <el-switch v-model.number="form.is_display" :active-value="1"
                                       :inactive-value="0"></el-switch>
                        </el-form-item>

                        <el-form-item label="新增分类商品" prop="goods">
                            <div class="goodsAdd">
                                <el-button style="width:100%;height:100%;" icon="el-icon-plus" @click="addGoods">
                                    <p style="margin: 5px 0px 0px -5px;font-size: 2px;">点击添加</p>
                                </el-button>
                            </div>
                            <div class="goodsAdd" v-for="(goods, index) in goodsData">
                                <img :src="goods.thumb"
                                     style="height: 100%;width: 100%;"
                                />
                                <i class="el-icon-close del" @click="goodsDel(index, goods.id)"></i>
                                <p style="font-size: 8px; margin-bottom: 0px;">[[ titleFormatLimit(goods.title, 5)
                                    ]]</p>
                                <p style="font-size: 8px; margin-top: -20px;">商品ID: [[ goods.id ]]</p>
                            </div>

                            <el-dialog title="选择商品" :visible.sync="selectMemberVisible">
                                <el-input v-model="form.keyword" placeholder="商品标题" clearable
                                          style="width: 40%; margin-bottom: 30px;"></el-input>
                                <el-button plain @click="searchGoods">搜索商品</el-button>
                                <el-table :data="searchData.data">
                                    <el-table-column property="id" label="商品ID" width="80"></el-table-column>
                                    <el-table-column property="title" label="商品标题"
                                                     :formatter="titleFormat"></el-table-column>
                                    <el-table-column property="thumb" label="商品图片">
                                        <template slot-scope="scope">
                                            <img :src="scope.row.thumb" min-width="40" height="40"/>
                                        </template>
                                    </el-table-column>
                                    <el-table-column label="操作">
                                        <template slot-scope="scope">
                                            <el-button type="text"
                                                       @click.native="insertGoods(scope.$index, scope.row.id)">添加
                                            </el-button>
                                        </template>
                                    </el-table-column>
                                </el-table>
                                <el-pagination style="margin-right: 50px"
                                               background align="right"
                                               layout="prev, pager, next"
                                               :total="searchData.total"
                                               :page-size="searchData.per_page"
                                               :page-count="searchData.last_page"
                                               :current-page="searchData.current_page"
                                               @prev-click="handlePrevPage"
                                               @next-click="handleNextPage"
                                               @current-change="handleCurrentPage">
                                </el-pagination>
                            </el-dialog>
                        </el-form-item>

                        <el-form-item label="已添加商品" prop="goods" v-if="updatePage">
                            <div class="goodsAdd" v-for="(goods, index) in recordGoods.data">
                                <img :src="goods.thumb"
                                     style="height: 100%;width: 100%;"
                                />
                                <i class="el-icon-close del" @click="goodsDestroy(form.id, goods.id, index)"></i>
                                <p style="font-size: 8px; margin-bottom: 0px;">[[ titleFormatLimit(goods.title, 5)
                                    ]]</p>
                                <p style="font-size: 8px; margin-top: -20px;">商品ID: [[ goods.id ]]</p>
                            </div>
                            <div v-if="loadingDisplay">
                                <el-button style="height: 100%;width: 100%;" @click="loadGoods" >
                                    <i class="el-icon-loading"></i>
                                    <span>加载更多商品</span>
                                </el-button>
                            </div>
                        </el-form-item>
                    </div>
                </div>

                <div class="vue-page">
                    <div class="vue-center">
                        <el-button type="primary" @click="submitForm('form')">提交</el-button>
                    </div>
                </div>
            </el-form>
        </div>
    </div>

    <script>
        let app = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    updatePage: false,
                    form: {
                        sort: '',
                        name: '',
                        is_display: 0,
                        keyword: '',
                    },
                    rules: {
                        sort: [
                            {required: true, message: '请输入排序数', trigger: 'blur'},
                            {type: 'number', message: '排序必须为整数'}
                        ],
                        name: [
                            {required: true, message: '请输入分类名称', trigger: 'blur'},
                        ]
                    },
                    selectMemberVisible: false,
                    keyword: '',
                    goodsData: [],
                    searchData: [],
                    addGoodsIds: [],
                    recordGoods: [],
                    loadingDisplay: false,
                    active_name: '2'
                }
            },
            mounted() {
                let id = this.getQueryVariable('id')
                if (id) {
                    this.updatePage = true
                    this.getCategory(id)
                }
            },
            methods: {
                createMarketingQr() {
                    // 跳转->生成营销码页面
                    window.location.href = `{!! yzWebFullUrl('plugin.marketing-qr.backend.category.generate') !!}`
                },
                historyClick() {
                    history.back()
                },
                submitForm(form) {
                    this.$refs[form].validate((valid) => {
                        if (valid) {
                            let params = {
                                add_goods_ids: this.addGoodsIds,
                                sort: this.form.sort,
                                name: this.form.name,
                                is_display: this.form.is_display
                            }
                            let url = `{!! yzWebUrl('enoughReduce.postage-included-category.generation') !!}`
                            if (this.form.id) {
                                url = `{!! yzWebUrl('enoughReduce.postage-included-category.update') !!}`
                                params['id'] = this.form.id
                            }
                            const index_url = `{!! yzWebUrl('enoughReduce.postage-included-category.index') !!}`
                            this.$http.post(url, params).then(response => {
                                if (response.data.result === 1) {
                                    this.$message.success(response.data.msg)
                                    window.location.href = index_url
                                } else {
                                    this.$message.error('操作失败, 请重试')
                                }
                            }).catch(err => {
                                console.log(err)
                            });
                        } else {
                            this.$message({
                                message: '请检查输入参数',
                                type: 'error'
                            });
                            return false;
                        }
                    });

                },
                addGoods() {
                    this.selectMemberVisible = true
                },
                searchGoods(page) {
                    const that = this
                    const params = {
                        params: {
                            keyword: this.form.keyword,
                            page: page,
                        }
                    }
                    const url = `{!! yzWebUrl('enoughReduce.postage-included-category.search-goods') !!}`
                    this.$http.get(url, params).then(response => {
                        if (response.body.result === 1) {
                            that.searchData = response.body.data
                        } else {
                            that.$message.error('获取失败,请重试')
                        }
                    }).catch(err => {
                        that.$message.error('获取失败,请重试')
                    });
                },
                titleFormat(row, column, cellValue) {
                    return this.titleFormatLimit(cellValue, 10)
                },
                titleFormatLimit(title, length) {
                    if (!title) return ''
                    if (title.length > length) {
                        return title.slice(0, length) + '...'
                    }
                    return title
                },
                goodsDel(index, goods_id) {
                    this.goodsData.splice(index, 1)
                    let delIndex = this.addGoodsIds.indexOf(goods_id)
                    this.addGoodsIds.splice(delIndex, 1)

                },
                insertGoods(index, id) {
                    if (this.goodsData.filter(data => data.id === id).length === 0) {
                        // 如果是修改页面,过滤不需要的选择的商品ID
                        if (this.form.id) {
                            if (this.recordGoods.data.filter(data => data.id === id).length === 0) {
                                this.goodsData.unshift(this.searchData.data[index])
                                this.addGoodsIds.push(id)
                            }
                        } else {
                            this.goodsData.unshift(this.searchData.data[index])
                            this.addGoodsIds.push(id)
                        }
                    }
                },
                handleCurrentPage(page) {
                    this.searchGoods(page)
                },
                handlePrevPage(page) {
                    // 必要方法,无需执行任何代码
                },
                handleNextPage(page) {
                    // 必要方法,无需执行任何代码
                },
                getQueryVariable(variable) {
                    let query = window.location.search.substring(1);
                    let vars = query.split("&");
                    for (let i = 0; i < vars.length; i++) {
                        let pair = vars[i].split("=");
                        if (pair[0] == variable) {
                            return pair[1];
                        }
                    }
                    return false;
                },
                getCategory(id) {
                    const url = `{!! yzWebUrl('enoughReduce.postage-included-category.record') !!}`
                    this.$http.post(url, {id: id}).then(response => {
                        if (response.body.result === 1) {
                            this.recordGoods = response.body.data.goods
                            this.form.sort = response.body.data.sort
                            this.form.name = response.body.data.name
                            this.form.is_display = response.body.data.is_display
                            this.form.id = response.body.data.id
                            this.loadingDisplay = this.recordGoods.current_page !== this.recordGoods.last_page
                            console.log(this.recordGoods)
                        } else {
                            this.$message.error('获取编辑信息失败,请重试')
                        }
                    }).catch(err => {
                        this.$message.error('获取编辑信息失败,请重试')

                    });
                },
                goodsDestroy(id, goods_id, index) {
                    let params = {
                        id: id,
                        goods_id: goods_id
                    }
                    const url = `{!! yzWebUrl('enoughReduce.postage-included-category.goods-destroy') !!}`
                    this.$http.post(url, params).then(response => {
                        if (response.body.result === 1) {
                            this.$message.success('已删除分类商品')
                            this.recordGoods.data.splice(index, 1)
                        } else {
                            this.$message.error('获取编辑信息失败,请重试')
                        }
                    }).catch(err => {
                        console.log(err)
                    });
                },
                loadGoods() {
                    console.log(123213)
                    let params = {
                        id: this.form.id,
                        page: this.recordGoods.current_page + 1
                    }
                    const url = `{!! yzWebUrl('enoughReduce.postage-included-category.record') !!}`
                    this.$http.post(url, params).then(response => {
                        if (response.body.result === 1) {
                            console.log(response.data)
                            this.recordGoods.current_page= response.data.data.goods.current_page
                            this.recordGoods.last_page= response.data.data.goods.last_page
                            this.loadingDisplay = this.recordGoods.current_page !== this.recordGoods.last_page

                            this.forGoods(response.data.data.goods.data)
                            this.recordGoods.data.push()
                        } else {
                            this.$message.error('获取编辑信息失败,请重试')
                        }
                    }).catch(err => {
                        console.log(err)

                    });
                },
                forGoods(data) {
                    for (let index in data) {
                        this.recordGoods.data.push(data[index])
                    }
                },
                handleClick(val) {
                    if (val.name == 1) {
                        window.location.href = `{!! yzWebFullUrl('enoughReduce.index.index') !!}`;
                    } else if (val.name == 2) {
                        window.location.href = `{!! yzWebFullUrl('enoughReduce.postage-included-category.index') !!}`;
                    }
                }
            }
        })
    </script>
    <style>
        .createCategory {
            background-color: #e5f6f1;
            color: #48c1a7;
            margin-right: 20px;
        }

        .goodsAdd {
            float: left;
            height: 80px;
            width: 80px;
            margin: 0px 10px 100px 0px;
            position: relative;
        }

        .goodsAdd .del {
            position: absolute;
            display: flex;
            align-items: center;
            cursor: pointer;
            justify-content: center;
            width: 30px;
            height: 30px;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 50%;
            top: 0;
            right: 0;
            color: white;
            font-size: 18px;
        }
    </style>
@endsection