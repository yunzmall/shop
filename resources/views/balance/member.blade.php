@extends('layouts.base')
@section('title', '余额管理')
@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .el-form-item__label {
            width: 300px;
            text-align: right;
        }
        .alert.alert-warning {
            border: 1px;
            color: red;
            border-radius: 3px;
            box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.14), 0 7px 10px -5px rgba(255, 152, 0, 0.4);
            background-color: #fcf4f4;
        }
        .note-span {
            width: 290px;
            text-align: right;
            margin-right: 10px;
        }

        .el-radio-group {
            display: inline-block;
            line-height: 1;
            vertical-align: inherit;
        }
        .content{
            background: #eff3f6;
            padding: 10px!important;
        }
        .con{
            padding-bottom:40px;
            position:relative;
            border-radius: 8px;
            min-height:100vh;
        }
        .con .setting .block{
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
        .con .confirm-btn{
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
        .vue-main-form {
            margin-top: 0;
        }
    </style>
    <div id="app" v-cloak class="main">
        <div class="block">
            <div class="vue-head">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">会员余额</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">
                        <el-form-item label="">
                            <el-input
                                    placeholder="会员ID/姓名/昵称/手机号"
                                    v-model="search_form.realname"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select clearable v-model="search_form.level_id" placeholder="会员等级">
                                <el-option
                                        v-for="item in member_level"
                                        :key="item.id"
                                        :label="item.level_name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select clearable v-model="search_form.group_id" placeholder="会员分组">
                                <el-option
                                        v-for="item in member_group"
                                        :key="item.id"
                                        :label="item.group_name"
                                        :value="item.id">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input clearable placeholder="最小" style="width: 250px" v-model="search_form.min_credit2">
                                <template slot="prepend">余额区间</template>
                                <template slot="append">-</template>
                            </el-input>
                            <el-input clearable placeholder="最大" style="width: 100px;margin-left: -5px"
                                      v-model="search_form.max_credit2">
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-button type="primary" @click="search(1)">搜索</el-button>
                        </el-form-item>
                        <el-form-item label="">
                            <el-button type="primary" @click="exportList()">导出 EXCEL</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
        </div>
        <div class="block">
            <div class="vue-main">
                <div class="vue-main-form">
                    <div class="vue-main-title" style="margin-bottom:20px">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">
                            会员余额列表
                            <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               总数：[[total]] &nbsp;
                               余额总合计：[[amount]]
                            </span>
                        </div>
                    </div>
                    <el-table :data="page_list.data" style="width: 100%">
                        <el-table-column label="会员ID" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.uid]]
                            </template>
                        </el-table-column>
                        <el-table-column label="粉丝" align="center" prop="created_at" width="auto">
                            <template slot-scope="scope">
                                <div>
                                    <el-image style='width:30px;height:30px;padding:1px;border:1px solid #ccc'
                                              v-if="scope.row.avatar"
                                              :src="scope.row.avatar"
                                              alt="">

                                    </el-image>
                                    <el-image
                                            v-if="!scope.row.avatar"
                                            :src="shopSet.headimg"
                                            alt="">
                                    </el-image>
                                </div>
                                <div>
                                    [[scope.row.nickname]]
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column label="姓名/电话号码" align="center" prop="">
                            <template slot-scope="scope">
                                [[scope.row.realname]] <br>
                                [[scope.row.mobile]]
                            </template>
                        </el-table-column>
                        <el-table-column label="等级/分组" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.yz_member.level ? scope.row.yz_member.level.level_name :
                                shopSet.level_name]]
                                <br>
                                [[scope.row.yz_member.group ? scope.row.yz_member.group.group_name : '']]
                            </template>
                        </el-table-column>
                        <el-table-column label="余额" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <span style="background-color: #fff7e6;color: #efb43c;padding: 5px">
                                余额：[[scope.row.credit2]]
                                </span>
                            </template>
                        </el-table-column>
                        <el-table-column label="操作" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <el-button type="primary" @click="nav(scope.row.uid)">余额充值</el-button>
                            </template>
                        </el-table-column>
                    </el-table>
                </div>
            </div>
        </div>

        <!-- 分页 -->
        <div class="vue-page">
            <el-row>
                <el-col align="right">
                    <el-pagination layout="prev, pager, next,jumper" @current-change="search" :total="total"
                                   :page-size="per_page" :current-page="current_page" background
                    ></el-pagination>
                </el-col>
            </el-row>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: '#app',
            // 防止后端冲突,修改ma语法符号
            delimiters: ['[[', ']]'],
            data() {
                return {
                    search_form: {
                        realname: '',
                        level_id: '',
                        group_id: '',
                        min_credit2: '',
                        max_credit2: ''
                    },
                    member_level: [],
                    member_group: [],
                    page_list: {
                        data:[]
                    },
                    total: 0,
                    per_page: 0,
                    current_page: 0,
                    shopSet: {headimg:'',level_name:'普通会员'},
                    amount:0

                }
            },
            created() {
                this.getData(1)
            },
            //定义全局的方法
            beforeCreate() {
            },
            filters: {},
            methods: {
                getData(page) {
                    let search = this.search_form
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('balance.member.result-data') !!}', {
                        search: search,
                        page: page
                    }).then(function (response) {
                        if (response.data.result) {
                            console.log(response.data.data)
                            this.page_list = response.data.data.pageList
                            this.member_level = response.data.data.memberLevel
                            this.member_group = response.data.data.memberGroup
                            this.total = response.data.data.pageList.total
                            this.per_page = response.data.data.pageList.per_page
                            this.current_page = response.data.data.pageList.current_page
                            if(response.data.data.shopSet){
                                this.shopSet = response.data.data.shopSet
                            }
                            this.amount = response.data.data.amount
                            loading.close();
                        } else {
                            this.$message({
                                message: response.data.msg,
                                type: 'error'
                            });
                        }

                        loading.close();
                    }, function (response) {
                        this.$message({
                            message: response.data.msg,
                            type: 'error'
                        });
                        loading.close();
                    });
                },
                search(page) {
                    this.getData(page)
                },
                exportList() {
                    let that = this;
                    console.log(that.search_form);

                    let json = that.search_form;

                    let url = '{!! yzWebFullUrl('balance.member-export.index') !!}';


                    if (that.code) {
                        url += "&code=" + that.code
                    }

                    for (let i in json) {
                        if (json[i]) {
                            url += "&search[" + i + "]=" + json[i]
                        }
                    }

                    console.log(url);
                    window.location.href = url;
                },
                nav(uid) {
                    let url = '{!! yzWebFullUrl('balance.recharge.index') !!}';
                    window.open(url + "&member_id=" + uid)
                }
            },
        })
    </script>
@endsection
