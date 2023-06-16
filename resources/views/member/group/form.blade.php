@extends('layouts.base')
@section('添加会员分组')
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
</style>
<div class="all">
    <div id="app">
        <div class="total-head">
            <div class="vue-title">
                <div class="vue-title-left"></div>
                <div class="vue-title-content">会员分组设置</div>
            </div>
            <el-form label-width="200px">
                <el-form-item :required="true" label="分组名称" placeholder="请输入分组名称">
                    <el-input clearable v-model="form.groupName" v-focus style="width: 600px;"></el-input>
                </el-form-item>
            </el-form>
        </div>
        <div class="fixed total-floo">
            <div class="fixed_box">
                <el-form>
                    <el-form-item>
                        <el-button @click="submit" type="primary">提交</el-button>
                        <el-button @click="returnList">返回列表</el-button>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</div>
<script>
    var vm = new Vue({
        el: "#app",
        delimiters: ['[[', ']]'],
        data() {
            return {
                form: {
                    groupName: ''
                },
                isid: false,
                id: null,
                isCount: 1,
                uniacid: 0
            }
        },
        created() {
            let i = window.location.href.indexOf('id=');
            if (i !== -1) {
                this.isid = true
                let id = Number(window.location.href.slice(i + 3));
                this.id = id
                this.postUpdateGroup(id);
            }
        },
        // 自定义组件
        directives: {
            // 注册一个局部的自定义指令 v-focus
            focus: {
                // 指令的定义
                inserted: function(el) {
                    // 聚焦元素
                    el.querySelector('input').focus()
                }
            }
        },
        methods: {
            //回退
            hisGo(i) {
                //  console.log(i);
                history.go(i)
            },
            //获取更新当前数据
            postUpdateGroup(id) {
                this.$http.post("{!!yzWebFullUrl('member.member-group.update')!!}", {
                    group_id: id,
                    group: this.isCount > 1 ? {
                        group_name: this.form.groupName
                    } : ""
                }).then(res => {
                    console.log(res);
                    if (this.isCount == 1) {
                        let {
                            groupModel: model
                        } = res.body.data
                        // 分组数据
                        this.form.groupName = model.group_name;
                    } else {
                        if (res.data.result == 1) {
                            this.$message.success(res.data.msg);
                            window.history.back(-1)
                        } else {
                            this.$message.error(res.data.msg);
                        }
                    }
                })
            },
            //添加数据
            postAddgroup() {
                this.$http.post("{!!yzWebFullUrl('member.member-group.store')!!}", {
                    group: {
                        group_name: this.form.groupName
                    }
                }).then(res => {
                    console.log(res);
                    if (res.data.result === 1) {
                        this.$message.success("添加分组成功")
                        window.history.back(-1)
                    } else {
                        this.$message.error("添加分组失败")
                    }
                    // console.log(res);
                })
            },
            //提交
            submit() {
                if (this.isid) {
                    this.isCount++
                    //提交修改
                    // this.postSetUpdateGroup(this.id);
                    this.postUpdateGroup(this.id)
                } else {
                    //提交添加
                    this.postAddgroup();
                }
            },
            //跳转
            returnList() {
                window.history.back(-1)
                // window.location.href="http://127.0.0.2/web/index.php?c=site&a=entry&m=yun_shop&do=8113&route=member.member-group.index";
            }
        }
    })
</script>@endsection