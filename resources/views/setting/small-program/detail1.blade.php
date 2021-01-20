@extends('layouts.base')
@section('title', '微信模板管理')
@section('content')
<style>
.panel{  
    margin-bottom:10px!important;
    border-radius: 10px;
    padding-left: 20px;
}   
.panel .active a {
	background-color: #29ba9c!important;
	border-radius: 18px!important;
    color:#fff;
}
.panel a{
    border:none!important;
    background-color:#fff!important;
}
.content{
    background: #eff3f6;
    padding: 10px!important;
}
.con{
    position:relative;
    border-radius: 8px;
    min-height:100vh;
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
.confirm-btn{
    width: 100%;
    position:absolute;
    bottom:0;
    left:0;
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
.add{
    width: 154px;
	height: 36px;
	border-radius: 4px;
    border: solid 1px #29ba9c;
    color:#29ba9c;
    display:flex;
    align-items:center;
    justify-content:center;
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
  line-height: 32px;
}
.el-table--border::after, .el-table--group::after, .el-table::before{
    background-color:#fff;
}
</style>
<div id='re_content' >
@include('layouts.newTabs')
    <div class="con">
        <div class="setting">
            <div class="block">
            <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>模板列表</b><i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                placement="bottom-start"
                title="提示"
                width="400"
                trigger="hover"
                content="小程序公众平台服务类目请添加商家自营 > 美妆/洗护。
                点击模版消息后方开关按钮即可开启默认模版消息，无需进行额外设置。
                ">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i></div></div>
            </div>
        <el-button type="primary" size="mini" style="margin-left:10px;" @click="synchronization">同步消息模板</el-button>
    <el-table
      :data="list"
      style="width: 100%;padding:0 10px;"
      >
      <el-table-column
        align="center"
        label="序号"
       >
        <template slot-scope="scope" >
            [[scope.row.id]]
        </template>
      </el-table-column>
      <el-table-column
        prop="title"
        align="center"
        label="模板名称"
        >
      </el-table-column>
      <el-table-column
        align="center"
        label="操作"
        >
        <template slot-scope="scope" >
        <a :href="'{{ yzWebFullUrl('setting.small-program.see', array('tmp_id' => '')) }}'+[[scope.row.priTmplId]]" style="color:#999"><i class="iconfont icon-ht_operation_perview" style="font-size:16px;"></i></a>
        </template>
      </el-table-column>
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
            let list = {!!json_encode($list)?:'{}' !!}
            console.log(list)
           return {
                list:list,
                activeName: 'one',
                multipleSelection:[]
           }
        },
        mounted () {
            this.list.forEach((item,index,key)=>{
                item.id=index+1
            })
        },
        methods: {
              handleSelectionChange(val) {
                    this.multipleSelection = val;
              },
              del(scope,rows){
                    rows.splice(scope.$index, 1);
                    let json={
                        id:scope.row.id
                    }
                    this.$http.post('{!! yzWebFullUrl('setting.slide.deleted') !!}',json).then(function (response){
                        if (response.data.result) {
                            this.$message({message:"删除成功！",type:"success"});
                            this.loading = false;
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        },function (response) {
                            console.log(response);
                            this.loading = false;
                        }
                    );
                },
              synchronization(){
                    this.$http.post('{!! yzWebFullUrl('setting.small-program.synchronization') !!}').then(function (response){
                        if (response.data.result) {
                            this.$message({message:response.data.msg,type:"success"});
                            this.loading = false;
                        }else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        },function (response) {
                            console.log(response);
                            this.loading = false;
                        }
                    );
              },
        },
    });
</script>
@endsection

