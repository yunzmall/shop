@extends('layouts.base')

@section('content')
    <style>
        .content{
            background: #eff3f6;
            padding: 10px!important;
        }
        .con{
            padding-bottom:70px;
            position:relative;
            border-radius: 8px;
            min-height:100vh;
            background-color:#fff;
        }
        .el-collapse{
            border-top:none;
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
        .confirm-btn{
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
        .el-form-item__label{
            margin-right:30px;
        }
        b{
            font-size:14px;
        }
        .el-checkbox{
            margin-right:0;
        }
        .vue-crumbs a {
            color: #333;
        }
        .vue-crumbs a:hover {
            color: #29ba9c;
        }
        .vue-crumbs {
            margin: 0 20px;
            font-size: 14px;
            color: #333;
            font-weight: 400;
            padding-bottom: 10px;
            line-height: 32px;
        }

        .el-checkbox__inner{
            border:solid 1px #56be69!important;
            z-index:0!important;
        }
    </style>
    <div id='re_content' >
        <div class="vue-crumbs">
            <a @click="goBack">系统</a> > 权限管理 > 角色管理 > 添加角色
        </div>
        <div class="con">
            <div class="setting">
                <el-form ref="form" :model="form" label-width="15%">
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>角色信息</b></div>
                        <el-form-item label="角色">
                            <el-input v-model="form.name"  style="width:60%;"></el-input>
                        </el-form-item>
                        <el-form-item label="是否启用"  >
                            <template>
                                <el-switch
                                        v-model="form.status"
                                        active-value="2"
                                        inactive-value="1"
                                >
                                </el-switch>
                            </template>
                            <div>提示：如果禁用，则当前角色的操作员全部会禁止使用</div>
                        </el-form-item>

                    </div>
                    <div style="background: #eff3f6;width:100%;height:15px;"></div>
                    <div class="block">
                        <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>角色权限</b></div>
                        <el-checkbox v-model="ischeckedAll"  style="margin-left:30px;" @change="checkedAll"><b>一键勾选</b></el-checkbox>
                        <el-collapse v-model="active" accordion>
                            <template v-for="(item,index) in permissions" class="warp">
                                <el-collapse-item  :name="index">
                                    <template slot="title">
                                        <el-checkbox v-model="item.checked" @change="firstUpdate(item)" style="margin-left:30px;"><b>[[item.name]]</b></el-checkbox>
                                    </template>
                                    <div v-for="(list,index) in item.child" style="font-size:0;display:flex;border-bottom:1px solid #EBEEF5;padding:10px 0;">
                                        <div style="width:20%;"><el-checkbox v-model="list.checked" @change="secondUpdate(item,list)" style="width:200px;margin-left:45px;"><b>[[list.name]]</b></el-checkbox></div>
                                        <div style="flex:1;display:flex;flex-wrap:wrap;">
                                            <div v-for="(obj,index) in list.child" style="width:200px;display:inline-block;">
                                                <el-checkbox v-model="obj.checked" @change="thirdUpdate(item,list,obj)">[[obj.name]]</el-checkbox>
                                            </div>
                                        </div>
                                    </div>
                                </el-collapse-item>
                            </template>
                        </el-collapse>
                    </div>
            </div>
            <div class="confirm-btn">
                <el-button type="primary" @click="submit">提交</el-button>
            </div>
            </el-form>
        </div>
    </div>

    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                let role = {!!json_encode($role)?:'{}' !!}
                    let permissions={!!json_encode($permissions)?:'{}' !!}
                    let userPermissions={!!json_encode($userPermissions)?:'{}' !!}
                    return {
                        id:role.id,
                        role:role,
                        userPermissions:userPermissions,
                        active:'0',
                        activeName: 'first',
                        permissions:permissions,
                        form:{
                            name:role.name,
                            status:String(role.status)
                        },
                        arr:[],
                        ischeckedAll:false,//是否全选
                    }
            },
            mounted () {
                this.getChecked();
            },
            methods: {
                checkedAll(){
                    this.permissions.forEach(item=>{
                        item.checked = this.ischeckedAll;
                        this.firstUpdate(item)
                    })
                },
                goBack() {
                    window.location.href = `{!! yzWebFullUrl('setting.shop.index') !!}`;
                },
                getChecked(){
                    this.permissions.forEach((item,index) => {
                        if(this.userPermissions.includes(item.key_name)){
                            item.checked=true
                        }else{
                            item.checked=false
                        }
                        if(item.child&&item.child.length>0){
                            item.child.forEach((list,index)=>{
                                if(this.userPermissions.includes(list.key_name)){
                                    list.checked=true
                                }else{
                                    list.checked=false
                                }
                                if(list.child&&list.child.length>0){
                                    list.child.forEach((obj,index)=>{
                                        if(this.userPermissions.includes(obj.key_name)){
                                            obj.checked=true
                                        }else{
                                            obj.checked=false
                                        }
                                    })
                                }
                            })
                        }
                    });
                    this.$forceUpdate()
                },
                firstUpdate(item){
                    if(item.checked){
                        if(item.child&&item.child.length>0){
                            item.child.forEach((list,index)=>{
                                list.checked=true
                                if(list.child&&list.child.length>0){
                                    list.child.forEach((obj,index)=>{
                                        obj.checked=true
                                    })
                                }
                            })
                        }
                    }else if(!item.checked){
                        if(item.child&&item.child.length>0){
                            item.child.forEach((list,index)=>{
                                list.checked=false
                                if(list.child&&list.child.length>0){
                                    list.child.forEach((obj,index)=>{
                                        obj.checked=false
                                    })
                                }
                            })
                        }
                    }
                },
                secondUpdate(item,list){
                    if(list.checked){
                        if(list.child&&list.child.length>0){
                            list.child.forEach((obj,index)=>{
                                obj.checked=true
                            })
                        }
                    }else if(!list.checked){
                        if(list.child&&list.child.length>0){
                        list.child.forEach((obj,index)=>{
                                    obj.checked=false
                        })
                        }
                    }
                    let a=item.child.some((block,index)=>{
                        return block.checked==true
                    })
                    if(a){
                        item.checked=true
                    }else{
                        item.checked=false
                    }
                    this.$forceUpdate()
                },
                thirdUpdate(item,list,obj){
                    if(list.child.length>0){
                        let a=list.child.some((obj,index)=>{
                            return obj.checked==true
                        })

                        if(a){
                            list.checked=true
                        }else{
                            list.checked=false
                        }
                    }
                    if(item.child.length>0){
                        let b=item.child.some((list,index)=>{
                            return list.checked==true
                        })

                        if(b){
                            item.checked=true
                        }else{
                            item.checked=false
                        }
                    }
                    this.$forceUpdate()
                },
                submit() {
                    this.arr=[];
                    this.permissions.forEach((item,index) => {
                        if(item.checked){
                            this.arr.push(item.key_name)
                            if(item.child&&item.child.length>0){
                                item.child.forEach((list,index)=>{
                                    if(list.checked){
                                        this.arr.push(list.key_name)
                                        if(list.child&&list.child.length>0){
                                            list.child.forEach((obj,index)=>{
                                                if(obj.checked){
                                                    this.arr.push(obj.key_name)
                                                }
                                            })
                                        }
                                    }
                                })
                            }
                        }
                    });
                    this.$http.post('{!! yzWebFullUrl('user.role.update') !!}',{YzRole:this.form,perms:this.arr,id:this.id}).then(function (response){
                        if (response.data.result) {
                            this.$message({message: "提交成功",type: 'success'});
                            window.location.href='{!! yzWebFullUrl('user.role.index') !!}';
                            this.loading = false;
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
                },
            },
        });
    </script>
@endsection
