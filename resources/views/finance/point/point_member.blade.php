@extends('layouts.base')

@section('content')

@section('title', trans('积分管理'))
<link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
<style>
    .content {
        background: #eff3f6;
        padding: 10px !important;
    }
    .el-dropdown-menu__item{
        padding: 0;
    }
    .el-dropdown-menu{
        padding: 0;
    }
    .button-style {
        width: 100%;
        height: 100%;
        border: none;
        font-size: 24px;
    }
    .vue-main-form {
        margin-top: 0;
    }
</style>
<div id="app" v-cloak class="main">
    <div class="block">
        @include('layouts.vueTabs')
    </div>
    <div class="block">
        <div class="vue-head">
            <div class="vue-main-title" style="margin-bottom:20px">
                <div class="vue-main-title-left"></div>
                <div class="vue-main-title-content">会员积分</div>
                <div class="vue-main-title-button">
                </div>
            </div>
            <div class="vue-search">
                <el-form :inline="true" :model="search_form" class="demo-form-inline">
                    <el-form-item label="">
                        <el-input
                                style="width: 270px"
                                placeholder="会员ID／会员姓名／昵称／手机号"
                                v-model="search_form.realname"
                                clearable>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="">
                        <el-select clearable v-model="search_form.level" placeholder="会员等级">
                            <el-option
                                    v-for="item in member_level"
                                    :key="item.id"
                                    :label="item.level_name"
                                    :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="">
                        <el-select clearable v-model="search_form.groupid" placeholder="会员分组">
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
                            <template slot="prepend">积分区间</template>
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
                        记录列表
                        <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               总数：[[total]] &nbsp;
                               积分总合计：[[amount]]
                            </span>
                    </div>
                </div>

                <el-table :data="record_list.data" style="width: 100%">
                    <el-table-column label="会员ID" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.uid]]
                        </template>
                    </el-table-column>
                    <el-table-column label="粉丝" align="center" prop="created_at" width="auto">
                        <template slot-scope="scope">
                            <div>
                                <el-image v-if="scope.row.uid"
                                          style='width:30px;height:30px;padding:1px;border:1px solid #ccc'
                                          :src="scope.row.avatar"
                                          alt="">
                                </el-image>
                            </div>
                            <div>
                                <el-button type="text" @click="memberNav(scope.row.uid)">
                                    [[scope.row.nickname]]
                                </el-button>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column label="姓名/手机号" align="center" prop="">
                        <template slot-scope="scope">
                            [[scope.row.realname]] <br>
                            [[scope.row.mobile]]
                        </template>
                    </el-table-column>
                    <el-table-column label="等级" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.yz_member.level ? scope.row.yz_member.level.level_name : shopSet.level_name]]
                        </template>
                    </el-table-column>
                    <el-table-column label="分组" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            [[scope.row.yz_member.group ? scope.row.yz_member.group.group_name : shopSet.group_name]]
                        </template>
                    </el-table-column>
                    <el-table-column label="积分" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                                <span style="background-color: #fff7e6;color: #efb43c;padding: 5px">
                                积分：[[scope.row.credit1]]
                                </span>
                        </template>
                    </el-table-column>
                    <el-table-column label="操作" align="center" prop="" width="auto">
                        <template slot-scope="scope">
                            <div style="display: flex;justify-content: center;">
                                <el-tooltip content="充值积分" placement="top" effect="light">
                                    <el-button @click="rechargeNav(scope.row.uid)" icon="el-icon-wallet"
                                               class="button-style">
                                    </el-button>
                                </el-tooltip>

                                <el-tooltip content="积分明细" placement="top" effect="light">
                                    <el-button @click="detailNav(scope.row.uid)" icon="el-icon-s-order"
                                               class="button-style">
                                    </el-button>
                                </el-tooltip>
                                <el-tooltip v-if="transfer_love" content="转出设置" placement="top" effect="light">
                                    <el-button @click="transferSetNav(scope.row.uid)" icon="el-icon-setting"
                                               class="button-style">
                                    </el-button>
                                </el-tooltip>
                            </div>
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
                    member_id: '',
                    level: '',
                    groupid: '',
                    pay_way: '',
                    min_credit2: '',
                    max_credit2: ''
                },
                transfer_love:'',
                activeName: 'member_point',
                record_list: {},
                total: 0,
                per_page: 0,
                current_page: 0,
                pageSize: 0,
                amount: 0,
                member_group: [],
                member_level: [],
                select:0,
                tab_list:[],
                shopSet:{}
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
                this.$http.post('{!! yzWebFullUrl('finance.point-member.index') !!}', {
                    search: search,
                    page: page
                }).then(function (response) {
                    if (response.data.result) {
                        console.log(response.data.data)
                        this.record_list = response.data.data.memberList
                        this.total = response.data.data.memberList.total
                        this.per_page = response.data.data.memberList.per_page
                        this.current_page = response.data.data.memberList.current_page
                        this.member_group = response.data.data.memberGroup
                        this.amount = response.data.data.amount
                        this.member_level = response.data.data.memberLevel
                        this.tab_list = response.data.data.tab_list
                        this.transfer_love = response.data.data.transfer_love
                        this.shopSet = response.data.data.shopSet
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
                let json = this.search_form;
                let url = '{!! yzWebFullUrl('point.member-export.index') !!}';


                for (let i in json) {
                    if (json[i]) {
                        url += "&search[" + i + "]=" + json[i]
                    }
                }

                console.log(url);
                window.location.href = url + '&export=1';
            },
            memberNav(uid) {
                let url = '{!! yzWebFullUrl('member.member.detail') !!}';
                window.open(url + "&id=" + uid)
            },
            handleClick() {
                window.location.href = this.getUrl()
            },
            getUrl() {
                let url = ''
                switch (this.activeName) {
                    case 'member_point' :
                        url = '{!! yzWebFullUrl('finance.point-member.index') !!}';
                        break;
                    case 'basic_set' :
                        url = '{!! yzWebFullUrl('finance.point-set.index') !!}';
                        break;
                    case 'recharge_record' :
                        url = '{!! yzWebFullUrl('point.recharge-records.index') !!}';
                        break;
                    case 'point_detailed' :
                        url = '{!! yzWebFullUrl('point.records.index') !!}';
                        break;
                    case 'point_queue' :
                        url = '{!! yzWebFullUrl('point.queue.index') !!}';
                        break;
                    case 'queue_detailed' :
                        url = '{!! yzWebFullUrl('point.queue-log.index') !!}';
                        break;
                    case 'superior_queue' :
                        url = '{!! yzWebFullUrl('point.queue-log.parentIndex') !!}';
                        break;
                }
                return url
            },
            detailNav(id) {
                let url = '{!!yzWebUrl('point.records.index')!!}';

                window.open(url + "&member_id=" + id);
            },
            rechargeNav(id) {
                let url = '{!!yzWebUrl('point.recharge.index')!!}';

                window.open(url + "&id=" + id);
            },
            transferSetNav(id) {
                let url = '{!!yzWebUrl('finance.point-love.index')!!}';

                window.open(url + "&member_id=" + id);
            }
        },
    })
</script>
@endsection
