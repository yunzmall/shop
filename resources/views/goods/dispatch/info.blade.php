@extends('layouts.base')
@section('title', '编辑配送模板')
@section('content')
<link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-crumbs">
                <a @click="goParent">配送模板</a> > 配送方式设置
            </div>
            <div class="vue-main">
                <div class="vue-main-title">
                    <div class="vue-main-title-left"></div>
                    <div class="vue-main-title-content">配送方式设置</div>
                </div>
                <div class="vue-main-form">
                    <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                        <el-form-item label="排序" prop="display_order">
                            <el-input v-model="form.display_order" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="配送方式名称" prop="dispatch_name">
                            <el-input v-model="form.dispatch_name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="计算方式" prop="calculate_type">
                            <el-radio v-model.number="form.calculate_type" :label="0">按重量计费</el-radio>
                            <el-radio v-model.number="form.calculate_type" :label="1">按件计费</el-radio>
                        </el-form-item>
                        <el-form-item label="配送区域" prop="explain_content">
                            <table class="el-table" style="width:90%">
                                <tr style="border-bottom:1px solid #EBEEF5">
                                    <th style="width:40%">运送到</th>
                                    <th>[[form.calculate_type==1?'首件(个)':'首重(克)']]</th>
                                    <th>[[form.calculate_type==1?'运费(元)':'首费(元)']]</th>
                                    <th>[[form.calculate_type==1?'续件(个)':'续重(克)']]</th>
                                    <th>续费(元)</th>
                                    <th style="width:80px">操作</th>
                                </tr>
                                <tr v-if="form.calculate_type==1">
                                    <td>
                                        全国【默认运费】
                                    </td>
                                    <td>
                                        <el-input v-model="form.first_piece" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="form.first_piece_price" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="form.another_piece" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="form.another_piece_price" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        
                                    </td>
                                </tr>
                                <tr v-else>
                                    <td>
                                        全国【默认运费】
                                    </td>
                                    <td>
                                        <el-input v-model="form.first_weight" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="form.first_weight_price" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="form.another_weight" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="form.another_weight_price" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        
                                    </td>
                                </tr>
                                <tr v-if="form.calculate_type==1" v-for="(item,index) in piece" :key="index">
                                    <td>
                                        <!-- <div style="font-size:12px;font-weight:600;line-height:18px;margin-right: 15px">
                                            <span v-for="(item1,index1) in item.areas" :key="index1">[[item1]];</span>
                                        </div> -->
                                        <div style="font-size:12px;font-weight:600;line-height:18px;margin-right: 15px">
                                            [[item.areas]]
                                        </div>
                                        <el-button size="small" @click="openVisible('piece',index)">选择区域</el-button>
                                    </td>
                                    <td>
                                        <el-input v-model="item.first_piece" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="item.first_piece_price" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="item.another_piece" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="item.another_piece_price" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-link :underline="false" @click="delList('piece',index)" class="vue-assist-color"> 删除</el-link>
                                    </td>
                                </tr>
                                <tr v-if="!form.calculate_type || form.calculate_type==0" v-for="(item,index) in weight" :key="index">
                                    <td>
                                        <!-- <div style="font-size:12px;font-weight:600;line-height:18px;margin-right: 15px">
                                            <span v-for="(item1,index1) in item.areas" :key="index1">[[item1]];</span>
                                        </div> -->
                                        <div style="font-size:12px;font-weight:600;line-height:18px;margin-right: 15px">
                                            [[item.areas]]
                                        </div>

                                        <el-button size="small" @click="openVisible('weight',index)">选择区域</el-button>
                                    </td>
                                    <td>
                                        <el-input v-model="item.first_weight" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="item.first_weight_price" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="item.another_weight" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                        <el-input v-model="item.another_weight_price" style="width:80%"></el-input>
                                    </td>
                                    <td>
                                    <el-link  :underline="false" @click="delList('weight',index)" class="vue-assist-color"> 删除</el-link>
                                    </td>
                                </tr>
                                
                            </table>
                            <el-button type="primary" plain icon="el-icon-plus" size="small" @click="addList">新增配送区域</el-button>
                            <div class="tip" v-if="form.calculate_type==1">根据件数来计算运费，当物品不足《首件数量》时，按照《首件费用》计算，超过部分按照《续件数量》和《续件费用》乘积来计算</div>
                            <div class="tip" v-else>根据重量来计算运费，当物品不足《首重重量》时，按照《首重费用》计算，超过部分按照《续重重量》和《续重费用》乘积来计算</div>
                        </el-form-item>
                        <el-form-item label="是否为默认快递模板" prop="is_default">
                            <el-switch v-model="form.is_default" :active-value="1" :inactive-value="0"></el-switch>
                        </el-form-item>
                        <el-form-item label="是否显示" prop="enabled">
                            <el-switch v-model="form.enabled" :active-value="1" :inactive-value="0"></el-switch>
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
            <el-dialog title="请选择地区" :visible.sync="centerDialogVisible" center v-if="chooseIndex!=-1&&showVisible" :before-close="beforeClose">
                <el-tree
                        v-loading="loading"
                        :props="props"
                        node-key="id"
                        :default-checked-keys="chooseItem.area_ids"
                        :default-expanded-keys="chooseItem.province_ids"
                        show-checkbox
                        lazy
                        @check-change="checkAreas"
                        ref="addressTree"
                        :data="treeData"
                        :load="loadNode"
                        style="height:500px;overflow:auto"
                >
                </el-tree>

                <span slot="footer" class="dialog-footer">
                    <el-button @click="beforeClose">取 消</el-button>
                    <el-button type="primary" @click="saveAreas">确 定</el-button>
                </span>

            </el-dialog>
        </div>
    </div>

    <script>
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                let id = {!! $id?:0 !!};
                console.log(id);
                return{
                    id:id,
                    submit_link:'',
                    piece:[],//按件计费-区域数据
                    weight:[],//按重量计费-区域数据
                    form:{
                        areas:'全国 [默认运费]',
                        first_weight:'0',
                        first_weight_price:'0',
                        another_weight:'0',
                        another_weight_price:'0',
                        

                    },
                    chooseIndex:-1,
                    chooseName:'weight',
                    chooseItem:{},
                    chooseAreas:[],
                    chooseAreaIds:[],
                    chooseProvinceIds:[],
                    centerDialogVisible:false,
                    showVisible:false,
                    props: {
                        label: 'areaname',
                        children: 'children',
                        isLeaf: 'isLeaf'
                    },
                    loading: false,
                    treeData: [],
                    rules:{
                        dispatch_name:{ required: true, message: '请输入配送方式名称'}
                    },
                }
            },
            created() {


            },
            mounted() {
                let json = {};
                if(!this.id) {
                    this.submit_link = '{!! yzWebFullUrl('goods.dispatch.add') !!}';
                }
                else {
                    this.submit_link = '{!! yzWebFullUrl('goods.dispatch.edit') !!}';
                    json.id = this.id;
                }
                

                this.getData(json);
            },
            methods: {
                getData(json) {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(this.submit_link,json).then(function (response) {
                            if (response.data.result){
                                this.form = {
                                    ...response.data.data.dispatch,
                                    enabled:response.data.data.dispatch.enabled || 0,
                                    calculate_type:response.data.data.dispatch.calculate_type || 0,
                                    is_default:response.data.data.dispatch.is_default || 0,
                                    areas:response.data.data.dispatch.areas || '全国 [默认运费]',
                                };
                                // 兼容旧的数据格式
                                if(response.data.data.dispatch.weight_data instanceof Object == true) {
                                    for(var i in response.data.data.dispatch.weight_data) {
                                        console.log(response.data.data.dispatch.weight_data[i])
                                        this.weight.push(response.data.data.dispatch.weight_data[i])
                                    }
                                }
                                else {
                                    this.weight = response.data.data.dispatch.weight_data || [];
                                }
                                if(response.data.data.dispatch.piece_data instanceof Object == true) {
                                    for(var i in response.data.data.dispatch.piece_data) {
                                        console.log(response.data.data.dispatch.piece_data[i])
                                        this.piece.push(response.data.data.dispatch.piece_data[i])
                                    }
                                }
                                else {
                                    this.piece = response.data.data.dispatch.piece_data || [];
                                }
                                
                                // this.form.enabled = 1;
                                console.log(this.piece_data)
                                // console.log(this.form)
                            }
                            else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                            loading.close();
                        },function (response) {
                            this.$message({message: response.data.msg,type: 'error'});
                            loading.close();
                        }
                    );
                },
                goParent() {
                    window.location.href = `{!! yzWebFullUrl('goods.dispatch.index') !!}`;
                },
                submitForm(formName) {
                    let that = this;
                    let json = this.form;
                    json.weight = this.weight;
                    json.piece = this.piece;
                    let json1 = {
                        dispatch:json
                    }
                    if(this.id) {
                        json1.id = this.id
                    }
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post(this.submit_link,json1).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
                                    this.goBack();
                                } else {
                                    this.$message({message: response.data.msg,type: 'error'});
                                }
                                loading.close();
                            },response => {
                                loading.close();
                            });
                        }
                        else {
                            console.log('error submit!!');
                            return false;
                        }
                    });
                },
                addList() {
                    if(this.form.calculate_type==1) {
                        this.piece.push(
                            {areas:"",area_ids:"",province_ids:[],first_piece:this.form.first_piece,first_piece_price:this.form.first_piece_price,another_piece:this.form.another_piece,another_piece_price:this.form.another_piece_price}
                        )
                    }
                    else {
                        console.log(this.weight)
                        this.weight.push(
                            {areas:"",area_ids:"",province_ids:[],first_weight:this.form.first_weight,first_weight_price:this.form.first_weight_price,another_weight:this.form.another_weight,another_weight_price:this.form.another_weight_price}
                        )
                    }
                },
                delList(name,index) {
                    if(name == "weight") {
                        this.weight.splice(index,1);
                    }
                    else if(name == 'piece') {
                        this.piece.splice(index,1);
                    }
                },
                handleClose(area) {
                    this.form.areas.splice(this.form.areas.indexOf(area), 1);
                },
                openVisible(name,index) {
                    this.centerDialogVisible = true;
                    this.showVisible = true;
                    this.chooseName = name;
                    this.chooseIndex = index;
                    this.chooseItem = {};
                    if(name == "weight") {
                        this.chooseItem = JSON.parse(JSON.stringify(this.weight[index]));
                        this.chooseItem.area_ids = this.weight[index].area_ids.split(";");
                        this.chooseItem.area_ids.forEach((item,index) => {
                            this.chooseItem.area_ids[index] = Number(item)
                        });
                        this.chooseItem.areas = this.weight[index].areas.split(";");
                    }
                    else if(name == "piece") {
                        this.chooseItem = JSON.parse(JSON.stringify(this.piece[index]));
                        this.chooseItem.area_ids = this.piece[index].area_ids.split(";");
                        this.chooseItem.area_ids.forEach((item,index) => {
                            this.chooseItem.area_ids[index] = Number(item)
                        });
                        // this.chooseItem.area_ids = this.piece[index].area_ids.split(";");
                        this.chooseItem.areas = this.piece[index].areas.split(";");
                    }
                    console.log(this.chooseItem)
                },
                loadNode(node, resolve) {
                    this.loading = true;
                    if (!node.data.id) {
                        //省份
                        node.data.id = 0;
                        this.$http.get("{!! yzWebUrl('area.list', ['parent_id'=> 0]) !!}").then(response => {
                            response.data.data.forEach(function (province) {
                                province.isLeaf = false;
                            });
                            resolve(response.data.data);

                            this.loading = false;
                        }, response => {
                            console.log(response);
                        });
                    } else {
                        //城市
                        this.$http.get("{!! yzWebUrl('area.list', ['parent_id'=> '']) !!}" + node.data.id).then(response => {
                            //城市没有子节点
                            response.data.data.forEach(function (city) {
                                city.isLeaf = true;
                            })
                            resolve(response.data.data);
                            // 载入数据后,刷新已选中
                            this.loading = false;
                        }, response => {
                            console.log(response);
                        });
                    }
                },
                checkAreas(node,checked,children) {
                    if(node.isLeaf){
                        return;
                    }
                    if(checked){
                        if(!this.chooseItem.province_ids) {
                            this.chooseItem.province_ids = [];
                        }
                        this.chooseItem.province_ids.push(node.id)
                    }
                },
                saveAreas() {
                    let areas = [];
                    let area_ids = [];
                    let province_ids = [];
                    this.$refs.addressTree.getCheckedNodes().forEach(function (node) {
                        if (node.level == 1) {
                            province_ids.push(node.id);
                        } else if (node.level == 2) {
                            area_ids.push(node.id);
                            areas.push(node.areaname)
                        }
                    });
                    this.$refs.addressTree.getHalfCheckedNodes().forEach(function (node) {
                        if (node.level == 1) {
                            province_ids.push(node.id);
                        }
                        
                    });
                    if(this.chooseName == "weight") {
                        this.weight[this.chooseIndex].area_ids = area_ids.join(";");
                        this.weight[this.chooseIndex].areas = areas.join(";");
                        this.weight[this.chooseIndex].province_ids = province_ids;
                    }
                    else if(this.chooseName == "piece") {
                        this.piece[this.chooseIndex].area_ids = area_ids.join(";");
                        this.piece[this.chooseIndex].areas = areas.join(";");
                        this.piece[this.chooseIndex].province_ids = province_ids;
                    }
                    
                    console.log(this.weight)
                    console.log(this.piece)
                    this.centerDialogVisible = false
                    this.showVisible = false;


                },
                beforeClose() {
                    this.centerDialogVisible = false;
                    this.showVisible = false;
                },
                goBack() {
                    history.go(-1)
                }             
            },
        })

    </script>
@endsection


