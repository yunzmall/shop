@extends('layouts.base')
@section('title', "商品列表")
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods.css')}}"/>
<style>
    .a-btn {
        border-radius: 2px;
        padding: 8px 12px;
        box-sizing: border-box;
        color: #666;
        font-weight: 500;
        text-align: center;
        margin-left: 1%;
        background-color: #fff;
    }
    .a-btn:hover{
        background-color: #29BA9C;
        color: #FFF;
        border-radius: 5px;
    }
    .tabs-div {
        background: #fff;
        padding: 13px 0;
        margin-left: 10px;
        cursor: pointer;
        border-radius:10px;
    }
    .a-colour2 {
        background-color: #29BA9C;
        color: #FFF;
        border-radius: 5px;
    }
    
    .sort_active{
        color: #29BA9C;
    }

    .dispatch-gap .el-checkbox {
        line-height: 3;
    }
</style>
    <div id="qrcode" ref="qrcode" style="display:none;"></div>
    <div class="rightlist">
        <div id="app" v-cloak v-loading="all_loading">

            <template>
                <div class="">
                    <div class="third-list">
                        <div class="tabs-div">
                            <span :class="tab_state == 1? 'a-btn a-colour2':'a-btn'" size="small" type="text" @click="stateList(1)">
                                上架中（[[statistics.put_shelf]]）
                            </span>
                            <span :class="tab_state == 0? 'a-btn a-colour2':'a-btn'" size="small" type="text" @click="stateList(0)">
                                仓库中（[[statistics.lower_shelf]]）
                            </span>
                            <span :class="tab_state == 'all'? 'a-btn a-colour2':'a-btn'" size="small" type="text" @click="stateList('all')">
                                全部商品（[[statistics.all_goods]]）
                            </span>
                        </div>
                        <div class="form-list">
                            <el-form :inline="true" :model="search_form" ref="search_form" style="margin-left:10px;">
                                <el-row>
                                    {{--<el-form-item label="" prop="">--}}
                                        {{--<el-select v-model="search_form.status" placeholder="请选择商品状态" clearable>--}}
                                            {{--<el-option v-for="item in status_list" :key="item.id" :label="item.name"--}}
                                                       {{--:value="item.id"></el-option>--}}
                                        {{--</el-select>--}}
                                    {{--</el-form-item>--}}
                                    <el-form-item label="" prop="">
                                        <el-select v-model="search_form.sell_stock" placeholder="请选择售中库存" clearable>
                                            <el-option v-for="item in sell_stock_list" :key="item.id" :label="item.name"
                                                       :value="item.id"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item>
                                        <el-select v-model="search_form.id_v1" placeholder="请选择一级分类" clearable
                                                   @change="changeV1()">
                                            <el-option v-for="item in category_list" :key="item.id" :label="item.name"
                                                       :value="item.id"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item>
                                        <el-select v-model="search_form.id_v2" placeholder="请选择二级分类" clearable
                                                   @change="changeV2()">
                                            <el-option v-for="item in category_list_v2" :key="item.id"
                                                       :label="item.name" :value="item.id"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item>
                                        <el-select v-model="search_form.id_v3" placeholder="请选择三级分类" clearable
                                                   v-if="catlevel==3">
                                            <el-option v-for="item in category_list_v3" :key="item.id"
                                                       :label="item.name" :value="item.id"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item label="" prop="">
                                        <el-select v-model="search_form.brand_id" placeholder="请选择品牌" clearable remote filterable :remote-method="getBrandData">
                                            <el-option v-for="item in brands_list" :key="item.id" :label="item.name"
                                                       :value="item.id"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item label="" prop="keyword">
                                        <el-input v-model="search_form.keyword" placeholder="请输入商品ID或关键字"></el-input>
                                    </el-form-item>
                                    <el-form-item label="" prop="keyword">
                                        <el-input v-model="search_form.filtering_name" placeholder="请输入商品标签"></el-input>
                                    </el-form-item>

                                    <el-form-item label="" prop="">
                                        <el-select v-model="search_form.is_spec" placeholder="请选择商品是否多规格" clearable>
                                            <el-option v-for="item in spec_list" :key="item.id" :label="item.name"
                                                       :value="item.id"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item>
                                        <el-select v-model="search_form.is_hide" placeholder="请选择商品是否显示" clearable>
                                            <el-option label="显示" value="1"></el-option>
                                            <el-option label="隐藏" value="2"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item>
                                        <el-select v-model="search_form.source_id" placeholder="请选择商品来源" clearable v-if="is_source_open==1">
                                            <el-option v-for="item in source_list" :key="item.id" :label="item.source_name" :value="item.id"></el-option>
                                        </el-select>
                                    </el-form-item>
                                    <el-form-item label="价格区间" prop="">
                                        <el-input v-model="search_form.min_price" placeholder="最低价"
                                                  style="width:150px;"></el-input>
                                        至
                                        <el-input v-model="search_form.max_price" placeholder="最高价"
                                                  style="width:150px;"></el-input>
                                    </el-form-item>
                                    <el-form-item label="商品类型" prop="leader_name">
                                        <el-checkbox v-model.number="search_form.is_new" :true-label="1"
                                                     :false-label="0">新品
                                        </el-checkbox>
                                        <el-checkbox v-model.number="search_form.is_hot" :true-label="1"
                                                     :false-label="0">热卖
                                        </el-checkbox>
                                        <el-checkbox v-model.number="search_form.is_recommand" :true-label="1"
                                                     :false-label="0">推荐
                                        </el-checkbox>
                                        <el-checkbox v-model.number="search_form.is_discount" :true-label="1"
                                                     :false-label="0">促销
                                        </el-checkbox>

                                    </el-form-item>

                                    {{--<el-form-item label="是否多规格" prop="is_spec">--}}
                                        {{--<el-radio v-model="search_form.is_spec"  label="1">是</el-radio>--}}
                                        {{--<el-radio v-model="search_form.is_spec"  label="0">否</el-radio>--}}
                                    {{--</el-form-item>--}}

                                    <a href="#">
                                        <el-button type="primary" icon="el-icon-search" @click="search(1)">搜索
                                        </el-button>
                                    </a>
                                    </el-col>
                                </el-row>
                            </el-form>
                        </div>
                        <div class="table-list">
                            <div style="margin-left:10px;">
                                <el-checkbox v-model.number="is_all_choose" :true-label="1" :false-label="0"
                                             @change="allChoose">[[is_all_choose==1?'全不选':'全选']]
                                </el-checkbox>
                                <el-button size="small" @click="batchPutAway(1)">批量上架</el-button>
                                <el-button size="small" @click="batchPutAway(0)">批量下架</el-button>
                                <el-button size="small" @click="batchDestroy">批量删除</el-button>
                                <el-button size="small" @click="openCategory">批量修改分类</el-button>
                                <el-button size="small" @click="openService">批量编辑服务提供</el-button>
                                <el-button size="small" @click="batchCopy">批量复制商品</el-button>
                                <el-button size="small" @click="openEditDispatch">修改运费模板</el-button>
                                <span style="color: #29bcb9;margin-left: 10px" v-if="choose_count>0"><b>已选商品（[[choose_count]]）</b></span>
                            </div>
                            <div>
                                <template>
                                    <!-- 表格start -->
                                    <el-table :data="goods_list" style="width: 100%"
                                              :class="table_loading==true?'loading-height':''"
                                              v-loading="table_loading">
                                        <el-table-column prop="id" label="选择" width="60" align="center">
                                            <template slot-scope="scope">
                                                <el-checkbox v-model.number="scope.row.is_choose" :true-label="1"
                                                             :false-label="0"
                                                             @change="oneChange(scope.row)"></el-checkbox>
                                            </template>
                                        </el-table-column>
                                        <el-table-column prop="id" label="ID" width="70"
                                                         align="center"></el-table-column>
                                        </el-table-column>
                                        <el-table-column prop="member_name" label="排序" max-width="80" align="center">
                                            <template slot-scope="scope">
                                                <el-popover class="item" placement="top" effect="light">
                                                    <div style="text-align:center;">
                                                        <el-input v-model="change_sort" size="small"
                                                                  style="width:100px;"></el-input>
                                                        <el-button size="small"
                                                                   @click="confirmChangeSort(scope.row.id)">确定
                                                        </el-button>
                                                    </div>
                                                    <a slot="reference">
                                                        <i class="el-icon-edit edit-i" title="点击编辑排序"
                                                           @click="editTitle(scope.$index,'sort')"></i>
                                                    </a>
                                                </el-popover>
                                                [[scope.row.display_order]]
                                            </template>
                                        </el-table-column>
                                        <el-table-column prop="total" label="商品" width="60" align="center">
                                            <template slot-scope="scope">
                                                <img :src="scope.row.thumb" style="width:50px;height:50px;">
                                            </template>
                                        </el-table-column>
                                        <el-table-column prop="down_time" label="" min-width="180" align="left"
                                                         class="edit-cell">
                                            <template slot-scope="scope">
                                                <el-popover class="item" placement="top" effect="light">
                                                    <div style="text-align:center;">
                                                        <div style="text-align:left;margin-bottom:10px;font-weight:900">
                                                            修改商品标题
                                                        </div>
                                                        <el-input v-model="change_title" style="width:400px"
                                                                  size="small"></el-input>
                                                        <el-button size="small"
                                                                   @click="confirmChange(scope.row.id,'title')">确定
                                                        </el-button>
                                                    </div>
                                                    <a slot="reference">
                                                        <i class="el-icon-edit edit-i" title="点击编辑"
                                                           @click="editTitle(scope.$index,'title')"></i>
                                                    </a>
                                                </el-popover>
                                                [[scope.row.title]]
                                            </template>
                                        </el-table-column>
                                        <el-table-column prop="cost_price" label="成本价" max-width="80" align="center">
                                            <template slot="header" slot-scope="scope">
                                                <div style="display: flex;align-items: center;justify-content:center">
                                                    <span>成本价</span>
                                                    <div style="display: flex;flex-direction: column;margin-left: 5px;">
                                                        <i class="el-icon-caret-top" :class="sort_cost_price == 'asc' ? 'sort_active' : '' " @click="btnPositiveSequence('cost_price')" ></i>
                                                        <i class="el-icon-caret-bottom" :class="sort_cost_price == 'desc' ? 'sort_active' : '' " @click="btnReverseOrder('cost_price')"></i>
                                                    </div>
                                                </div>
                                            </template>
                                            <template slot-scope="scope">
                                                <el-popover class="item" placement="top" effect="light"
                                                            :disabled="scope.row.has_option==1">
                                                    <div style="text-align:center;">
                                                        <el-input v-model="change_cost_price" size="small"
                                                                  style="width:100px;"></el-input>
                                                        <el-button size="small"
                                                                   @click="confirmChange(scope.row.id,'cost_price')">确定
                                                        </el-button>
                                                    </div>
                                                    <a slot="reference">
                                                        <i class="el-icon-edit edit-i"
                                                           :title="scope.row.has_option==1?'多规格不支持快速修改':'点击编辑'"
                                                           @click="editTitle(scope.$index,'cost_price')"></i>
                                                    </a>
                                                </el-popover>
                                                ￥[[scope.row.cost_price]]
                                            </template>
                                        </el-table-column>
                                        <el-table-column prop="member_num" label="价格" max-width="80" align="center">
                                            <template slot="header" slot-scope="scope">
                                                <div style="display: flex;align-items: center;justify-content:center">
                                                    <span>价格</span>
                                                    <div style="display: flex;flex-direction: column;margin-left: 5px;">
                                                        <i class="el-icon-caret-top" :class="sort_price == 'asc' ? 'sort_active' : '' " @click="btnPositiveSequence('price')" ></i>
                                                        <i class="el-icon-caret-bottom" :class="sort_price == 'desc' ? 'sort_active' : '' " @click="btnReverseOrder('price')"></i>
                                                    </div>
                                                </div>
                                            </template>
                                            <template slot-scope="scope">
                                                <el-popover class="item" placement="top" effect="light"
                                                            :disabled="scope.row.has_option==1">
                                                    <div style="text-align:center;">
                                                        <el-input v-model="change_price" size="small"
                                                                  style="width:100px;"></el-input>
                                                        <el-button size="small"
                                                                   @click="confirmChange(scope.row.id,'price')">确定
                                                        </el-button>
                                                    </div>
                                                    <a slot="reference">
                                                        <i class="el-icon-edit edit-i"
                                                           :title="scope.row.has_option==1?'多规格不支持快速修改':'点击编辑'"
                                                           @click="editTitle(scope.$index,'price')"></i>
                                                    </a>
                                                </el-popover>
                                                ￥[[scope.row.price]]
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="成本利润率" prop="cost_ratio" align="center">
                                            <template slot-scope="scope">
                                                [[scope.row.cost_ratio]]
                                          </template>
                                        </el-table-column>
                                        <el-table-column label="库存" align="center" max-width="80">
                                            <template slot="header" slot-scope="scope">
                                                <div style="display: flex;align-items: center;justify-content:center">
                                                    <span>库存</span>
                                                    <div style="display: flex;flex-direction: column;margin-left: 5px;">
                                                        <i class="el-icon-caret-top" :class="sort_stock == 'asc' ? 'sort_active' : '' " @click="btnPositiveSequence('stock')" ></i>
                                                        <i class="el-icon-caret-bottom" :class="sort_stock == 'desc' ? 'sort_active' : '' " @click="btnReverseOrder('stock')"></i>
                                                    </div>
                                                </div>
                                            </template>
                                            <template slot-scope="scope">
                                                <el-popover class="item" placement="top" effect="light"
                                                            :disabled="scope.row.has_option==1">
                                                    <div style="text-align:center;">
                                                        <el-input v-model="change_stock" size="small"
                                                                  style="width:100px;"></el-input>
                                                        <el-button size="small"
                                                                   @click="confirmChange(scope.row.id,'stock')">确定
                                                        </el-button>
                                                    </div>
                                                    <a slot="reference">
                                                        <i class="el-icon-edit edit-i"
                                                           :title="scope.row.has_option==1?'多规格不支持快速修改':'点击编辑'"
                                                           @click="editTitle(scope.$index,'stock')"></i>
                                                    </a>
                                                </el-popover>
                                                [[scope.row.stock]]
                                            </template>
                                        </el-table-column>
                                        <el-table-column prop="real_sales" label="销量" max-width="80" align="center">
                                            <template slot="header" slot-scope="scope">
                                                <div style="display: flex;align-items: center;justify-content:center">
                                                    <span>销量</span>
                                                    <div style="display: flex;flex-direction: column;margin-left: 5px;">
                                                        <i class="el-icon-caret-top" :class="sort_sales == 'asc' ? 'sort_active' : '' " @click="btnPositiveSequence('sales')" ></i>
                                                        <i class="el-icon-caret-bottom" :class="sort_sales == 'desc' ? 'sort_active' : '' " @click="btnReverseOrder('sales')"></i>
                                                    </div>
                                                </div>
                                            </template>
                                        </el-table-column>

                                        <el-table-column label="状态" prop="status_message" align="center">
                                            <template slot-scope="scope">
                                                [[scope.row.status?'上架':'下架']]
                                                <el-switch v-model="scope.row.status" :active-value="1"
                                                           :inactive-value="0"
                                                           @change="putAway(scope.row.id,scope.$index)"></el-switch>
                                            </template>
                                        </el-table-column>
                                        <el-table-column label="操作" width="320" align="center">
                                            <template slot-scope="scope">
                                                <div class="table-option">
                                                    <el-popover class="item" placement="left" effect="light"
                                                                trigger="hover">
                                                        <div style="text-align:center;">
                                                            <img :src="smallImg" alt=""
                                                                 style="margin:10px;width:100px;height:100px;">
                                                        </div>
                                                        <a slot="reference" @mouseover="SmallCode(scope.$index)" :href="'{{ yzWebFullUrl('goods.goods.generate-small-code', array('id' => '')) }}'+[[scope.row.id]]">小程序</a>
                                                    </el-popover>&nbsp;&nbsp;
                                                    <el-popover class="item" placement="left" effect="light"
                                                                trigger="hover">
                                                        <div style="text-align:center;">
                                                            <img :src="img" alt=""
                                                                 style="margin:10px;width:120px;height:120px;">
                                                        </div>
                                                        <a slot="reference" @mouseover="listCode(scope.$index)">推广链接</a>
                                                    </el-popover>&nbsp;&nbsp;
                                                    <a @click="copyGoods(scope.row.id)">
                                                        复制商品
                                                    </a>
                                                    &nbsp;&nbsp;
                                                    <a @click="editGoods(scope.row.id)">
                                                        编辑
                                                    </a>&nbsp;&nbsp;
                                                    <a @click="delOne(scope.row.id)">
                                                        删除
                                                    </a>&nbsp;&nbsp;
                                                    <a @click="copyList(scope.row.id)">
                                                        复制链接
                                                    </a>
                                                    <div>
                                                        <input v-model="scope.row.link" :ref="'list'+scope.row.id"
                                                               style="position:absolute;opacity:0;height:1px;"/>
                                                    </div>
                                                </div>
                                                <div>
                                                    <el-checkbox border size="mini" v-model.number="scope.row.is_new"
                                                                 :true-label="1" :false-label="0"
                                                                 @change="setProperty(scope.row.id,scope.$index,'is_new')">
                                                        新品
                                                    </el-checkbox>
                                                    <el-checkbox border size="mini" v-model.number="scope.row.is_hot"
                                                                 :true-label="1" :false-label="0"
                                                                 @change="setProperty(scope.row.id,scope.$index,'is_hot')">
                                                        热卖
                                                    </el-checkbox>
                                                    <el-checkbox border size="mini"
                                                                 v-model.number="scope.row.is_recommand" :true-label="1"
                                                                 :false-label="0"
                                                                 @change="setProperty(scope.row.id,scope.$index,'is_recommand')">
                                                        推荐
                                                    </el-checkbox>
                                                    <el-checkbox border size="mini"
                                                                 v-model.number="scope.row.is_discount" :true-label="1"
                                                                 :false-label="0"
                                                                 @change="setProperty(scope.row.id,scope.$index,'is_discount')">
                                                        促销
                                                    </el-checkbox>
                                                </div>

                                            </template>
                                        </el-table-column>
                                    </el-table>
                                    <!-- 表格end -->
                                </template>

                            </div>
                        </div>
                    </div>
                    
                    <el-dialog :visible.sync="category_show" width="60%" center  title="选择分类">
                        <div style="height:500px">
                            <el-select v-model="batch_v1" placeholder="请选择一级分类" clearable @change="batchChangeV1($event)" :style="{width:catlevel==3?'33%':'48%'}">
                                <el-option v-for="(item,index) in batch_category" :key="item.id" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                            <el-select v-model="batch_v2" placeholder="请选择二级分类" clearable @change="batchChangeV2($event)" :style="{width:catlevel==3?'33%':'48%'}">
                                <el-option v-for="item in batch_category_v2" :key="item.id" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                            <el-select v-model="batch_v3" placeholder="请选择三级分类" clearable v-if="catlevel==3" style="width:33%">
                                <el-option v-for="item in batch_category_v3" :key="item.id" :label="item.name" :value="item.id"></el-option>
                            </el-select>
                        </div>
                        <span slot="footer" class="dialog-footer">
                            <el-button @click="category_show = false">取 消</el-button>
                            <el-button type="primary" @click="batchCategory">确 定 </el-button>
                        </span>
                    </el-dialog>

                    <el-dialog title="服务提供" :visible.sync="service_show" center width="730px">
                        <div style="overflow:auto">
                            <el-form ref="service_form" style="width: 100%;height:auto;overflow:auto">
                                <el-form-item label="是否自动上下架">
                                    <el-radio v-model="service_form.is_automatic" :label="1">是</el-radio>
                                    <el-radio v-model="service_form.is_automatic" :label="0">否</el-radio>
                                </el-form-item>
                                <el-form-item label="时间方式">
                                    <el-radio v-model="service_form.time_type" :label="0">固定</el-radio>
                                    <el-radio v-model="service_form.time_type" :label="1">循环</el-radio>
                                    <span style="display: flex">
                            <span class="tip" style="margin-right: 20px">固定：在设置的时间商品自动上下架时间</span>
                            <span class="tip">循环：在循环日期内商品每天在设置的时间点自动循环上下架</span>
                        </span>
                                </el-form-item>
                                <el-form-item label="上下架时间" v-if="service_form.time_type==0">
                                    <el-date-picker
                                            v-model="service_form.shelves_time"
                                            type="datetimerange"
                                            value-format="timestamp"
                                            align="right"
                                            unlink-panels
                                            range-separator="至"
                                            start-placeholder="开始日期"
                                            end-placeholder="结束日期"
                                            :picker-options="pickerOptions">
                                    </el-date-picker>
                                </el-form-item>
                                <el-form-item label="循环日期" v-if="service_form.time_type==1">
                                    <el-date-picker
                                            v-model="service_form.loop_date"
                                            type="daterange"
                                            value-format="timestamp"
                                            align="right"
                                            unlink-panels
                                            range-separator="至"
                                            start-placeholder="开始日期"
                                            end-placeholder="结束日期"
                                            :picker-options="pickerOptions"
                                    >
                                    </el-date-picker>
                                </el-form-item>
                                <el-form-item label="上架时间" v-if="service_form.time_type==1">
                                    <el-time-select
                                            v-model="service_form.loop_time_up"
                                            value-format="timestamp"
                                            :picker-options="{
                                    start: '00:00',
                                    step: '00:05',
                                    end: '24:00'
                                }"
                                            placeholder="选择时间">
                                    </el-time-select>
                                    <span style="margin-left: 15px;margin-right: 8px">下架时间</span>
                                    <el-time-select
                                            v-model="service_form.loop_time_down"
                                            value-format="timestamp"
                                            :picker-options="{
                                    start: '00:00',
                                    step: '00:05',
                                    end: '24:00',
                                    minTime: service_form.loop_time_up
                                }"
                                            placeholder="选择时间">
                                    </el-time-select>
                                </el-form-item>
                                <el-form-item label="库存自动刷新" v-if="service_form.time_type==1">
                                    <el-switch v-model="service_form.auth_refresh_stock" active-color="#13ce66" inactive-color="#ff4949" :active-value="1" :inactive-value="0"></el-switch>
                                    <div class="tip">开启后，循环日期期间，每日重新上架时，库存商品数自动刷新为原始库存数</div>
                                </el-form-item>
                                <el-form-item label="原始库存" v-if="service_form.time_type==1">
                                    <el-input v-model="service_form.original_stock" style="width:30%;"></el-input>
                                </el-form-item>
                                <span style="">
                        <el-button type="primary" @click="serviceSubmit">确 认</el-button>
                        <el-button @click="closeService">取 消</el-button>
                    </span>
                            </el-form>
                        </div>
                    </el-dialog>

                    <!--统一修改运费-->
                    <el-dialog :visible.sync="edit_dispatch_show" center width="730px" v-loading="dispatch_loading"
                           element-loading-text="拼命同步中..."
                           element-loading-spinner="el-icon-loading"
                           element-loading-background="rgba(0, 0, 0, 0.8)">
                        <div>
                            <el-form label-width="35%" >
                                <div id="vue_head">
                                    <div class="base_set">
                                        <div class="vue-main-form">
                                            <el-form-item label="运费配置">
                                                <el-radio v-model="edit_dispatch_form.dispatch_type" :label="1">统一邮费</el-radio>
                                                <el-radio v-model="edit_dispatch_form.dispatch_type" :label="0">运费模板</el-radio>
                                            </el-form-item>
                                            <el-form-item label=" ">
                                                <div>
                                                    <el-input v-model="edit_dispatch_form.dispatch_price" style="width:80%;" v-if="edit_dispatch_form.dispatch_type == 1">
                                                        <template slot="append">元</template>
                                                    </el-input>
                                                    <div v-if="(Number(edit_dispatch_form.dispatch_price) < 0 || edit_dispatch_form.dispatch_price == '') && edit_dispatch_form.dispatch_type" style="color: #EE3939;font-size: 12px;line-height: 1; margin-top: 10px;">请输入大于等于0的数</div>
                                                    <el-select v-model="edit_dispatch_form.dispatch_id" placeholder="请选择运费模板" v-if="edit_dispatch_form.dispatch_type == 0" clearable filterable allow-create default-first-option>
                                                        <el-option :label="item.dispatch_name" :value="item.id" v-for="(item,index) in dispatchTemplates" :key="index"></el-option>
                                                    </el-select>
                                                </div>
                                            </el-form-item>
                                            <el-form-item label="配送方式" v-if="dispatchTypesSetting.length > 0">
                                                <el-checkbox-group v-model="edit_dispatch_form.dispatch_type_ids" class="dispatch-gap">
                                                    <el-checkbox :label="item.id"  v-for="(item,index) in dispatchTypesSetting" :key="index">[[item.name]]</el-checkbox>
                                                </el-checkbox-group>
                                            </el-form-item>
                                        </div>
                                    </div>
                                </div>

                                <div style="display: flex;justify-content: center;">
                                    <el-button type="primary" @click="dispatchSubmit">保存</el-button>
                                    <el-button @click="closeEditDispatch">取消</el-button>
                                </div>
                            </el-form>
                        </div>
                    </el-dialog>

                    <!-- 分页 -->
                    <div class="vue-page" v-show="total>1">
                        <el-row>
                            <el-col align="right">
                                <el-pagination
                                        background
                                        v-loading="loading"
                                        @size-change="setPageSize"
                                        @current-change="search"
                                        layout="total, sizes, prev, pager, next, jumper"
                                        :current-page="current_page"
                                        :page-sizes="[20, 50, 100, 200]"
                                        :page-size="per_size"
                                        :total="total">
                                </el-pagination>
                                {{--<el-pagination layout="prev, pager, next,jumper" @current-change="search" :total="total"--}}
                                               {{--:page-size="per_size" :current-page="current_page" background--}}
                                               {{--v-loading="loading"></el-pagination>--}}
                            </el-col>
                        </el-row>
                    </div>
                </div>

            </template>

        </div>
    </div>
    <script src="{{resource_get('static/js/qrcode.min.js')}}"></script>
    <script>
        var app = new Vue({
            el: "#app",
            delimiters: ['[[', ']]'],
            data() {
                return {

                    edit_url: '{!! yzWebFullUrl('goods.goods.edit') !!}',//商品编辑链接
                    delete_url: '{!! yzWebFullUrl('goods.goods.destroy') !!}',//商品删除链接
                    copy_url: '{!! yzWebFullUrl('goods.goods.copy') !!}', //商品复制链接
                    sort_url: '{!! yzWebFullUrl('goods.goods.displayorder') !!}', //商品排序链接


                    id: "",
                    img: "",//二维码
                    smallImg:"",//小程序二维码
                    catlevel: 0,//是否显示三级分类
                    is_all_choose: 0,//是否全选
                    goods_list: [],//商品列表
                    change_title: "",//修改标题弹框赋值
                    change_price: "",//修改价格弹框赋值
                    change_cost_price: "",//修改成本价弹框赋值
                    change_stock: "",//修改库存弹框赋值
                    change_sort: "",//修改排序弹框赋值
                    all_loading: false,
                    status_list: [
                        {id: '', name: '全部状态'},
                        {id: 0, name: '下架'},
                        {id: 1, name: '上架'},
                    ],
                    sell_stock_list: [
                        {id: '', name: '全部'},
                        {id: 0, name: '售罄'},
                        {id: 1, name: '出售中'},
                    ],
                    spec_list: [
                        {id: '', name: '全部'},
                        {id: 0, name: '否'},
                        {id: 1, name: '是'},
                    ],
                    brands_list: [],//品牌名称
                    category_list: [],
                    category_list_v2: [],
                    category_list_v3: [],
                    search_form: {
                        id_v1: '',
                        id_v2: '',
                        id_v3: '',
                        sort:'',
                        filtering_name:'',
                    },
                    form: {},
                    level_list: [],

                    //批量修改分类
                    batch_category:[],
                    batch_category_v2:[],
                    batch_category_v3:[],
                    batch_v1:'',
                    batch_v2:'',
                    batch_v3:'',
                    category_show:false,
                    service_show:false,
                    edit_dispatch_show:false,

                    loading: false,
                    table_loading: false,

                    //服务提供
                    service_form:{
                        is_automatic: 0,
                        time_type: 1,
                        auth_refresh_stock: 1,
                        shelves_time:[],
                        loop_date:[],
                    },
                    edit_dispatch_form:{
                        dispatch_type:1,
                        dispatch_price:"",
                        dispatch_type_ids:[],
                        dispatch_id:""
                    },
                    dispatchTypesSetting:{},
                    dispatchTemplates:{},
                    //商品来源
                    source_list: [],
                    is_source_open: 0,
                    pickerOptions: {
                        shortcuts: [{
                            text: "最近一周",
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
                                picker.$emit("pick", [start, end]);
                            }
                        }, {
                            text: "最近一个月",
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
                                picker.$emit("pick", [start, end]);
                            }
                        }, {
                            text: "最近三个月",
                            onClick(picker) {
                                const end = new Date();
                                const start = new Date();
                                start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
                                picker.$emit("pick", [start, end]);
                            }
                        }]
                    },
                    //

                    producer_id: 0, //什么厂家搜索的

                    //分页
                    total: 0,
                    per_size: 0,
                    current_page: 0,
                    this_page:0,
                    rules: {},


                    tab_state:1,//选项卡
                    statistics: {
                        'lower_shelf':0,
                        'put_shelf': 0,
                        'all_goods': 0,
                    },

                    sort_price:"",
                    sort_stock:"",
                    sort_sales:"",
                    sort_cost_price:"",
                    dispatch_loading: false,
                    choose_count:0,
                }
            },
            created() {
                //this.getData();
                let that = this;
                document.onkeydown = function(){
                    if(window.event.keyCode == 13)
                        that.search(1);
                }

            },
            mounted() {
                let data = {!! $data !!};
                let producer_id = JSON.parse('{!! $producerId !!}');

                if (producer_id) {
                    this.producer_id = producer_id;
                }

                this.setData(data);
                console.log(data)
            },
            methods: {
                btnPositiveSequence(val){
                    switch (val) {
                        case 'price'://价格
                        this.sort_price =  this.sort_price == 'asc'? '' : 'asc';
                            break;
                        case 'stock': //库存
                        this.sort_stock = this.sort_stock == 'asc'? '' : 'asc';
                            break;
                        case 'sales': //销量
                        this.sort_sales = this.sort_sales == 'asc'? '' : 'asc';
                            break;
                        case 'cost_price'://成本价
                            this.sort_cost_price =  this.sort_cost_price == 'asc'? '' : 'asc';
                            break;
                        default:
                            break;
                    }

                    this.search(1);
                },
                btnReverseOrder(val){
                    switch (val) {
                        case 'price'://价格
                        this.sort_price = this.sort_price == 'desc'? '' : 'desc';
                            break;
                        case 'stock': //库存
                        this.sort_stock = this.sort_stock == 'desc'? '' : 'desc';
                            break;
                        case 'sales': //销量
                        this.sort_sales = this.sort_sales == 'desc'? '' : 'desc';
                            break;
                        case 'cost_price': //成本价
                            this.sort_cost_price = this.sort_cost_price == 'desc'? '' : 'desc';
                            break;
                        default:
                            break;
                    }

                    this.search(1);
                },   
                stateList(value) {
                    this.tab_state  = value;
                    this.search(1);
                },
                //设置每页条数
                setPageSize(val) {
                    let refresh = val !== this.per_size;

                    this.per_size = val;

                    if (refresh) {
                        this.search(1);
                    }
                },

                setData(data) {
                    this.goods_list = data.list.data;
                    let arr = [];
                    this.goods_list.forEach((item, index) => {
                        item.title = this.escapeHTML(item.title)
                        arr.push(Object.assign({}, item, {is_choose: 0}))//是否选中
                    });
                    this.goods_list = arr;
                    this.total = data.list.total;
                    this.current_page = data.list.current_page;
                    this.per_size = data.list.per_page;
                    this.category_list = data.category;
                    this.catlevel = data.cat_level;
                    this.source_list = data.source_list;
                    this.is_source_open = data.is_source_open;

                    if (data.edit_url) {
                        this.edit_url = data.edit_url;
                    }
                    if (data.delete_url) {
                        this.delete_url = data.delete_url;
                    }
                    if (data.sort_url) {
                        this.sort_url = data.sort_url;
                    }
                    if (data.copy_url) {
                        this.copy_url = data.copy_url;
                    }

                    if (data.lower_shelf) {
                        this.statistics.lower_shelf = data.lower_shelf;
                    }
                    if (data.put_shelf) {
                        this.statistics.put_shelf = data.put_shelf;
                    }
                    if (data.all_goods) {
                        this.statistics.all_goods = data.all_goods;
                    }
                    if (data.dispatchTypesSetting) {
                        this.dispatchTypesSetting = data.dispatchTypesSetting;
                        for(let item of this.dispatchTypesSetting) {
                            this.edit_dispatch_form.dispatch_type_ids.push(item.id);
                        }
                    }
                    if (data.dispatchTemplates) {
                        this.dispatchTemplates = data.dispatchTemplates;
                    }

                },
                
                getData() {
                    var that = this;
                    that.table_loading = true;
                    that.$http.post("{!! yzWebFullUrl('goods.goods.goods-list') !!}").then(response => {
                        //console.log(response);
                        if (response.data.result == 1) {
                            this.setData(response.data.data);

                        } else {
                            that.$message.error(response.data.msg);
                        }
                        that.table_loading = false;
                    }), function (res) {
                        //console.log(res);
                        that.table_loading = false;
                    };
                },
                // 一级分类改变
                changeV1() {
                    let that = this;
                    this.search_form.id_v2 = "";
                    this.search_form.id_v3 = "";
                    this.category_list_v2 = [];
                    this.category_list_v3 = [];
                    that.$http.post("{!! yzWebFullUrl('goods.category.get-categorys-json') !!}",{level:2,parent_id:this.search_form.id_v1}).then(response => {
                        if (response.data.result == 1) {
                            this.category_list_v2 = response.data.data;
                        } else {
                            that.$message.error(response.data.msg);
                        }
                    }), function (res) {
                        //console.log(res);
                    };
                    this.category_list.find(item => {
                        if (item.id == this.search_form.id_v1) {
                            this.category_list_v2 = item.childrens;
                        }
                    });
                },
                // 二级分类改变
                changeV2() {
                    let that = this;
                    this.search_form.id_v3 = "";
                    this.category_list_v3 = [];
                    if (this.catlevel == 3) {
                        that.$http.post("{!! yzWebFullUrl('goods.category.get-categorys-json') !!}",{level:3,parent_id:this.search_form.id_v2}).then(response => {
                        if (response.data.result == 1) {
                            this.category_list_v3 = response.data.data;
                        } else {
                            that.$message.error(response.data.msg);
                        }
                        }), function (res) {
                            //console.log(res);
                        };
                    }
                },
                getBrandData(keyword) {
                    if(keyword=="") {
                        return;
                    }
                    let that = this;
                    that.$http.post("{!! yzWebFullUrl('goods.brand.search-brand') !!}",{keyword:keyword}).then(response => {
                        if (response.data.result == 1) {
                            this.brands_list = response.data.data;
                        } else {
                            that.$message.error(response.data.msg);
                        }
                    }), function (res) {
                        //console.log(res);
                    };
                },
                // 搜索、分页
                search(page) {
                    this.this_page = page;
                    var that = this;
                    console.log(that.search_form)
                    // 商品类型
                    let product_attr = [];
                    if (that.search_form.is_new == 1) {
                        product_attr.push('is_new')
                    }
                    if (that.search_form.is_hot == 1) {
                        product_attr.push('is_hot')
                    }
                    if (that.search_form.is_recommand == 1) {
                        product_attr.push('is_recommand')
                    }
                    if (that.search_form.is_discount == 1) {
                        product_attr.push('is_discount')
                    }

                    let order_by = {};
                    if (this.sort_price) {
                        order_by.price = this.sort_price;
                    }

                    if (this.sort_stock) {
                        order_by.stock = this.sort_stock;
                    }

                    if (this.sort_sales) {
                        order_by.real_sales = this.sort_sales;
                    }

                    if (this.sort_cost_price) {
                        order_by.cost_price = this.sort_cost_price;
                    }

                    let json = {
                        page: page,
                        tab_state:that.tab_state != 'all'?that.tab_state:'',
                        order_by:order_by,
                        per_size:that.per_size,
                        producer_id:that.producer_id,
                        search: {
                            keyword: that.search_form.keyword,
                            status: that.search_form.status,
                            sell_stock: that.search_form.sell_stock,
                            brand_id: that.search_form.brand_id,
                            min_price: that.search_form.min_price,
                            max_price: that.search_form.max_price,
                            product_attr: product_attr,//商品类型
                            is_spec: that.search_form.is_spec,
                            is_hide: that.search_form.is_hide,
                            filtering_name:that.search_form.filtering_name,
                            source_id:that.search_form.source_id,
                        },
                        category: {
                            parentid: that.search_form.id_v1,
                            childid: that.search_form.id_v2,
                            thirdid: that.search_form.id_v3,
                        }
                    };
                    that.table_loading = true;
                    that.$http.post("{!! yzWebFullUrl('goods.goods.goods-search') !!}", json).then(response => {
                        console.log(response);
                        if (response.data.result == 1) {
                            let arr = [];
                            that.goods_list = response.data.data.data;
                            that.goods_list.forEach((item, index) => {
                                item.title = that.escapeHTML(item.title)
                                arr.push(Object.assign({}, item, {is_choose: 0}))//是否选中
                            });
                            that.goods_list = arr;
                            that.total = response.data.data.total;
                            that.current_page = response.data.data.current_page;
                            that.per_size = response.data.data.per_page;
                        } else {
                            that.$message.error(response.data.msg);
                        }
                        that.table_loading = false;
                    }), function (res) {
                        console.log(res);
                        that.table_loading = false;
                    };
                },

                qrcodeScan(url) {//生成二维码
                    let qrcode = new QRCode('qrcode', {
                        width: 100,  // 二维码宽度
                        height: 100, // 二维码高度
                        render: 'image',
                        text: url
                    });
                    var data = $("canvas")[$("canvas").length - 1].toDataURL().replace("image/png", "image/octet-stream;");
                    console.log(data)
                    this.img = data;
                },
                // 活动二维码
                listCode(index) {
                    this.qrcodeScan(this.goods_list[index].link);
                },
                // 小程序二维码
                SmallCode(index) {
                    this.smallImg = this.goods_list[index].small_link;
                },
                // 复制活动链接
                copyList(index) {
                    that = this;
                    let Url = that.$refs['list' + index];
                    console.log(Url)
                    Url.select(); // 选择对象
                    document.execCommand("Copy", false);
                    that.$message({message: "复制成功！", type: "success"});
                },
                editGoods(id) {
                    let link = this.edit_url +`&id=`+id;
                    window.open(link)
                },
                copyGoods(id) {
                    let link = this.copy_url +`&id=`+id;
                    window.location.href = link;
                },
                // 单个选择
                oneChange(item) {
                    let that = this;
                    let is_all = 0;
                    that.goods_list.some((item, index) => {
                        if (item.is_choose == 1) {
                            is_all = 1;
                        } else {
                            is_all = 0;
                            return true;
                        }
                    })
                    that.is_all_choose = is_all;
                    this.sumChoose()
                },
                // 全选
                allChoose() {
                    let that = this;
                    let status = 0;
                    if (that.is_all_choose == 1) {
                        status = 1;
                    } else {
                        status = 0;
                    }
                    that.goods_list.forEach((item, index) => {
                        item.is_choose = status;
                    })
                    this.sumChoose()
                },
                // 统计选中个数
                sumChoose() {
                    let that = this;
                    that.choose_count = 0;
                    that.goods_list.forEach((item, index) => {
                        if(item.is_choose === 1) {
                            that.choose_count +=1;
                        };
                    })
                },
                // 上架、下架
                putAway(id, index) {
                    var that = this;
                    that.table_loading = true;
                    let data = that.goods_list[index].status;
                    let json = {id: id, type: 'status', data: data};
                    that.$http.post("{!! yzWebFullUrl('goods.goods.setPutaway') !!}", json).then(response => {
                        console.log(response);
                        if (response.data.result == 1) {
                            that.$message.success('操作成功！');
                        } else {
                            that.$message.error(response.data.msg);
                            that.goods_list[index].is_choose == 1 ? 0 : 1;
                        }
                        that.table_loading = false;
                    }), function (res) {
                        console.log(res);
                        that.table_loading = false;
                    };
                },
                // 批量上架、下架
                batchPutAway(data) {
                    var that = this;
                    that.table_loading = true;
                    let ids = [];
                    that.goods_list.forEach((item, index) => {
                        if (item.is_choose == 1) {
                            ids.push(item.id);
                        }
                    })
                    let json = {data: data, ids: ids}
                    that.$http.post("{!! yzWebFullUrl('goods.goods.batchSetProperty') !!}", json).then(response => {
                        console.log(response);
                        if (response.data.result == 1) {
                            that.$message.success('操作成功！');
                            that.is_all_choose = 0;
                            that.search(1);
                        } else {
                            that.$message.error(response.data.msg);
                        }
                        that.table_loading = false;
                    }), function (res) {
                        console.log(res);
                        that.table_loading = false;
                    };
                },
                //批量复制商品
                batchCopy() {
                    var that = this;
                    that.table_loading = true;
                    let ids = [];
                    that.goods_list.forEach((item, index) => {
                        if (item.is_choose == 1) {
                            ids.push(item.id);
                        }
                    })
                    let json = {ids: ids}
                    that.$http.post("{!! yzWebFullUrl('goods.goods.batchCopy') !!}", json).then(response => {
                        if (response.data.result == 1) {
                            that.$message.success('操作成功！');
                            that.is_all_choose = 0;
                            that.search(1);
                        } else {
                            that.$message.error(response.data.msg);
                        }
                        that.table_loading = false;
                    }), function (res) {
                        console.log(res);
                        that.table_loading = false;
                    };
                },
                openEditDispatch() {
                    this.edit_dispatch_show = true;
                },
                closeEditDispatch() {
                    this.edit_dispatch_show = false;
                },
                // 单个删除
                delOne(id) {
                    var that = this;
                    that.$confirm('确定删除吗', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        that.table_loading = true;
                        that.$http.post(this.delete_url, {id: id}).then(response => {
                            console.log(response);
                            if (response.data.result == 1) {
                                that.$message.success("删除成功！");
                                that.search(1);
                            } else {
                                that.$message.error(response.data);
                            }
                            that.table_loading = false;
                        }), function (res) {
                            console.log(res);
                            that.table_loading = false;
                        };
                    }).catch(() => {
                        this.$message({type: 'info', message: '已取消修改'});
                    });
                },
                // 批量删除
                batchDestroy() {
                    var that = this;
                    that.$confirm('确定删除吗', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        type: 'warning'
                    }).then(() => {
                        that.table_loading = true;
                        let ids = [];
                        that.goods_list.forEach((item, index) => {
                            if (item.is_choose == 1) {
                                ids.push(item.id);
                            }
                        })
                        let json = {ids: ids}
                        that.$http.post("{!! yzWebFullUrl('goods.goods.batchDestroy') !!}", json).then(response => {
                            console.log(response);
                            if (response.data.result == 1) {
                                that.$message.success('操作成功！');
                                that.is_all_choose = 0;
                                that.search(1);
                            } else {
                                that.$message.error(response.data.msg);
                            }
                            that.table_loading = false;
                        }), function (res) {
                            console.log(res);
                            that.table_loading = false;
                        };
                    }).catch(() => {
                        this.$message({type: 'info', message: '已取消修改'});
                    });
                },
                // 批量修改分类-打开分类选择框
                openCategory() {
                    this.category_show = true;
                    if(this.batch_category.length==0) {
                        this.getBatchCategory();
                    }
                },
                openService() {
                    this.service_show = true;
                },
                closeService() {
                    this.service_show = false;
                },
                serviceSubmit() {
                    var that = this;
                    that.table_loading = true;
                    let ids = [];
                    that.goods_list.forEach((item, index) => {
                        if (item.is_choose == 1) {
                            ids.push(item.id);
                        }
                    });
                    if (this.service_form.time_type==0) {
                        console.log(this.service_form)
                        if (!this.service_form.shelves_time[0] || !this.service_form.shelves_time[1]) {
                            this.$message({message: '上下架时间不能为空', type: 'error'});return;
                        }
                        this.service_form.on_shelf_time = this.service_form.shelves_time[0] / 1000;
                        this.service_form.lower_shelf_time = this.service_form.shelves_time[1] / 1000;
                    }
                    if (this.service_form.time_type==1) {
                        if (!this.service_form.loop_date[0] || !this.service_form.loop_date[1]) {
                            this.$message({message: '循环时间不能为空', type: 'error'});return;
                        }
                        this.service_form.loop_date_start = this.service_form.loop_date[0] / 1000;
                        this.service_form.loop_date_end = this.service_form.loop_date[1] / 1000;
                    }
                    if (this.service_form.time_type==1) {
                        if (!this.service_form.loop_time_up || !this.service_form.loop_time_down) {
                            this.$message({message: '循环上下架时间不能为空', type: 'error'});return;
                        }
                    }
                    let json = {
                        ids:ids,
                        service_form:this.service_form,
                    };
                    that.$http.post("{!! yzWebFullUrl('goods.goods.batchService') !!}", json).then(response => {
                        console.log(response);
                        if (response.data.result == 1) {
                            that.$message.success('操作成功！');
                            that.is_all_choose = 0;
                            that.search(1);
                            that.service_show = false;
                        } else {
                            that.$message.error(response.data.msg);
                        }
                        that.table_loading = false;
                    }), function (res) {
                        console.log(res);
                        that.table_loading = false;
                    };
                },
                getBatchCategory() {
                    var that = this;
                    that.$http.post("{!! yzWebFullUrl('goods.category.get-all-shop-category') !!}", {}).then(response => {
                        console.log(response);
                        if (response.data.result == 1) {
                            this.batch_category = response.data.data.list;
                        } else {
                            that.$message.error(response.data.msg);
                        }
                    }), function (res) {
                        console.log(res);
                    };
                },
                batchCategory() {
                    let that = this;
                    let json = {
                        level_1:this.batch_v1,
                        level_2:this.batch_v2,
                        goods_id_arr:[],
                    };
                    if(!json.level_1) {
                        this.$message.error('请选择一级分类')
                        return;
                    }
                    if(!json.level_2) {
                        this.$message.error('请选择二级分类')
                        return;
                    }
                    that.goods_list.forEach((item, index) => {
                        if (item.is_choose == 1) {
                            json.goods_id_arr.push(item.id);
                        }
                    })
                    if(this.catlevel == 3) {
                        json.level_3 = this.batch_v3
                        if(!json.level_3) {
                            this.$message.error('请选择三级分类')
                            return;
                        }
                    }

                    that.$http.post("{!! yzWebFullUrl('goods.category.change-many-goods-category') !!}", json).then(response => {
                        console.log(response);
                        if (response.data.result == 1) {
                            that.$message.success('操作成功！');
                            that.is_all_choose = 0;
                            that.search(1);
                        } else {
                            that.$message.error(response.data.msg);
                        }
                    }), function (res) {
                        console.log(res);
                    };
                },
                batchChangeV1(val) {
                    console.log(val)
                    this.batch_category_v2 = [];
                    this.batch_category_v3 = [];
                    this.batch_v2 = '';
                    this.batch_v3 = '';
                    let obj = this.batch_category.find((item,index) => {
                        return item.id == val
                    })
                    this.batch_category_v2 = obj?obj.has_many_children:[]
                    
                },
                batchChangeV2(val) {
                    if(this.catlevel!=3) {
                        return
                    }
                    this.batch_category_v3 = [];
                    this.batch_v3 = '';
                    let obj = this.batch_category_v2.find((item,index) => {
                        return item.id == val
                    })
                    this.batch_category_v3 = obj?obj.has_many_children:[]
                    
                },
                
                // 新品、热卖、推荐、促销、
                setProperty(id, index, type) {
                    var that = this;
                    that.table_loading = true;
                    console.log(that.goods_list[index][type])
                    let data = that.goods_list[index][type];
                    let json = {id: id, type: type, data: data};
                    that.$http.post("{!! yzWebFullUrl('goods.goods.setProperty') !!}", json).then(response => {
                        console.log(response);
                        if (response.data.result == 1) {
                            that.$message.success('操作成功！');
                        } else {
                            that.$message.error(response.data.msg);
                            that.goods_list[index][type] == 1 ? 0 : 1;
                        }
                        that.table_loading = false;
                    }), function (res) {
                        console.log(res);
                        that.table_loading = false;
                    };
                },
                // 编辑商品标题
                editTitle(index, type) {
                    let that = this;
                    if (type == 'title') {
                        that.change_title = "";
                        that.change_title = that.goods_list[index].title;
                    }
                    if (type == 'price') {
                        if (that.goods_list[index].has_option == 1) {
                            that.$message.error('多规格不支持快速修改');
                            return false;
                        }
                        that.change_price = "";
                        that.change_price = that.goods_list[index].price;
                    }
                    if (type == 'cost_price') {
                        if (that.goods_list[index].has_option == 1) {
                            that.$message.error('多规格不支持快速修改');
                            return false;
                        }
                        that.change_cost_price = "";
                        that.change_cost_price = that.goods_list[index].cost_price;
                    }
                    if (type == 'stock') {
                        if (that.goods_list[index].has_option == 1) {
                            that.$message.error('多规格不支持快速修改');
                            return false;
                        }
                        that.change_stock = "";
                        that.change_stock = that.goods_list[index].stock;
                    }
                    if (type == 'sort') {
                        that.change_sort = "";
                        that.change_sort = that.goods_list[index].display_order;
                    }
                },
                // 确认修改标题、价格、库存
                confirmChange(id, type) {
                    let that = this;
                    let value = '';
                    if (type == 'title') {
                        value = that.change_title;
                        if (that.change_title == '') {
                            that.$message.error('标题不能为空');
                            return false;
                        }
                    }
                    if (type == 'price') {
                        value = that.change_price;
                        if (!(/^\d+(\.\d+)?$/.test(that.change_price))) {
                            that.$message.error('请输入正确价格');
                            return false;
                        }
                    }
                    if (type == 'cost_price') {
                        value = that.change_cost_price;
                        if (!(/^\d+(\.\d+)?$/.test(that.change_cost_price))) {
                            that.$message.error('请输入正确成本价');
                            return false;
                        }
                    }
                    if (type == 'stock') {
                        value = that.change_stock;
                        if (!(/^\d+$/.test(that.change_stock))) {
                            that.$message.error('请输入正确数字');
                            return false;
                        }
                    }
                    let json = {
                        id: id,
                        type: type,
                        value: value,
                    };
                    that.table_loading = true;
                    that.$http.post("{!! yzWebFullUrl('goods.goods.change') !!}", json).then(response => {
                        console.log(response);
                        if (response.data.result == 1) {
                            that.$message.success('操作成功！');
                            if (document.all) {
                                document.getElementById('app').click();
                            } else {// 其它浏览器
                                var e = document.createEvent('MouseEvents')
                                e.initEvent('click', true, true)
                                document.getElementById('app').dispatchEvent(e)
                            }
                            that.search(this.this_page);
                        } else {
                            that.$message.error(response.data.msg);
                        }
                        that.table_loading = false;
                    }), function (res) {
                        console.log(res);
                        that.table_loading = false;
                    };
                },
                // 确认修改排序
                confirmChangeSort(id) {
                    let that = this;
                    if (!(/^\d+$/.test(that.change_sort))) {
                        that.$message.error('请输入正确数字');
                        return false;
                    }
                    that.table_loading = true;
                    let json = {id: id, value: that.change_sort};
                    that.$http.post("{!! yzWebFullUrl('goods.goods.displayorder') !!}", json).then(response => {
                        console.log(response);
                        if (response.data.result == 1) {
                            that.$message.success('操作成功！');
                            // that.$refs.search_form.click();
                            if (document.all) {
                                document.getElementById('app').click();
                            } else {// 其它浏览器
                                var e = document.createEvent('MouseEvents')
                                e.initEvent('click', true, true)
                                document.getElementById('app').dispatchEvent(e)
                            }
                            that.search(1);
                        } else {
                            that.$message.error(response.data.msg);
                        }
                        that.table_loading = false;
                    }), function (res) {
                        console.log(res);
                        that.table_loading = false;
                    };
                },
                // 字符转义
                escapeHTML(a) {
                    a = "" + a;
                    return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");
                    ;
                },
                async dispatchSubmit() {
                    this.dispatch_loading = true;
                    let json = {
                        data:this.edit_dispatch_form,
                        goods_id_arr:[],
                    };

                    this.goods_list.forEach((item, index) => {
                        if (item.is_choose == 1) {
                            json.goods_id_arr.push(item.id);
                        }
                    })

                    try {
                        let {data: {data,result,msg} } = await this.$http.post("{!! yzWebFullUrl('goods.goods.batchEditDispatch') !!}", json)
                        this.dispatch_loading = false;
                        if(result) {
                            this.edit_dispatch_show = false;
                            this.$message.success('操作成功！');
                            this.is_all_choose = 0;
                            this.search(this.this_page);
                        }else {
                            this.$message.error(msg);
                        }
                    }catch(e) {
                        this.$message.error(e);
                    }

                }
            },
        })

    </script>
@endsection
