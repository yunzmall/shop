@extends('layouts.base')

@section('content')
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
        .btn{
            width: 50px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: normal;
            height: auto;
            overflow: hidden;
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
                    <div class="vue-main-title-content">上级队列</div>
                    <div class="vue-main-title-button">
                    </div>
                </div>
                <div class="vue-search">
                    <el-form :inline="true" :model="search_form" class="demo-form-inline">
                        <el-form-item label="">
                            <el-input
                                    style="width: 270px"
                                    placeholder="订单号"
                                    v-model="search_form.order_sn"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input
                                    style="width: 270px"
                                    placeholder="会员ID"
                                    v-model="search_form.uid"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-input
                                    style="width: 270px"
                                    placeholder="会员姓名/昵称/手机号"
                                    v-model="search_form.member_kwd"
                                    clearable>
                            </el-input>
                        </el-form-item>
                        <el-form-item label="">
                            <el-select clearable v-model="search_form.status" placeholder="队列状态">
                                <el-option
                                        v-for="item in queue_status"
                                        :key="item.value"
                                        :label="item.name"
                                        :value="item.value">
                                </el-option>
                            </el-select>
                        </el-form-item>
                        <el-form-item label="">
                            <el-button type="primary" @click="search(1)">搜索</el-button>
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
                            队列列表
                            <span style="margin-left:20px;font-size: 10px;font-weight: 0;color: #9b9da4">
                               总数：[[total]]
                                奖励积分总数：[[amount]]
                            </span>
                        </div>
                    </div>

                    <el-table :data="record_list.data" style="width: 100%">
                        <el-table-column label="id" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.id]]
                            </template>
                        </el-table-column>
                        <el-table-column label="奖励人" align="center" prop="created_at" width="auto">
                            <template slot-scope="scope">
                                <div>
                                    <el-image style='width:30px;height:30px;padding:1px;border:1px solid #ccc'
                                              :src="scope.row.has_one_member.avatar"
                                              alt="">
                                    </el-image>
                                </div>
                                <div>
                                    <el-link type="success" @click="memberNav(scope.row.uid)">
                                       ([[scope.row.uid]]) [[scope.row.has_one_member.nickname]]
                                    </el-link>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="买家" align="center" prop="created_at" width="auto">
                            <template slot-scope="scope">
                                <div>
                                    <el-image style='width:30px;height:30px;padding:1px;border:1px solid #ccc'
                                              :src="scope.row.has_one_order.avatar"
                                              alt="">
                                    </el-image>
                                </div>
                                <div>
                                    <el-link type="success" @click="memberNav(scope.row.has_one_order.uid)">
                                        ([[scope.row.has_one_order.uid]]) [[scope.row.has_one_order.nickname]]
                                    </el-link>
                                </div>
                            </template>
                        </el-table-column>
                        <el-table-column label="订单编号" align="center" prop="">
                            <template slot-scope="scope">
                                <el-link  @click="orderNav(scope.row.has_one_order.id)" type="success">
                                    [[scope.row.has_one_order.order_sn]]
                                </el-link>

                            </template>
                        </el-table-column>
                        <el-table-column label="上级层数/奖励积分" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.level]]级上级 <br>[[scope.row.point]]
                            </template>
                        </el-table-column>
                        <el-table-column label="预赠时间" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.expect_reward_time]]
                            </template>
                        </el-table-column>
                        <el-table-column label="实赠时间" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                [[scope.row.actual_reward_time]]
                            </template>
                        </el-table-column>
                        <el-table-column label="队列状态" align="center" prop="" width="auto">
                            <template slot-scope="scope">
                                <span v-if="scope.row.status==1">已发放</span>
                                <span v-if="scope.row.status==0">待发放</span>
                                <span v-if="scope.row.status==-1">已失效</span>
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
                        uid:'',
                        member_kwd: '',
                        order_sn:'',
                        status:''
                    },
                    activeName: 'superior_queue',
                    record_list: {},
                    total: 0,
                    per_page: 0,
                    current_page: 0,
                    pageSize: 0,
                    amount: 0,
                    search_time: [],
                    tab_list:[],
                    queue_status: [
                        {
                            value: 0,
                            name: '待发放'
                        }, {
                            value: 1,
                            name: '已发放'
                        }, {
                            value: -1,
                            name: '已失效'
                        }
                    ],
                    order_url:''
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
                    this.$http.post('{!! yzWebFullUrl('point.queue-log.parentIndex') !!}', {
                        search: search,
                        page: page
                    }).then(function (response) {
                        if (response.data.result) {
                            this.record_list = response.data.data.list
                            this.total = response.data.data.list.total
                            this.per_page = response.data.data.list.per_page
                            this.current_page = response.data.data.list.current_page
                            this.amount = response.data.data.amount
                            this.tab_list = response.data.data.tab_list
                            this.search_form = response.data.data.search
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
                memberNav(uid) {
                    let url = '{!! yzWebFullUrl('member.member.detail') !!}';
                    window.open(url + "&id=" + uid)
                },
                orderNav(order_id) {
                    let url = '{!! yzWebFullUrl('order.detail') !!}';
                    window.open(url + "&id=" + order_id)
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
                }
            },
        })
    </script>
{{--    <div id="member-blade" class="rightlist">--}}
{{--        <!-- 新增加右侧顶部三级菜单 -->--}}

{{--        <div class="right-titpos">--}}
{{--            @include('layouts.tabs')--}}
{{--        </div>--}}
{{--        <!-- 新增加右侧顶部三级菜单结束 -->--}}
{{--        <div class="panel panel-info">--}}
{{--            <div class="panel-heading">筛选</div>--}}
{{--            <div class="panel-body">--}}
{{--                <form action="" method="post" class="form-horizontal" role="form" id="form1">--}}
{{--                    <div class="form-group col-sm-11 col-lg-11 col-xs-12">--}}
{{--                        <div class="">--}}
{{--                            <div class='input-group'>--}}
{{--                                <input class="form-control" name="search[order_sn]" type="text"--}}
{{--                                       value="{{ $search['order_sn'] ?? ''}}" placeholder="订单号">--}}
{{--                                <input class="form-control" name="search[uid]" type="text"--}}
{{--                                       value="{{ $search['uid'] ?? ''}}" placeholder="会员ID">--}}
{{--                                <input class="form-control" name="search[member_kwd]" type="text"--}}
{{--                                       value="{{ $search['member_kwd'] ?? ''}}" placeholder="会员姓名／昵称／手机号">--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="form-group ">--}}
{{--                        <div class="col-xs-12 col-sm-9 col-md-10">--}}
{{--                            <select style="width: 200px" class="form-control" name="search[status]">--}}
{{--                                <option value="0"--}}
{{--                                        @if(empty($search['status']) && $search['status']!=='0' && $search['status']!==0) selected="selected" @endif>--}}
{{--                                    队列状态--}}
{{--                                </option>--}}
{{--                                <option value="0"--}}
{{--                                        @if($search['status'] == 0 && $search['status'] !== '' && $search['status'] !== null) selected="selected" @endif>--}}
{{--                                    待发放--}}
{{--                                </option>--}}
{{--                                <option value="1" @if($search['status'] == 1) selected="selected" @endif>已发放</option>--}}
{{--                                <option value="-1" @if($search['status'] == -1) selected="selected" @endif>已失效</option>--}}
{{--                            </select>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="form-group col-sm-1 col-lg-1 col-xs-12">--}}
{{--                        <div class="">--}}
{{--                            <input type="submit" class="btn btn-block btn-success" value="搜索">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </form>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <div class="clearfix">--}}
{{--            <div class="panel panel-default">--}}
{{--                <div class="panel-heading">总数：{{ $list->total() }}</div>--}}
{{--                <div class="panel-body">--}}
{{--                    <table class="table table-hover" style="overflow:visible;">--}}
{{--                        <thead class="navbar-inner">--}}
{{--                        <tr>--}}
{{--                            <th style='width:5.5%; text-align: center;'>ID</th>--}}
{{--                            <th style='width:14.5%; text-align: center;'>奖励人</th>--}}
{{--                            <th style='width:14.5%; text-align: center;'>买家</th>--}}
{{--                            <th style='width:16%; text-align: center;'>订单号</th>--}}
{{--                            <th style='width:12.5%; text-align: center;'>上级层数<br>奖励积分</th>--}}
{{--                            <th style='width:15.5%; text-align: center;'>预赠时间</th>--}}
{{--                            <th style='width:15.5%; text-align: center;'>实赠时间</th>--}}
{{--                            <th style='width:5%; text-align: center;'>队列状态</th>--}}
{{--                        </tr>--}}
{{--                        </thead>--}}
{{--                        <tbody>--}}
{{--                        @foreach($list as $item)--}}
{{--                            <tr style="text-align: center;">--}}
{{--                                <td>{{ $item->id }}</td>--}}
{{--                                <td>--}}
{{--                                    @if($item->hasOneMember)--}}
{{--                                        <a href="{!! yzWebUrl('member.member.detail', ['id'=>$item->uid]) !!}"><img--}}
{{--                                                    src="{{$item->hasOneMember->avatar_image}}"--}}
{{--                                                    style="width:30px;height:30px;padding:1px;border:1px solid #ccc"><BR>--}}
{{--                                            ({{$item->hasOneMember->uid}})--}}
{{--                                            {{$item->hasOneMember->nickname}}--}}
{{--                                        </a>--}}
{{--                                    @else--}}
{{--                                        {{$item->uid}}--}}
{{--                                    @endif--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    @if($item->hasOneOrder->belongsToMember)--}}
{{--                                        <a href="{!! yzWebUrl('member.member.detail', ['id'=>$item->hasOneOrder->belongsToMember->uid]) !!}"><img--}}
{{--                                                    src="{{$item->hasOneOrder->belongsToMember->avatar_image}}"--}}
{{--                                                    style="width:30px;height:30px;padding:1px;border:1px solid #ccc"><BR>--}}
{{--                                            ({{$item->hasOneOrder->belongsToMember->uid}})--}}
{{--                                            {{$item->hasOneOrder->belongsToMember->nickname}}--}}
{{--                                        </a>--}}
{{--                                    @endif--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    @if($item->hasOneOrder)--}}
{{--                                        <a href="{!! yzWebUrl('order.detail', ['id'=>$item->order_id,'order_id'=>$item->order_id]) !!}">--}}
{{--                                            {{$item->hasOneOrder->order_sn}}--}}
{{--                                        </a>--}}
{{--                                    @endif--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    {{$item->level}}级上级--}}
{{--                                    <br>--}}
{{--                                    {{$item->point}}--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    {{date('Y-m-d H:i:s',$item->expect_reward_time)}}--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    @if($item->actual_reward_time)--}}
{{--                                        {{date('Y-m-d H:i:s',$item->actual_reward_time)}}--}}
{{--                                    @endif--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    @if($item->status == 1)--}}
{{--                                        已发放--}}
{{--                                    @elseif($item->status == 0)--}}
{{--                                        待发放--}}
{{--                                    @elseif($item->status == -1)--}}
{{--                                        已失效--}}
{{--                                    @endif--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                        @endforeach--}}
{{--                        </tbody>--}}
{{--                    </table>--}}

{{--                    {!! $pager !!}--}}

{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

@endsection