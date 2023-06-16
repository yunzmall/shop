@extends('layouts.base')
@section('content')
<link rel="stylesheet" href="{{static_url('css/public-number.css')}}">
<style>
.panel{
    margin-bottom:10px!important;
    padding-left: 20px;
    border-radius: 10px;
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
    padding-bottom:40px;
    position:relative;
    min-height:100vh;
    background-color:#fff;
    border-radius: 8px;
}
.con .setting .block{
    padding:10px;
    background-color:#fff;
    border-radius: 8px;
  
}
.con .setting .block .title{
    display:flex;
    align-items:center;
    margin-bottom:15px;
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

b{
    font-size:14px;
}
.head{
  width: 100%;
	height: 43px;
	background-color: #f6f7f9;
  display:flex;
  align-items:center;
  margin:0 auto;
}
.left{
  width:42%;
  height:100%;
  float:left;
}
.right{
  width:58%;
  height:100%;
  float:left;
  border-left:solid 1px #f6f7f9;
}
.block:after{
  content:'';
  display:block;
  clear:both;
}
</style>
<div id='re_content' >

    <div class="con">
        <div class="setting">
            <div class="block">
            <div class="title"><span style="width: 4px;height: 18px;background-color: #29ba9c;margin-right:15px;display:inline-block;"></span><b>添加微信自定义模板</b><span style="margin-left:20px;font-size:12px;">请选择对应模板变量, 否则消息通知内容有误</span></div>
            <el-form ref="form" :model="form" label-width="20%">
              <div class="left">  
                <div style="margin-left:20px;margin-bottom:20px;">选择模板区域</div>
                <el-form-item label="添加模板">
                    <el-input  v-model="templateidshort" style="width:70%;" ></el-input>
                    <el-button type="primary" @click="addQuick">快速添加</el-button>
                </el-form-item>
                <el-form-item label="选择模板">
                    <template>
                        <el-select style="width:90%;" v-model="form.template_id" @change="Select" filterable>
                            <el-option  

                                    v-for="item in tmp_list"
                                    :label="item.title"
                                    :value="item.template_id">
                            </el-option>
                        </el-select>
                    </template>
                </el-form-item>
                <el-form-item label="模板展示" v-if="content">
                    <div style="width: 50%;min-height:129px;background-color: #f6f7f9;border-radius: 4px;box-sizing:border-box;padding:8px;" v-html="content">
                    </div>
                </el-form-item>
                <el-form-item label="模板变量类型" >
                    <template>
                        <el-select style="width:90%;" v-model="value" @change="paramSelect" filterable>
                            <el-option
                                    v-for="item in temp"
                                    :label="item.title"
                                    :value="item.value">
                            </el-option>
                        </el-select>
                    </template>
                    <div v-if="param.length>0" style="width:90%;">提示：点击变量后会自动插入选择的文本框的焦点位置，在发送给粉丝时系统会自动替换对应变量值</div>
                </el-form-item>
                <el-form-item label="类型选择" v-if="param.length>0" >
                  <div style="width:90%;">
                  <template v-for="(item,index,key) in param">
                    <el-tag   style="margin-right:10px;margin-bottom:10px;"  @click="add(item)">[[item]]</el-tag>
                  </template>
                  </div>
                </el-form-item>
              </div>
            </el-form>
            <el-form ref="form" :model="form" label-width="15%">
              <div class="right">
                <div style="margin-left:20px;margin-bottom:20px;">模板设置变量区域</div>
                <el-form-item label="模板名称">
                    <el-input v-model="form.title"  @focus="addName('title')" style="width:80%;" ref="title"></el-input>
                </el-form-item>
                <el-form-item label="模板消息ID">
                    <el-input v-model="form.template_id"  style="width:80%;" disabled></el-input>
                </el-form-item>
{{--                <el-form-item label="头部标题">--}}
{{--                    <el-input  type="textarea"  v-model="form.first"  @focus="addName('first')" style="width:80%;" ref="first"></el-input>--}}
{{--                </el-form-item>--}}
{{--                <el-form-item label="文字颜色">--}}
{{--                <div><el-color-picker v-model="form.first_color" @change="change"></el-color-picker></div>--}}
{{--                </el-form-item>--}}
                <el-form-item label="键值设置">
                  <div class="tabel-data" style="width:80%;">
                    <div class="head">
                      <div style="width:33%;text-align:center;">键名</div>
                      <div style="width:67%;text-align:center;">描述</div>
{{--                      <div style="width:25%;text-align:center;">颜色</div>--}}
                     
                    </div>
                    <div class="data">
                     <div v-for="(item,index,key) in block" style="display:flex;align-items:center;margin-top:20px;">
                        <div style="width:33%;text-align:center;">[[item.title]]</div>
                        <div style="width:67%;text-align:center;">
                          <el-input  type="textarea"  v-model="item.value"  @focus="addName(item)" ></el-input>
                        </div>
{{--                          <div style="width:25%;text-align:center;display:flex;align-items:center;justify-content:center;"><el-color-picker v-model="item.color" @change="change"></el-color-picker></div>--}}
                      </div>
                        <span>注意：单个描述内容建议不超过20个字，且不支持换行</span>
                    </div>
                  </div>
                </el-form-item>
                </el-form-item>
              
{{--                <el-form-item label="尾部描述">--}}
{{--                    <el-input  type="textarea"  v-model="form.remark"  @focus="addName('remark')" style="width:80%;" ref="remark"></el-input>--}}
{{--                </el-form-item>--}}
{{--                <el-form-item label="文字颜色">--}}
{{--                <div><el-color-picker v-model="form.remark_color" @change="change"></el-color-picker></div>--}}
{{--                </el-form-item>--}}
                <el-form-item label="跳转链接地址">
                    <el-input v-model="form.news_link" @focus="addName('new_link')" style="width:70%;" ref="new_link"></el-input><el-button @click="show=true" style="margin-left:10px;">选择链接</el-button>
                </el-form-item>
              </div>  
            </el-form>
            </div>
        </div>
        <div class="confirm-btn">
            <el-button type="primary" @click="submit">提交</el-button>
        </div>
        <pop :show="show" @replace="changeProp1" @add="parHref"></pop>
    </div>
</div>
@include('public.admin.pop') 
<script>
    var vm = new Vue({
        el: "#re_content",
        delimiters: ['[[', ']]'],
        data() {
    
           return {
                info:'',
                id:'',
                templateidshort:'',
                name:'',
                show:false,
                tmp_list:[],
                content:'',
                temp:[],
                param:[],
                block:[],
                value:'',
                form:{
                  tp_kw:[],
                  tp_color:[],
                  tp_value:[],
                  title:'',
                  template_id:'',
                  // first:'',
                  // first_color:"",
                  // remark:'',
                  // remark_color:"",
                  news_link:'',
                },
           }
        },
        mounted () {
          this.UrlSearch()
           this.gettemp();
           
        },
        methods: {
        UrlSearch() {
          
            var name, value;
            var str = location.href; //获取到整个地址
            var num = str.indexOf("?")
            str = str.substr(num + 1); //取得num+1后所有参数，这里的num+1是下标 str.substr(start [, length ]
            var arr = str.split("&"); //以&分割各个参数放到数组里
            for (var i = 0; i < arr.length; i++) {
            num = arr[i].indexOf("=");
            if (num > 0) {
            name = arr[i].substring(0, num);
            value = arr[i].substr(num + 1);
            this.id = value;
            }
            }
          },
          addQuick(){
            this.$http.post("{!! yzWebUrl('setting.wechat-notice.addTmp') !!}",{templateidshort:this.templateidshort}).then(function (response){
                       if (response.data.result) {
                        this.$message({message: response.data.msg,type: 'success'});
                        window.location.href='{!! yzWebFullUrl('setting.diy-temp.index') !!}'
                        }
                        else {
                            this.$message({message: response.data.msg,type: 'error'});
                            loading.close();
                            location.reload();
                        }
                      },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
            })
          },
          change(val){
            this.$forceUpdate()
          },
         
          add(item){
            if(!this.name){
              return 
            }
            if(this.name instanceof Object){
              this.block.forEach((list,index,key)=>{
                if(list.title==this.name.title){
                  this.block[index].value=[this.block[index].value]+`[${item}]`
                }
              })
            }else{
              this.form[this.name]=this.$refs[this.name].value+`[${item}]`
            }
          },
          addName(name){
              this.name=name
          },
            //   弹窗显示与隐藏的控制
          changeProp1(item){
                        this.show=item;
                    },
                   // 当前链接的增加
          parHref(child,confirm){
                        this.show=confirm;
                        this.form.news_link=child;

            },
            paramSelect(val){
              this.temp.forEach((item,index,key)=>{
                if(item.value==val){
                  this.param=this.temp[index].param
                }
              })
            },
            Select(val){
              this.block=[];
              this.tmp_list.forEach((item,index,key)=>{
                if(item.template_id==val){
                  this.content=this.tmp_list[index].content
                  this.form.template_id=item.template_id
                  this.form.title=item.title
                  this.tmp_list[index].contents.slice(1,this.tmp_list[index].contents.length-1).forEach((list,index,key)=>{
                      this.block.push({title:list,color:"#101111",value:''})
                  })
                }
              })
              
            },
            getData(){
                 this.$http.post('{!! yzWebFullUrl('setting.diy-temp.edit') !!}',{id:this.id}).then(function (response){
                       if (response.data.result) {
                            this.info=response.data.data.temp
                            this.info.data.forEach((item,index,key)=>{
                              this.block.push({title:item.keywords,color:item.color,value:item.value})
                            })
                            // this.form.first_color=this.info.first_color
                            // this.form.first=this.info.first
                            this.form.news_link=this.info.news_link
                            // this.form.remark=this.info.remark
                            // this.form.remark_color=this.info.remark_color
                            this.form.template_id=this.info.template_id
                            this.form.title=this.info.title
                            this.temp=response.data.data.wechat_temp
                            this.tmp_list.forEach((item,index,key)=>{
                              if(item.template_id==this.form.template_id){
                                this.content=this.tmp_list[index].content
                              }
                            })
                            this.$forceUpdate()
                        }
                        else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                      },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                     })
             },
             gettemp(){
                 this.$http.post('{!! yzWebFullUrl('setting.wechat-notice.returnJson') !!}').then(function (response){
                       if (response.data.result) {
                           this.tmp_list=response.data.data.tmp_list
                           this.getData();
                           this.$forceUpdate()
                        }
                        else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                      },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                     })
             },
            submit() {
                this.block.forEach((item,index,key)=>{
                  this.form.tp_kw.push(item.title)
                  this.form.tp_value.push(item.value)
                  this.form.tp_color.push(item.color)
                })
                    this.$http.post('{!! yzWebFullUrl('setting.diy-temp.edit') !!}',{'temp':this.form,id:this.id}).then(function (response){
                        this.$message({message: response.data.msg,type: 'success'});
                        window.location.href='{!! yzWebFullUrl('setting.diy-temp.index') !!}'
                     },function (response) {
                        this.$message({message: response.data.msg,type: 'error'});
                    })
            },
        },
    });
</script>
@endsection
