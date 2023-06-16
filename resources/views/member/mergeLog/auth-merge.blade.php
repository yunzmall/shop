@extends('layouts.base')
@section('title', "")
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-head">
                <div class="vue-main-title" style="margin-bottom:20px">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">会员合并记录</div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">
                        <el-form-item label="">
                            <el-input v-model="search_form.before_uid" placeholder="合并前会员id"></el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input v-model="search_form.after_uid" placeholder="合并后会员id"></el-input>
                        </el-form-item>
                        <el-form-item label="" style="float: right">
                            <el-button type="primary" @click="search(1)">搜索</el-button>
                            <el-button type="primary" @click="oldLog">查看历史记录</el-button>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
            <div class="vue-main">
                <div class="vue-main-form">
                    <div class="vue-main-title" style="margin-bottom:20px">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">合并记录</div>
                        <div class="vue-main-title-button">
                            总数：[[ total ]]
                        </div>
                    </div>
                    <el-table :data="data_list" style="width: 100%">
                        <el-table-column label="时间" align="center" prop="created_at" width="auto"></el-table-column>
                        <el-table-column label="合并前uid" align="center" prop="before_uid" width="auto"></el-table-column>
                        <el-table-column label="合并后uid" align="center" prop="after_uid" width="auto"></el-table-column>
                        <el-table-column label="合并前手机号" align="center" prop="before_mobile" width="auto"></el-table-column>
                        <el-table-column label="合并后手机号" align="center" prop="after_mobile" width="auto"></el-table-column>
                        <el-table-column label="合并前余额" align="center" prop="before_amount" width="auto"></el-table-column>
                        <el-table-column label="合并后余额" align="center" prop="after_amount" width="auto"></el-table-column>
                        <el-table-column label="合并前积分" align="center" prop="before_point" width="auto"></el-table-column>
                        <el-table-column label="合并后积分" align="center" prop="after_point" width="auto"></el-table-column>
                        <el-table-column :label="'合并前可用'+love_name" align="center" prop="before_love_usable" width="auto" v-if="is_love"></el-table-column>
                        <el-table-column :label="'合并后可用'+love_name" align="center" prop="after_love_usable" width="auto" v-if="is_love"></el-table-column>
                        <el-table-column :label="'合并前冻结'+love_name" align="center" prop="before_love_froze" width="auto" v-if="is_love"></el-table-column>
                        <el-table-column :label="'合并后冻结'+love_name" align="center" prop="after_love_froze" width="auto" v-if="is_love"></el-table-column>
                        <el-table-column label="合并类型" align="center" prop="merge_type_name" width="auto"></el-table-column>
                    </el-table>
                </div>
            </div>
            <!-- 分页 -->
            <div class="vue-page" >
                <el-row>
                    <el-col align="right">
                        <el-pagination layout="prev, pager, next,jumper" @current-change="search" :total="total"
                                       :page-size="per_page" :current-page="current_page" background
                        ></el-pagination>
                    </el-col>
                </el-row>
            </div>
        </div>
    </div>

    <script>
        var is_love = '{!! app('plugins')->isEnabled('love') !!}';
        var love_name = '{!! $love_name !!}';
        var app = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            data() {
                return {
                    data_list: [],
                    search_form: {
                        before_uid:"",
                        after_uid:"",
                    },
                    current_page: 1,
                    total: 1,
                    per_page: 1,
                    is_love:is_love,
                    love_name:love_name,
                }
            },
            created() {

            },
            mounted() {
                this.getData(1);
            },
            methods: {
                getData(page) {
                    let json = {
                        page: page,
                        search: {
                            before_uid:this.search_form.before_uid,
                            after_uid:this.search_form.after_uid,
                        },
                    };
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('member.merge-log.index') !!}',json).then(function(response) {
                        if (response.data.result) {
                            let list = response.data.data.list;
                            this.data_list = list.data;
                            this.current_page = list.current_page;
                            this.total = list.total;
                            this.per_page = list.per_page;
                            loading.close();
                        } else {
                            this.$message({message: response.data.msg, type: 'error'});
                        }
                        loading.close();
                    }, function(response) {
                        this.$message({message: response.data.msg, type: 'error'});
                        loading.close();
                    });
                },
                search(val) {
                    this.getData(val);
                },
                oldLog() {
                    let url = '{!! yzWebFullUrl('member.merge-log.old-log') !!}';
                    location.href = url;
                }
            },
        })
    </script>
@endsection