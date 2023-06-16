@extends('layouts.base')
@section('title', '折扣邮费设置')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>

    <style>
        
        .el-tabs__item,.is-top{font-size:16px}
        .el-tabs__active-bar { height: 3px;}
        .el-scrollbar__wrap{
            overflow-x: hidden;
        }
        .firstCate{
            width: 100%;
            text-align: center;
        }
        .firstCate .el-checkbox__inner{
            display: none;
        }
        .el-checkbox-group {
            text-align: left;  
        }
        
        
        .el-checkbox {   
            padding-left: 20px;
            text-align: left; 
        }

        .active-no-color {
            color: #666;
        }

        .checkbox-pane {
            display: flex;
        }

        .checkbox-color {
            color: #29BA9C;
        }

        .checkbox-name {
            line-height: 19px;
            margin-left: 5px;
            cursor: pointer;
        }

        .el-select-dropdown__list {
            margin-bottom: 20px;
        }

        .category-style {
            text-align:left;
            background-color:#eff3f6;
            border:3px solid #eff3f6;
            padding-left:5px;
        }
        .scrollbar-style {
            height:400px;
            border:3px solid #eff3f6;
            width:auto;
        }


    </style>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-crumbs">
                <a @click="goParent">折扣运费设置</a> > 折扣邮费设置
            </div>
            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">折扣邮费设置</div>
                </div>
                <div class="vue-main-form">
                    
                    <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                        <el-form-item label="选择分类" prop="classification">
                            <el-input :value="classification" style="width:60%;" disabled></el-input>
                            <el-button type="primary" @click="visDia()">选择分类</el-button>
                            <p class="help-block">只能选择商品的二级、三级分类</p>
                            <el-dialog title="选择分类" :visible.sync="dialogTableVisible" @close="closeDialog">
                                <el-select 
                                    default-first-option 
                                    value-key="id"
                                    v-model="search_categorys"
                                    filterable
                                    multiple
                                    remote
                                    reserve-keyword

                                    :remote-method="loadCategorys"
                                    :loading="loading"
                                    style="width:100%;margin-bottom:20px">
                                    <el-option v-for="item in categorys" :key="item.id" :label="'[ID:'+item.id+'][分类:'+item.name+']'" :value="item"></el-option>
                                </el-select>
                                <el-row :gutter="0">
                                    <el-col :span="8" >
                                        <div class="category-style">一级分类</div>
                                        <el-scrollbar class="scrollbar-style">
                                            <el-checkbox-group v-model="checkedFirstCate" class="firstCate" @change="loadSecond">
                                                <el-checkbox  style="display:block;" v-for="(item, index) in firstCate"
                                                    :label="item.id" :value="item.id" name="type">[[item.name]]
                                                </el-checkbox>
                                            </el-checkbox-group>
                                        </el-scrollbar>
                                    </el-col>
                                    <el-col :span="8" >
                                        <div class="category-style">二级分类</div>
                                        <el-scrollbar class="scrollbar-style">
                                            <!-- <el-checkbox-group v-model="checkedSecondCate" style="width:100%;text-align: center" @change="loadThird"> -->
                                                <div v-for="(item, index) in secondCate" class="checkbox-pane" :key="index">
                                                    <el-checkbox style="display:block;"  :label="item.id" :value="item.id" name="type" v-model="item.checked" @change="loadThird($event,item, index)">[[empty]]</el-checkbox>
                                                    <span @click="tapCheckBox(item, index)" :class="item.color ? 'checkbox-color checkbox-name' : 'checkbox-name'">[[item.name]]</span>
                                                </div>
                                            <!-- </el-checkbox-group> -->
                                        </el-scrollbar>
                                    </el-col>
                                    <el-col :span="8" >
                                        <div  class="category-style">三级分类</div>
                                        <el-scrollbar class="scrollbar-style">
                                            <!-- <el-checkbox-group v-model="checkedThirdCate" style="width:100%;text-align: center" style="width:100%" @change="loadThree"> -->
                                                <div v-for="(item, index) in thirdCate" class="checkbox-pane">
                                                    <el-checkbox style="display:block;" :label="item.id" :value="item.id" name="type" v-model="item.checked" @change="loadThree($event,item, index)">[[empty]]</el-checkbox>
                                                    <span class="checkbox-name">[[item.name]]</span>
                                                </div>
                                            <!-- </el-checkbox-group> -->
                                        </el-scrollbar>
                                    </el-col>
                                </el-row>

                                <span slot="footer" class="dialog-footer">
                                    <!-- <el-button @click="dialogVisible = false">取 消</el-button> -->
                                    <el-button type="primary" @click="choose()">确 定</el-button>
                                </span>
                            </el-dialog>
                        </el-form-item>
                        <el-form-item label="邮费设置">
                            <el-radio v-model="form.freight_type" :label="1">统一邮费</el-radio>
                            <el-input v-model.number="form.freight_value" style="width:60%;" :disabled="form.freight_type!=1">
                                <template slot="append">元</template>
                            </el-input><br>
                            <el-radio v-model="form.freight_type" :label="0">运费模板</el-radio>
                            <el-select v-model="form.template_id" value-key="id" style="width:60%" placeholder="请选择运费模板" :disabled="form.freight_type!=0">
                                <el-option v-for="item in template_list" :key="item.id" :label="item.dispatch_name" :value="item.id"></el-option>
                            </el-select>
                        </el-form-item>
                    </el-form>
                </div>
            </div>
            <!-- 分页 -->
            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">保存设置</el-button>
                    <el-button @click="goBack">返回</el-button>
                </div>
            </div>
            <!--end-->
        </div>
    </div>


    <script>
        var vm = new Vue({
        el:"#app",
        delimiters: ['[[', ']]'],
            data() {
                // let member_list = JSON.parse('{!! $levels?:'{}' !!}');
                 let url = JSON.parse('{!! $url !!}');
               // let url = {};
                 let categoryDiscount = JSON.parse('{!! $categoryDispach?:'{}' !!}');
                //let categoryDiscount = {};
                let classify = JSON.parse('{!! $classify?:'{}' !!}');
                console.log(categoryDiscount,456);
                let form ={
                        category_ids:[],
                        freight_type:1,
                        freight_value:"",
                        template_id:1,
                        ...categoryDiscount,
                    };
                let template_list = JSON.parse('{!! $list?:"{}" !!}');
                let classic =[];
                let classification = classic.join(",");
                let firstCate = JSON.parse('{!! $firstCate?:"{}" !!}');
                let secondCate = [];
                let thirdCate = [];
                let checkedFirstCate = [];
                let checkedSecondCate = [];
                let checkedThirdCate = [];
                let showCate=[];
                // var checkNumber = (rule, value, callback) => {
                //     if (!Number.isInteger(value)) {
                //         callback(new Error('请输入数字'));
                //     }
                //     setTimeout(() => {
                //         callback();
                //     }, 1000);
                // };

                return{
                    url:url,
                    form:form,
                    classic:classic,
                    template_list:template_list,
                    // member_list:member_list,
                    categorys:[],
                    dialogVisible:true,
                    dialogTableVisible:false,
                    loading: false,
                    submit_loading: false,
                    firstCate,
                    secondCate,
                    thirdCate,
                    checkedFirstCate,
                    checkedSecondCate,
                    checkedThirdCate,
                    classify,
                    rules: {
                        
                    },
                    keyword:"",//搜索分类
                    checkedFirstText:[], //获取二级点击文字时的id
                    categoryArr:[], //保存回显编辑的数据
                    empty:"",
                    search_categorys:[],
                    classification:"",
                }
            },
            mounted() {
                if(this.form.category_ids.length) {
                    // let arr=Object.keys(this.classify)
                    // if(arr.length!==0){
                    //     this.checkedFirstCate=this.classify[0]
                    //     this.checkedSecondCate=this.classify[1]
                    //     this.checkedThirdCate=this.classify[2]
                    //     this.startLoadThird();
                    //     this.startLoadSecond();
                    //     console.log(  this.checkedFirstCate)
                    //     console.log(     this.checkedSecondCate)
                    //     console.log( this.checkedThirdCate)
                    // }

                    this.categoryArr = this.form.category_ids;
                    for(let item of this.form.category_ids) { 
                        this.classification += `[ID:${item.id}][分类: ${item.name}],`;
                    }
                }
            },
            watch: {
                'search_categorys'(newVal,oldVal){
                    // 二级
                    for(let cItem of this.secondCate) {
                        this.$set(cItem,'checked',false)
                        for(let item of this.search_categorys) {
                            if(item.id == cItem.id) {
                                this.$nextTick(() => {
                                    this.$set(cItem,'checked',true)
                                })
                                break;
                            }
                        }
                    }
                    // 三级
                    for(let cItem of this.thirdCate) {
                        this.$set(cItem,'checked',false)
                        for(let item of this.search_categorys) {
                            if(item.id == cItem.id) {
                                this.$nextTick(() => {
                                    this.$set(cItem,'checked',true)
                                })
                                break;
                            }
                        }
                    }
                }
            },
            methods: {
                goBack() {
                    window.location.href='{!! yzWebFullUrl('discount.batch-dispatch.freight') !!}';
                },
                loadCategorys(query) {
                    if (query !== '') {
                        this.loading = true;
                        this.$http.get("{!! yzWebUrl('discount.batch-dispatch.select-category', ['keyword' => '']) !!}" + query).then(response => {
                            this.categorys = response.data.data;
                            this.data=response.data.data;
                            this.loading = false;
                        }, response => {
                            console.log(response);
                        });
                    } else {
                        this.categorys = [];
                    }
                },
                submitForm(formName) {
                    // if(this.form.discount_method == 1){
                    //     for(let i=0;i<this.member_list.length;i++){
                    //         if(this.form.discount_value[i]<10||this.form.discount_value[i]>0){
                    //             this.$message({message: "折扣数值不能大于10或者小于0",type: 'error'});
                    //             return false;
                    //         }
                    //     }
                    // }
                    console.log(this.form,'this.formthis.form');

                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post(this.url,{'form_data':this.form}).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
                                    window.location.href='{!! yzWebFullUrl('discount.batch-dispatch.freight') !!}';
                                } else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                    loading.close();
                                }
                            },response => {
                                loading.close();
                            });
                        }else {
                            console.log('error submit!!');
                            return false;
                        }
                    });
                },
                goParent() {
                    window.location.href = `{!! yzWebFullUrl('discount.batch-dispatch.freight') !!}`;
                },
                choose(){
                    this.dialogTableVisible = false;
                },
                visDia() {
                    if(this.categoryArr.length) {

                        console.log(1111111);
                        this.getCategoryData();
                        return

                        // this.getCategoryData();
                        // return
                        this.categorys = this.categoryArr;
                        this.search_categorys = this.categoryArr;

                    }
                    this.dialogTableVisible = true;
                },
                // 获取所有的分类数据

                getCategoryData() {
                    this.$http.get("{!! yzWebUrl('discount.batch-dispatch.get-all-cate') !!}").then(({data}) => {
                        if(data.result) {
                            console.log(data.data);
                            this.categorys = data.data;
                            this.search_categorys = this.categoryArr;
                            this.dialogTableVisible = true;
                        }else {
                            this.$message.error(data.msg);
                        }
                    });
                },

                // getCategoryData() {
                //     this.$http.get("{!! yzWebUrl('discount.batch-dispatch.get-all-cate') !!}").then(({data}) => {
                //         if(data.result) {
                //             this.categorys = data.data;
                //             this.search_categorys = this.categoryArr;
                //             this.dialogTableVisible = true;
                //         }else {
                //             this.$message.error(data.msg);
                //         }
                //     });
                // },

                // 关闭弹窗回调
                closeDialog() {
                    let newArr = [];
                    this.classification = "";
                    for(let item of this.search_categorys) {
                        this.classification += `[ID:${item.id}][分类: ${item.name}],`
                        newArr.push(item.id);
                    }
                    this.categoryArr = this.search_categorys;
                    this.form.category_ids = newArr;
                },
                // startLoadSecond(){
                //     this.$http.get("{!! yzWebUrl('discount.batch-dispatch.get-child', ['level' => 2]) !!}" + "&cate="+this.checkedFirstCate).then(response => {
                //         this.secondCate=response.body.data
                //         }, response => {
                //     });
                // },
                // startLoadThird(){
                //     this.$http.get("{!! yzWebUrl('discount.batch-dispatch.get-child', ['level' => 3]) !!}" + "&cate="+this.checkedSecondCate).then(response => {
                //         this.thirdCate=response.body.data
                //     }, response => {
                //     });
                // },
                //根据选择的第一级分类请求第二级分类
                loadSecond(data){
                    let val =  data[data.length - 1];
                    this.checkedFirstCate = [val];
                    this.$http.get("{!! yzWebUrl('discount.batch-dispatch.get-child') !!}"+`&level=2&cate=${val}`).then(({data}) => {
                        if(data.result) {
                            this.secondCate = data.data;
                            this.thirdCate = [];
                            this.checkedFirstText = [];
                            // 回显二级分类 ✔
                            for(let item of this.search_categorys) {
                                for(let cItem of this.secondCate) {
                                    this.$set(cItem,'checked',false)
                                    if(item.id == cItem.id) {
                                        this.$nextTick(() => {
                                            this.$set(cItem,'checked',true)
                                        })
                                        break;
                                    }
                                }
                            }
                        }else {
                            this.$message.error(data.msg);
                        }
                    });
                },
                tapCheckBox(row,index) {
                    // 处理多选搜索
                    this.$set(this.secondCate[index],'color',this.secondCate[index].color ? false : true);
                    let secondIndex = this.checkedFirstText.findIndex(item => {
                        return item == row.id
                    }) 
                    if(secondIndex == -1) {
                        this.checkedFirstText.push(row.id);
                    }else {
                        this.checkedFirstText.splice(secondIndex,1);
                    }
                    this.$http.get("{!! yzWebUrl('discount.batch-dispatch.get-child') !!}"+`&level=3&cate=${this.checkedFirstText}`).then(({data}) => {
                        if(data.result) {
                            this.thirdCate = data.data;
                            // 回显三级分类 ✔
                            for(let item of this.search_categorys) {
                                for(let cItem of this.thirdCate) {
                                    this.$set(cItem,'checked',false)
                                    if(item.id == cItem.id) {
                                        this.$nextTick(() => {
                                            this.$set(cItem,'checked',true)
                                        })
                                        break;
                                    }
                                }
                            }
                        }else {
                            this.$message.error(data.msg);
                        }
                    });
                },
                  //根据选择的第二级分类请求第三级分类
                loadThird($event,row,index){
                    if(!row.checked) {
                        // 删除多选框
                        let secondIndex = this.search_categorys.findIndex(item => {
                            return item.id == row.id
                        }) 
                        this.search_categorys.splice(secondIndex,1);
                        return
                    }
                    // 选中后添加复选框
                    this.categorys = [row];
                    let secondIndex = this.search_categorys.findIndex(item => {
                        return item.id == row.id
                    }) 
                    if(secondIndex == -1) {
                        this.search_categorys.push(row);
                    }
                },
                loadThree($event,row,index) {
                    if(!row.checked) {
                        // 删除多选框
                        let currentIndex = this.search_categorys.findIndex(item => {
                            return item.id == row.id
                        }) 
                        this.search_categorys.splice(currentIndex,1);
                        return
                    }
                    // 选中后添加复选框
                    this.categorys = [row];
                    let currentIndex = this.search_categorys.findIndex(item => {
                        return item.id == row.id
                    }) 
                    if(currentIndex == -1) {
                        this.search_categorys.push(row);
                    }
                }
            },
        });
    </script>
@endsection



