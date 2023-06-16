@include('public.admin.box-item')
<style scoped>
  .search-box{display: flex;flex-wrap: wrap;}
  .search-item{width: 240px;margin: 10px;}
  .page{width: calc(100% - 275px);position: fixed;bottom: 0;z-index: 199;box-sizing: border-box;box-shadow: 0px -1px 10px rgb(0 0 0 / 10%);background-color: #fff;height: 60px;display: flex;justify-content: center;align-items: center;border-radius: 10px 10px 0 0;}
</style>
<template id="table-template">
  <div>
    <box-item :text="searchText">
      <div class="search-box">
        <template v-for="(item,i) in searchList">
          <el-input v-model="search[item.key]" :placeholder="item.p" class="search-item" clearable  @keyup.enter.native="searchValue" v-if="!item.type || item.type == 'input'"></el-input>
          <el-select v-model="search[item.key]" :placeholder="item.p" class="search-item" clearable v-else-if="item.type == 'select'">
            <el-option v-for="(option,index) in item.options" :key="index" :label="option.label" :value="option.value" clearable></el-option>
          </el-select>
          <el-date-picker v-model="date" type="datetimerange" :picker-options="pickerOptions" range-separator="至" start-placeholder="开始日期" end-placeholder="结束日期"
            align="right" @change="changeDate" clearable v-else-if="item.type == 'date'" value-format="timestamp" style="margin:10px;">
          </el-date-picker>
          <div style="margin: 10px;" v-else-if="item.type=='slot'">
            <slot :name="item.name"></slot>
          </div>
        </template>
        <div style="margin:10px;">
          <el-button v-if="isInit" @click="initSearch">重置</el-button>
          <el-button v-if="exportUrl" @click="exportData">导出EXCEL</el-button>
          <slot name="search-btn"></slot>
          <el-button type="primary" icon="el-icon-search" v-if="isSearch" @click="searchValue" :disabled="loading">搜索</el-button>
        </div>
      </div>
      <slot name="btn" slot="btn"></slot>
    </box-item>
    <box-item :text="tableText">
      <slot name="teble-btn" slot="btn"></slot>
      <el-table :data="tableData" style="width: 100%;" v-loading="loading">
        <template v-for="(item,i) in tableList">
          <el-table-column :label="item.label" :align="item.align || 'center'" v-if="item.type == 'slot'">
            <template slot-scope="scope">
              <slot slot-scope="scope" :row="scope.row" :name="item.name"></slot>
            </template>
          </el-table-column>
          <el-table-column :prop="item.prop" :label="item.label" :align="item.align || 'center'" v-else></el-table-column>
        </template>
      </el-table>
    </box-item>
    <vue-page v-if="total > 0">
      <el-pagination @current-change="getdata" :current-page.sync="page" :page-size="per_page" layout="prev, pager, next, jumper" :total="total" background :disabled="loading"></el-pagination>
    </vue-page>
  </div>
</template>
<script>
  Vue.component("table-template", {
    delimiters: ['[[', ']]'],
    props: {
      "search-text": {
        type: String | Array,
        default:"筛选"
      },
      "table-text": {
        type: String | Array,
        default:"记录列表"
      },
      "search-list":{
        type:Array
      },
      "table-list":{
        type:Array
      },
      "is-search":{
        type:Boolean,
        default:true
      },
      "is-init":{
        type:Boolean,
        default:false
      },
      "search_url":{
        type:String
      },
      "params":{
        type:Function | Object
      },
      "data-key":{
        type:String,
        default:"list"
      },
      "tab-date-type":{},
      "export-url":{
        type:String,
        default:""
      },
      "data-format":{
        type:String,
        default:""
      }
    },
    data(){
      function dateData(day){
        function onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * day);
            picker.$emit('pick', [start, end]);
        }
        return onClick
      }
      return{
        search:{},
        searchData:{},
        date:[],
        pickerOptions: {
          shortcuts: [
            {text: '最近一周',onClick:dateData(7)},
            {text: '最近一个月',onClick:dateData(30)},
            {text: '最近三个月',onClick:dateData(90)}
          ]
        },
        tableData:[],
        loading:false,
        page:1,
        per_page:10,
        total:0,
        amount_total:0
      }
    },
    created(){
      this.initdata();
      this.getdata();
    },
    methods:{
      initSearch(){
        this.$confirm('此操作会重置所有的筛选条件, 继续?', '提示', {
          confirmButtonText: '确定',
          cancelButtonText: '取消',
          type: 'warning'
        }).then(() => {
          this.$emit("init");
          this.search = {};
        });
      },
      initdata(){
        this.page = 1;
        this.total = 0;
        this.amount_total = 0;
        this.loading = false;
      },
      getJson(dataFormat){
        let params = {};
        if(typeof(this.params) === "function"){
          params = this.params()
        }else if(typeof(this.params) === "object" && !(Array.isArray(this.params))){
          params = this.params;
        }
        if(dataFormat){
          let json = {};
          json[dataFormat] = {...this.searchData,page:this.page,...params};
          return json;
        }
        return {...this.searchData,page:this.page,...params};
      },
      getdata(){
        this.loading = true;
        let data = this.getJson(this.dataFormat);
        this.$http.post(this.search_url,data).then(({data:{result,msg,data}})=>{
          this.loading = false;
          if(result == 1){
            let list = data[this.dataKey];
            this.tableData = list.data;
            this.total = list.total;
            this.total = list.total;
            this.per_page = list.per_page;
            this.amount_total = data.amount_total;
            this.$emit("gettotal",this.total);
            this.$emit("getamounttotal",this.amount_total);
            this.$emit("getdata",{data,result,msg});
          }else this.$message.error(msg);
        })
      },
      searchValue(){
        this.page = 1;
        this.searchData = this.search;
        this.getdata();
        this.$emit("search",this.search);
      },
      changeDate(data){
        if(this.tabDateType){
          if(data){
            this.search.start = parseInt(data[0] / 1000);
            this.search.end = parseInt(data[1] / 1000);
          }else{
            delete this.search.start;
            delete this.search.end;
          }
          return false;
        }
        if(data) this.search.time = {
          start:parseInt(data[0] / 1000),
          end:parseInt(data[1] / 1000)
        }
        else delete this.search.time;
      },
      exportData(){
        let data = this.getJson();
        let url = this.exportUrl;
        for (const key in data) {
          url += `&${key}=${data[key]}`
        }
        window.open(url);
      }
    },
    template: `#table-template`,
  })
</script>

