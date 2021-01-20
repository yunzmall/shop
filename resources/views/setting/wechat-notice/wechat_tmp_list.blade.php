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
    margin-bottom:10px;
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
.el-table--fit{
    margin-top:-10px;
}
b{
    font-size:14px;
}
.el-checkbox__inner{
    border:solid 1px #56be69!important;
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
            <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>添加模板</b>
            <i class="iconfont icon-ht_tips" style="font-size:16px;color:#ff9b19;margin-left:16px;" slot="reference">
            <el-popover
                placement="bottom-start"
                title="提示"
                width="400"
                trigger="hover"
                content="请将公众平台模板消息所在行业选择为：IT科技/互联网|电子商务   其他/其他，所选行业不一致将会导致模板消息不可用。
                您的公众平台模板消息目前所属行业为：IT科技/互联网|电子商务   IT科技/IT软件与服务。
                当前列表内的模板消息为您已申请的模板消息，您可以点击查看详情或者删除处理。">
                <el-button slot="reference" style="opacity: 0;margin-left:-10px;"></el-button>
            </el-popover>
            </i>
            </div>
            </div>
                <el-input  style="width:15%;margin-right:16px;" placeholder="请输入模板编号" v-model="tempcode"></el-input><el-button  type="primary" @click="addWechat">添加微信模板</el-button>
            </div>
            <div style="background: #eff3f6;width:100%;height:15px;"></div>
            <div class="block">
            <div class="title"><div style="display:flex;align-items:center;"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>模板列表</b><el-button style="margin-left:15px;" size="mini" type="primary">删除</el-button></div></div>
            </div>
    <el-table
      :data="list"
      style="width: 100%;padding:0 10px;"
      @selection-change="handleSelectionChange"
      >
      <el-table-column
      label="全选"
      type="selection"
      align="center"
      >
    </el-table-column>
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
        prop="primary_industry"
        align="center"
        label="	所属行业"
        >
      </el-table-column>
      <el-table-column
        align="center"
        label="操作"
        >
        <template slot-scope="scope" >
        <a :href="'{{ yzWebFullUrl('setting.wechat-notice.see', array('tmp_id' => '')) }}'+[[scope.row.template_id]]" style="color:#999;margin-right:35px;"><i class="iconfont icon-ht_operation_perview" style="font-size:16px;"></i></a>
        <a style="color:#999"><i class="iconfont icon-ht_operation_delete" style="font-size:16px;" @click="del(scope, list)"></i></a>
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
           return {
                list:[],
                tempcode:'',
                activeName: 'one',
                multipleSelection:[]
           }
        },
        mounted () {
              this.getData();
        },
        methods: {
              handleSelectionChange(val) {
                    this.multipleSelection = val;
              },
            //   allDelete(){
            //     rows.splice(scope.$index, 1);
            //         let json={
            //             id:scope.row.id
            //         }
            //         this.$http.post('{!! yzWebFullUrl('setting.wechat-notice.del') !!}',json).then(function (response){
            //             if (response.data.result) {
            //                 this.$message({message:"删除成功！",type:"success"});
            //                 this.loading = false;
            //             }else {
            //                 this.$message({message: response.data.msg,type: 'error'});
            //             }
            //             },function (response) {
            //                 console.log(response);
            //                 this.loading = false;
            //             }
            //         );
            //   },
              addWechat(){
                    this.$http.post('{!! yzWebFullUrl('setting.wechat-notice.addTmp') !!}',{ templateidshort:this.tempcode}).then(function (response){
                            if (response.data.result) {
                                this.$message({message: response.data.msg,type: 'success'});
                            }else{
                              this.$message({message: response.data.msg,type: 'error'});
                            }            
                         },function (response) {
                            this.$message({message: response.data.msg,type: 'error'});
                      })
              },
              del(scope,rows){
                    rows.splice(scope.$index, 1);
                    let json={
                        id:scope.row.template_id
                    }
                    this.$http.post('{!! yzWebFullUrl('setting.wechat-notice.del') !!}',json).then(function (response){
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
            getData(){
                    this.$http.post('{!! yzWebFullUrl('setting.wechat-notice.index') !!}').then(function (response){
                            if (response.data.result) {
                              this.list=response.data.data.list;
                              this.list.forEach((item,index,key)=>{
                                  item.id=index+1
                              })
                            }else{
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

