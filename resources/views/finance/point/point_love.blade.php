@extends('layouts.base')

@section('content')
    <link href="{{static_url('yunshop/balance/balance.css')}}" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <style>
        .vue-main-form {
            margin-top: 0;
        }
        .vue-main{
            height: 100%;
            padding: 5px;
        }
        .content {
            background: #eff3f6;
            padding: 10px !important;
        }

        .el-form-item__label {
            width: 300px;
            text-align: right;
            color: #2f2310;
        }

        .on-submit-div {
            background-color: #fff !important;
            border-top: #f6f6f6;
            margin: 0 10px;
            border-top-style: solid;
            height: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .el-radio-button__inner, .el-radio-group {
            vertical-align: 0;
        }
        .
    </style>
    <div id="app" v-cloak class="main">
        <div class="block">
            @include('layouts.vueTabs')
        </div>
        <el-form ref="form" :model="set">
            <div class="block">
                <div class="vue-main">
                    <div class="vue-main-form">
                        <div class="vue-main-title" style="margin-bottom:20px">
                            <div class="vue-main-title-left"></div>
                            <div class="vue-main-title-content">
                                积分自动转入设置
                            </div>
                        </div>
                        <el-form-item label="粉丝">
                            <div style="display: flex;flex-direction: column;justify-content: center">
                                <div style="width: 100px; height: 100px">
                                    <el-image
                                            style="width: 100px; height: 100px"
                                            :src="memberModel.avatar"
                                    v-if="info">
                                    </el-image>
                                </div>
                                <div style="width: 100px;text-align: center">
                                    <span class="demonstration">[[memberModel.nickname]]</span>
                                </div>
                            </div>
                        </el-form-item>
                        <el-form-item label="姓名">
                            [[memberModel.realname ? memberModel.realname : memberModel.nickname]]
                        </el-form-item>
                        <el-form-item label="手机号">
                            [[memberModel.mobile]]
                        </el-form-item>
                        <el-form-item label="转入比例">
                            <el-input style="width: 450px"
                                      v-model="set.rate">
                                <template slot="append">%</template>
                            </el-input>
                            <span class='help-block'>自动转入爱心值/111独立比例设置：为空、为零使用基础设置中自动转入比例，-1 此会员不自动转入</span>
                        </el-form-item>
                        <el-form-item label="积分转入爱心值比例设置">
                            <el-input style="width: 450px"
                                      v-model="set.transfer_integral">
                                <template slot="append"><span style="font-size: 16px"><b>:</b></span></template>
                            </el-input>
                            <el-input class="" style="width: 450px"
                                      v-model="set.transfer_love">
                            </el-input>
                            <span class='help-block'>如果积分转入爱心值比例设置为空、为零，使用全局比例设置</span>
                        </el-form-item>
                    </div>
                </div>
            </div>
            <div class="vue-page" style="text-align: center">
                <el-button type="primary" @click="onSubmit">提交</el-button>
            </div>
        </el-form>
    </div>
    <script>
        var vm = new Vue({
            el: '#app',
            // 防止后端冲突,修改ma语法符号
            delimiters: ['[[', ']]'],
            data() {
                return {
                    set:{
                        rate: '',
                        transfer_love: '',
                        transfer_integral: '',
                    },
                    memberModel: {},
                    activeName: '',
                    member_id: '',
                    tab_list:{},
                    info: false
                }
            },
            mounted() {
                this.member_id = this.getParam('member_id')
                this.getData()
            },
            //定义全局的方法
            beforeCreate() {
            },
            filters: {},
            methods: {
                getParam(name) {
                    return location.href.match(new RegExp("[?#&]" + name + "=([^?#&]+)", "i"))
                        ? RegExp.$1
                        : "";
                },
                getData() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.$http.post('{!! yzWebFullUrl('finance.point-love.index') !!}',{member_id:this.member_id}).then(function (response) {
                        if (response.data.result) {
                            this.memberModel = response.data.data.memberModel
                            console.log(this.memberModel)
                            this.set.rate = response.data.data.memberModel.point_love ? response.data.data.memberModel.point_love.rate : ''
                            this.set.transfer_integral = response.data.data.memberModel.point_love ? response.data.data.memberModel.point_love.transfer_integral : ''
                            this.set.transfer_love = response.data.data.memberModel.point_love ? response.data.data.memberModel.point_love.transfer_love : ''
                            this.tab_list = response.data.data.tab_list
                            loading.close();
                            this.info = true;
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
                onSubmit() {
                    let loading = this.$loading({
                        target: document.querySelector(".content"),
                        background: 'rgba(0, 0, 0, 0)'
                    });
                    this.set.member_id = this.member_id
                    this.$http.post('{!! yzWebFullUrl('finance.point-love.update') !!}', this.set).then(function (response) {
                        if (response.data.result) {
                            this.$message({
                                message: response.data.msg,
                                type: 'success'
                            });
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
                removeConsumeItem(index) {
                    this.set.enoughs.splice(index, 1)
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
                handleClick() {
                    window.location.href = this.getUrl()
                },
            },
        })
    </script>

@endsection