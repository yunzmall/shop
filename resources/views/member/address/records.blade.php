@extends('layouts.base')
@section('title', '收货地址管理')
@section('content')
<link href="{{static_url('yunshop/css/total.css')}}" media="all" rel="stylesheet" type="text/css" />

<style>
    .vue-title {
        display: flex;
        margin: 5px 0;
        line-height: 32px;
        font-size: 16px;
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
        flex: 1;
    }

    .member-info {
        display: flex;
        margin: 38px;
        justify-content: space-between;
    }

    .search-top {
        display: flex;
        height: 200px;
    }

    .top-item1 {
        flex: 1;
    }

    .top-item2 {
        width: 540px;
    }

    .audit,
    .is_follow {
        width: 60px;
        height: 22px;
        text-align: center;
        line-height: 22px;
        font-size: 14px;
        color: #fff;
        margin: 0 auto;
        border-radius: 4px;
        background-color: #13c7a7;
    }

    .un_follow {
        width: 70px;
        height: 22px;
        text-align: center;
        line-height: 22px;
        font-size: 14px;
        color: #fff;
        margin: 0 auto;
        border-radius: 4px;
        background-color: #ffb025;
    }

    .name-over {
        line-height: 37px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .el-table_1_column_2 .cell {
        /* display: flex; */
    }

    /* 分页 */
    .pagination-right {
        text-align: center;
        margin: 50px auto;
    }

    /* 列表 */
    p {
        font-size: 12px;
        margin-top: 5px;
    }

    .cell {
        /* border:1px solid red; */
        text-align: center;
    }
</style>
<div class="all">
    <div id="app">
        <div class="total-head">
            <div class="top-item2">
                <div class="vue-title">
                    <div class="vue-title-left"></div>
                    <div class="vue-title-content">收货地址</div>
                </div>
                <div style="display:flex;margin-top:20px;">
                    <div>
                        <img style="width:90px;height:90px;" :src="avatar" alt="">
                    </div>
                    <div style="margin-left:10px;display:flex;flex-direction:column;justify-content: space-between;line-height:20px;">
                        <p style="color: #333333;font-weight:600">姓名：[[realname]]</p>
                        <p style="color: #333333;font-weight:600">昵称：[[nickname]]</p>
                        <p style="color: #333333;font-weight:600">手机号：[[mobile]]</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="total-floo">
            <el-table :data="tableData" style="width: 100%;">
                <el-table-column label="ID">
                    <template slot-scope="scope">
                        <p>[[ scope.row.id ]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="收货人">
                    <template slot-scope="scope">
                        <p>[[ scope.row.username ]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="手机号">
                    <template slot-scope="scope">
                        <p>[[ scope.row.mobile ]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="省份">
                    <template slot-scope="scope">
                        <p>[[ scope.row.province ]]</p>
                    </template>
                </el-table-column>

                <el-table-column label="城市">
                    <template slot-scope="scope">
                        <p>[[ scope.row.city ]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="区域">
                    <template slot-scope="scope">
                        <p>[[ scope.row.district]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="街道">
                    <template slot-scope="scope">
                        <p v-if="is_street==1">[[ scope.row.street]]</p>
                    </template>
                </el-table-column>
                <el-table-column label="详细地址">
                    <template slot-scope="scope">
                        <p>[[ scope.row.address ]]</p>
                    </template>
                </el-table-column>
            </el-table>
        </div>
        <div class="fixed total-floo">
            <div class="fixed_box">
                <el-form>
                    <el-form-item>
                        <el-button @click="returnEvent">返回</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</div>

<script>
    var vm = new Vue({
        el: '#app',
        delimiters: ['[[', ']]'],
        data() {
            return {
                avatar: "",
                nickname: "",
                tableData: [],
                realname: "",
                mobile: "",
                currentPage: 1,
                pagesize: 6,
                total: 1,
                is_street: ""
            }
        },
        created() {
            //优化在不同设备固定定位挡住的现象设置父元素的内边距
            window.onload = function() {
                let all = document.querySelector(".all");
                let h = window.innerHeight * 0.05;
                all.style.paddingBottom = h + "px";
            }
            let i = window.location.href.indexOf('id=');
            if (i !== -1) {
                let id = Number(window.location.href.slice(i + 3));
                this.postVipInfoList(id);
            }
        },
        methods: {
            //回退
            hisGo(i) {
                //  console.log(i);
                history.go(i)
            },
            //返回
            returnEvent() {
                history.go(-1);
            },
            //会员信息列表
            postVipInfoList(id) {
                this.$http.post("{!!yzWebFullUrl('member.member-address.show')!!}", {
                    member_id: id
                }).then(res => {
                    let {
                        member,
                        address,
                        is_street
                    } = res.body.data;
                    //会员头像
                    this.avatar = member.avatar;
                    //昵称
                    this.nickname = member.nickname;
                    //真实姓名
                    this.realname = member.realname;
                    //手机号码
                    this.mobile = member.mobile;
                    console.log(res);
                    console.log(member);
                    this.tableData = address
                    //判断街道
                    // console.log(is_street,89622);
                    this.is_street = is_street;
                })
            },
        }
    })
</script>

@endsection