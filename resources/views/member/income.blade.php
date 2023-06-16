@extends('layouts.base')
@section('title', '会员收入详情')
@section('content')
<link href="{{static_url('yunshop/css/member.css')}}" media="all" rel="stylesheet" type="text/css" />
<style scoped>
    /* 全屏背景 */
    body {
        background: #eff3f6;
    }

    /* 头部 */
    .total-head {
        padding: 10px;
        margin: 20px 0;
        background: white;
        box-sizing: border-box;
        border-radius: 5px;
    }

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
        /* border:1px solid red; */
        /* width:calc(100% - 100px); */
        display: flex;
        /* margin: 38px; */
        justify-content: space-between;
    }

    .item1 {
        display: flex;
        margin-left:14px;
    }

    /* 收入的金额 */
    .user-info {
        width: 200px;
        color: #333;
        /* display: flex; */
        margin-left: 19px;
        overflow: hidden;
        /* flex-direction: column; */
        justify-content: space-between;
        /* font-family: SourceHanSansCN-Regular; */
        font-family: arial, "Hiragino Sans GB", "Microsoft Yahei", 微软雅黑, 宋体, 宋体, Tahoma, Arial, Helvetica, STHeiti;
        font-size: 14px;
        /* font-weight: normal;
        font-stretch: normal; */
        letter-spacing: 1px;
        line-height: 33px;
        margin-top: -5px;
    }
    .user-info div:nth-child(1){
        color: rgb(51, 51, 51);
        font-weight: 600;
    }
    .user-info div:not(div:nth-child(1)) {
        /* color: #868686; */
        /* font-weight: 600; */
        color: rgb(134, 134, 134);
    }
    .member_income_box{
        display: flex;
        /* border:1px yellow solid; */
        width: 819px;
        justify-content:space-around;
        margin-right:9%;
    }
    .item2-price,
    .item3-price,
    .item4-price {
        font-family: SourceHanSansCN-Bold;
        font-size: 32px;
        font-weight: 600;
        font-stretch: normal;
        letter-spacing: 2px;
        color: #f3766c;
        line-height: 55px;
    }

    .item2-price {
        color: #f3766c;
    }

    .item3-price {
        color: #faa701;
    }

    .item4-price {
        color: #2cc08d;
    }

    .item2-income,
    .item3-income,
    .item4-income {
        font-family: SourceHanSansCN-Regular;
        font-size: 20px;
        font-weight: normal;
        font-stretch: normal;
        letter-spacing: 1px;
        color: #595959;
        text-align: center;
        margin-top:2px;
    }

    /* 收入的类型 */
    .el-table .cell {
        font-family: SourceHanSansCN-Regular;
        font-size: 14px;
        font-weight: normal;
        font-stretch: normal;
        letter-spacing: 0px;
        color: #666666;
    }

    .el-table th>.cell {
        font-weight: 600;
    }

    .el-table__row .cell {
        letter-spacing: 1px;
    }

    [v-cloak] {
        display: none;
    }

    .icon-solid {
        width: 1px;
        height: 46px;
        margin-top:20px;
        background-color: #dedede;
    }
    .el-table thead {
        height:72px;
    }
</style>
<div class="all">
    <div id="app" v-cloak>
        <div class="total-head">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">收入详情</div>
            </div>
            <div class="member-info">
                <div class="item1">
                    <!-- 会员头像信息 -->
                    <div class="avatar">
                        <img style="width:90px;height:90px;" :src="avatar" alt="">
                    </div>
                    <!-- 会员个人信息 -->
                    <div class="user-info">
                        <div>会员id：[[uid]]</div>
                        <div>姓名：[[realname]]</div>
                        <div>手机号码：[[mobile]]</div>
                    </div>
                </div>
                <div class="member_income_box">
                <!-- 会员收入和余额 -->
                <div class="item2">
                    <div class="item2-price">[[income]]</div>
                    <div class="item2-income">累计收入</div>
                </div>
                <div class="icon-solid"></div>
                <div class="item3">
                    <div class="item3-price">[[withdraw]]</div>
                    <div class="item3-income">累计提现</div>
                </div>
                <div class="icon-solid"></div>
                <div class="item4">
                    <div class="item4-price">[[no_withdraw]]</div>
                    <div class="item4-income">未提现</div>
                </div>
                </div>
            </div>
        </div>
        <div class="total-floo">
            <!-- 会员收入详情 -->
            <el-table :data="tableData" style="width:100%" :row-style="{height:'82px'}" >
                <el-table-column  height="50px" prop="type_name" align="center" label="收入类型">
                </el-table-column>
                <el-table-column prop="income" align="center" label="收入总金额">
                </el-table-column>
                <el-table-column prop="withdraw" align="center" label="已提现金额">
                </el-table-column>
                <el-table-column prop="no_withdraw" align="center" label="未提现金额">
                </el-table-column>
            </el-table>
        </div>
    </div>
</div>
<script>
    var vm = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        data() {
            return {
                tableData: [],
                avatar: "",
                uid: "",
                mobile: "",
                realname: "",
                income: "",
                no_withdraw: "",
                withdraw: ""

            }
        },
        created() {
            let i = window.location.href.indexOf('id=');
            if (i !== -1) {
                this.isid = true
                let id = Number(window.location.href.slice(i + 3));
                this.id = id
                this.postAgent(id);
            }
        },
        methods: {
            //回退
            hisGo(i) {
                //  console.log(i);
                history.go(i)
            },
            postAgent(id) {
                this.$http.post("{!!yzWebFullUrl('member.member-income.show')!!}", {
                    id
                }).then(res => {
                    console.log(res);
                    let {
                        member,
                        incomeAll,
                        item
                    } = res.body.data
                    //头像
                    this.avatar = member.avatar;
                    //会员id
                    this.uid = member.uid;
                    //昵称
                    this.mobile = member.mobile;
                    //姓名
                    this.realname = member.realname;
                    //累计收入
                    this.income = incomeAll.income;
                    //累计提现
                    this.withdraw = incomeAll.withdraw
                    //未提现
                    this.no_withdraw = incomeAll.no_withdraw
                    for (let i in item) {
                        this.tableData.push(item[i]);
                    }
                    console.log(this.tableData);
                })
            }
        }
    })
</script>
@endsection