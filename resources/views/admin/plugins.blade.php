@extends('layouts.base')
@section('title', trans('插件管理'))
@section('content')
    <style>
        .content{
            background: #eff3f6;
            padding: 10px!important;
        }
        .con{
            padding-bottom:20px;
            position:relative;
            border-radius: 8px;
            min-height: 100vh;
            background: #fff;
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
            justify-content:space-between;
        }
        .el-form-item{
            padding-left:300px;
            margin-bottom:10px!important;
        }

        .el-form-item__label{
            margin-right:30px;
        }
        .add{
            width: 155px;
            height: 36px;
            border-radius: 4px;
            color: #ffffff;
            display:flex;
            align-items:center;
            justify-content:center;
            background-color: #29ba9c;
        }
        .el-table--fit{
            margin-top:-10px;
        }
        b{
            font-size:14px;
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
        }
        .el-table--border::after, .el-table--group::after, .el-table::before{
            background-color:#fff;
        }
    </style>
    <div id='re_content' >
        <template>

        </template>
        <div class="con">
            <div class="setting">
                <div class="block">
                    <div class="title"><div style="display:flex;align-items:center;"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>插件管理</b><span style="color: #999999;font-size:12px;display:inline-block;margin-left:16px;">如存在未授权插件，您可能正在使用盗版插件，为了保障您的权益，请尽快联系客服处理！</span></div><span></div><el-button type="primary"><a href="{{yzWebUrl('plugin.plugins-market.Controllers.new-market.show')}}" style="color:#fff;">插件安装/升级</a></el-button></div>
                    <div>
                        <div>
                            <el-input v-model="search_form.title"  style="width:15%;margin-right:15px;"></el-input>
                            <template>
                                <el-select v-model="search_form.permit_status"  style="margin-right:15px;">
                                    <el-option
                                            v-for="item in permit"
                                            :key="item.value"
                                            :label="item.label"
                                            :value="item.value">
                                    </el-option>
                                </el-select>
                            </template>
                            <template>
                                <el-select v-model="search_form.status"  style="margin-right:15px;">
                                    <el-option
                                            v-for="item in action"
                                            :key="item.value"
                                            :label="item.label"
                                            :value="item.value">
                                    </el-option>
                                </el-select>
                            </template>
                            <template>
                                <el-select v-model="search_form.update_status"  style="margin-right:15px;">
                                    <el-option
                                            v-for="item in update"
                                            :key="item.value"
                                            :label="item.label"
                                            :value="item.value">
                                    </el-option>
                                </el-select>
                            </template>
                            <el-button type="primary" @click="search">搜索</el-button>
                        </div>
                        <div  style="margin-top:20px;">
                            <el-button type="primary" class="btn-one" @click="allChoose">全选</el-button>
                            <el-button type="primary" class="btn-one" @click="allOpen">批量启用</el-button>
                            <el-button type="danger" class="btn-two" @click="allClose">批量禁用</el-button>
                            @if(YunShop::app()->role == 'founder')  {{--判断是不是超级管理员--}}
                            <el-button type="danger" class="btn-two" @click="allDelete">批量卸载</el-button>
                            @endif
                        </div>
                    </div>
                </div>
                <div style="background: #eff3f6;width:100%;height:15px;"></div>
                <div class="block" style="padding-top:20px;">
                    <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>插件列表</b><span style="color: #999999;font-size:12px;display:inline-block;margin-left:16px;"><span>插件总数：{{$countPlugin}}   已授权插件：{{$countPlugin-$unPermitPlugin}}     未授权插件：{{$unPermitPlugin}}</span></div></div>
                </div>
                <template style="margin-top:-10px;">
                    <el-table
                            :data="list"
                            style="padding:0 10px"
                            style="width: 100%">
                        <el-table-column
                                align="center"
                                label="选择"
                                width="100">
                            <template slot-scope="scope">
                                <el-checkbox  v-model="scope.row.choose" @change="getStatus(scope.row)" :disabled = " scope.row.name=='plugins-market' " ></el-checkbox>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="title"
                                align="center"
                                label="插件名称"
                                label="100">
                        </el-table-column>
                        <el-table-column
                                prop="version"
                                align="center"
                                label="当前版本"
                                label="100">
                        </el-table-column>
                        <el-table-column
                                prop="new_version"
                                align="center"
                                label="最新版本"
                                label="100">
                            <template slot-scope="scope" >
                                [[scope.row.new_version]]
                                <span style="margin-left:5px;color:#29BA9C;" v-if="scope.row.update">可升级</span>
                            </template>
                        </el-table-column>
                        <el-table-column
                                prop="permit_status"
                                align="center"
                                label="是否授权"
                                label="100">
                            <template slot-scope="scope" >
                                <div v-if="scope.row.permit_status=='已授权'" style="color:#29BA9C;">已授权</div>
                                <div v-if="scope.row.permit_status=='未授权'" style="color:red;">未授权</div>
                            </template>
                        </el-table-column>
                        <el-table-column
                                align="center"
                                label="状态"
                                label="100">

                            <template slot-scope="scope" >
                                <el-switch
                                        @change="changeStatus(scope.row)"
                                        v-model="scope.row.status"
                                >
                                </el-switch>
                            </template>

                        </el-table-column>
                        @if(YunShop::app()->role == 'founder')  {{--判断是不是超级管理员--}}
                        <el-table-column
                                align="center"
                                label="操作"
                                label="100">
                            <template slot-scope="scope" >
                                <el-button type="danger" @click="deletePlugin(scope.row)"  :disabled = " scope.row.name=='plugins-market' " >卸载</el-button>
                            </template>

                        </el-table-column>
                        @endif
                    </el-table>
                </template>
            </div>
        </div>
    </div>
    <script>
        var vm = new Vue({
            el: "#re_content",
            delimiters: ['[[', ']]'],
            data() {
                let data = {!! $data ?: '[]' !!}
                    return {
                        is_all_choose:false,
                        activeName: 'one',
                        all_loading:false,
                        page:1,
                        page_size:1,
                        loading:false,
                        search_loading:false,
                        search_form:{},
                        real_search_form:{},
                        value:'',
                        form:{
                            link:'',
                            checkList:[],
                        },
                        permit: [
                            {
                                value: '已授权',
                                label: '已授权'
                            },
                            {
                                value: '未授权',
                                label: '未授权'
                            },
                        ],
                        permit: [
                            {
                                value: '已授权',
                                label: '已授权'
                            },
                            {
                                value: '未授权',
                                label: '未授权'
                            },
                        ],
                        permit: [
                            {
                                value: '已授权',
                                label: '已授权'
                            },
                            {
                                value: '未授权',
                                label: '未授权'
                            },
                        ],
                        action: [
                            {
                                value: 'enable',
                                label: '启用'
                            },
                            {
                                value: 'disable',
                                label: '禁用'
                            },
                        ],
                        update: [
                            {
                                value: '可升级',
                                label: '可升级'
                            },
                        ],
                        list: data,
                    }
            },
            created(){
                let arr =[]
                this.list.forEach((item,index)=>{
                    item.choose=false;
                    if(Number(String(item.new_version).split('.').join(''))>Number(String(item.version).split('.').join(''))){
                        item.update=true
                    }else{
                        item.update=false
                    }
                    arr.push(item)
                    this.$forceUpdate()
                })
                this.list=arr;
            },
            mounted () {
            },
            methods: {
                getStatus(){
                    let arr=[]
                    this.list.forEach((item,index)=>{
                        arr.push(item)
                    })
                    this.list=arr
                    this.$forceUpdate()
                },
                changeStatus(item){
                    let json={
                        name:item.name,
                        action:item.status?'enable':'disable'
                    }
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});

                    this.$http.post('{!! yzWebFullUrl('plugins.manage') !!}',json).then(function (response){
                            if (response.data.result) {
                                this.$message({message:response.data.msg,type:"success"});
                                this.loading = false;
                            }else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            loading.close();
                            let a = window.location.href+"&"+Math.floor((Math.random()*10)+1);;
                            window.location.href = a ;
                        },function (response) {
                            console.log(response);
                            this.loading = false;
                        }
                    );
                },
                allChoose() {
                    let arr=[]
                    if(!this.is_all_choose){
                        this.list.forEach((item,index)=>{
                            if(item.name !== 'plugins-market'){
                                item.choose=true
                            }
                            arr.push(item)
                            this.$forceUpdate()
                        })
                        this.is_all_choose=true
                    }else{
                        this.list.forEach((item,index)=>{
                            if(item.name !== 'plugins-market'){
                                item.choose=false
                            }
                            arr.push(item)
                            this.$forceUpdate()
                        })
                        this.is_all_choose=false
                    }
                    this.list=arr
                    this.$forceUpdate()
                },
                allClose(){
                    let vals=[]
                    this.list.forEach((item,index)=>{
                        if(item.choose){
                            vals.push(item.name)
                        }
                    })
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('plugins.batchMange') !!}',{names:vals.join(','),action:'disable'}).then(function (response){
                            if (response.data.result) {
                                this.$message({message:response.data.msg,type:"success"});
                                this.loading = false;
                            }else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            loading.close();
                            location.reload();
                        },function (response) {
                            console.log(response);
                            this.loading = false;
                        }
                    );
                },
                allOpen(){
                    let vals=[]
                    this.list.forEach((item,index)=>{
                        if(item.choose){
                            vals.push(item.name)
                        }
                    })
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post('{!! yzWebFullUrl('plugins.batchMange') !!}',{names:vals.join(','),action:'enable'}).then(function (response){
                            if (response.data.result) {
                                this.$message({message:response.data.msg,type:"success"});
                                this.loading = false;
                            }else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            loading.close();
                            location.reload();
                        },function (response) {
                            console.log(response);
                            this.loading = false;
                        }
                    );
                },
                allDelete(){
                    this.$confirm('卸载插件后,将删除插件文件、整站无法使用该插件!如插件已经停止供应，将无法再次安装! 是否继续?', '温馨提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let vals=[]
                        this.list.forEach((item,index)=>{
                            if(item.choose){
                                vals.push(item.name)
                            }
                        })
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                        this.$http.post('{!! yzWebFullUrl('plugins.batchMange') !!}',{names:vals.join(','),action:'delete'}).then(function (response){
                                if (response.data.result) {
                                    this.$message({message:response.data.msg,type:"success"});
                                    this.loading = false;
                                }else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                }
                                loading.close();
                                location.reload();
                            },function (response) {
                                console.log(response);
                                this.loading = false;
                            }
                        );
                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消卸载'
                        });
                    });


                },
                search() {
                    this.search_loading = true;
                    this.$http.post('{!! yzWebFullUrl('plugins.get-plugin-data') !!}',{search:this.search_form}
                    ).then(function (response) {
                            if (response.data.result){
                                this.list=response.data.data
                                let arr=[]
                                this.list.forEach((item,index)=>{
                                    item.choose=false;
                                    if(Number(String(item.new_version).split('.').join(''))>Number(String(item.version).split('.').join(''))){
                                        item.update=true
                                    }else{
                                        item.update=false
                                    }
                                    arr.push(item)
                                    this.$forceUpdate()
                                })

                                this.list=arr;
                                this.real_search_form=Object.assign({},this.search_form);
                            }
                            else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            this.search_loading = false;
                        },function (response) {
                            this.search_loading = false;
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                    );
                },
                deletePlugin(item){
                    this.$confirm('卸载插件后,将删除插件文件、整站无法使用该插件!如插件已经停止供应，将无法再次安装! 是否继续?', '温馨提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        let json={
                            name:item.name,
                            action:'delete'
                        }
                        let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                        this.$http.post('{!! yzWebFullUrl('plugins.manage') !!}',json).then(function (response){
                                if (response.data.result) {
                                    this.$message({message:response.data.msg,type:"success"});
                                    this.loading = false;
                                }else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                }
                                loading.close();
                                location.reload();
                            },function (response) {
                                console.log(response);
                                this.loading = false;
                            }
                        );
                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '已取消卸载'
                        });
                    });
                },
            },
        });
    </script>
@endsection

