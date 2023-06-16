@extends('layouts.base')
@section('title', '编辑优惠券')
@section('content')
    <link rel="stylesheet" type="text/css" href="{{static_url('yunshop/goods/vue-goods1.css')}}"/>
    <div class="all">
        <div id="app" v-cloak>
            <div class="vue-crumbs">
                <a @click="goParent">优惠券管理</a> > 编辑优惠券
            </div>
            <el-form ref="form" :model="form" :rules="rules" label-width="15%">
                <div class="vue-head" style="margin-bottom:20px">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">基本信息</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="排序" prop="display_order">
                            <el-input v-model="form.display_order" style="width:70%;"></el-input>
                            <div class="tip">数字越大越靠前</div>
                        </el-form-item>
                        <el-form-item label="优惠券名称" prop="name">
                            <el-input v-model="form.name" style="width:70%;"></el-input>
                        </el-form-item>
                        <el-form-item label="是否开启" prop="status">
                            <el-switch v-model.number="form.status" :active-value="1" :inactive-value="0"></el-switch>
                            <div class="tip">关闭后,用户无法领取, 但是已经被领取的可以继续使用</div>
                        </el-form-item>
                    </div>
                </div>
                <div class="vue-head" style="margin-bottom:20px">
                    <div class="vue-main-title">
                        <div class="vue-main-title-left"></div>
                        <div class="vue-main-title-content">优惠方式</div>
                    </div>
                    <div class="vue-main-form">
                        <el-form-item label="使用条件 - 订单金额" prop="enough">
                            <el-input v-model="form.enough" style="width:70%;"></el-input>
                            <div class="tip">消费满多少金额才可以使用该优惠券 (设置为 0 则不限制消费金额)</div>
                        </el-form-item>
                        <el-form-item label="领取条件 - 会员等级" prop="level_limit">
                            <el-select v-model="form.level_limit" style="width:70%" placeholder="请选择会员" >
                                <el-option :value="-1" label="所有会员"></el-option>
                                <el-option v-for="item in member_list" :key="item.id" :label="item.level_name+'(及以上等级可以领取)'" :value="item.id"></el-option>
                            </el-select>
                            <div class="tip">选择"所有会员"表示商城的所有会员,包括没有划分等级的; <br>例如: 选择等级 3,表示包括 3 以及大于等级 3 的会员都可领取,即等级 3, 4, 5...都可以领取.</div>
                        </el-form-item>
                        <el-form-item label="使用时间限制" prop="time_limit">
                            <el-radio v-model="form.time_limit" :label="0">
                                获取后<el-input v-model.number="form.time_days" style="width:100px;" size="mini" :disabled="form.time_limit==1"></el-input>天内有效(0 为不限时间使用)
                            </el-radio><br>
                            <el-radio v-model.number="form.time_limit" :label="1">
                                时间范围
                            </el-radio>
                            <el-date-picker
                                    :disabled="form.time_limit==0"
                                    size="mini"
                                    v-model="form.time"
                                    type="daterange"
                                    value-format="timestamp"
                                    range-separator="至"
                                    start-placeholder="开始日期"
                                    end-placeholder="结束日期"
                                    style="margin-left:5px;"
                                    :default-time="['00:00:00', '23:59:59']"
                                    align="right">
                            </el-date-picker>
                        </el-form-item>
                        <el-form-item label="使用方式" prop="is_complex">
                            <el-radio v-model.number="form.is_complex" :label="0">单张使用</el-radio>
                            <el-radio v-model.number="form.is_complex" :label="1">多张一起使用</el-radio>
                            <div class="tip">如选择单张使用，则一笔订单只能使用一张该类型的优惠券； 选择多张一起使用，则满足使用的金额就可以， 比如我有300-50优惠券3张，下单金额满900元，可以用三张，下单金额满600元可以用2张，下单金额满300元可以用一张</div>
                        </el-form-item>
                        <el-form-item label="优惠方式" prop="coupon_method">
                            <el-radio v-model.number="form.coupon_method" :label="1">立减</el-radio>
                            <el-radio v-model.number="form.coupon_method" :label="2">打折</el-radio>
                            <div>
                                <el-input v-model="form.deduct" style="width:70%" v-show="form.coupon_method==1">
                                    <template slot="prepend">立减</template>
                                    <template slot="append">元</template>
                                </el-input>
                                <el-input v-model="form.discount" style="width:70%" v-show="form.coupon_method==2">
                                    <template slot="prepend">打</template>
                                    <template slot="append">折</template>
                                </el-input>
                            </div>

                            <div class="tip">最多保留两位小数</div>
                        </el-form-item>
                        <el-form-item label="适用范围" prop="use_type">
                            <el-radio-group v-model.number="form.use_type">
                                <el-radio :label="0">平台自营商品（不包含供应商商品）</el-radio>
                                <el-radio :label="1">指定商品分类 </el-radio>
                                <el-radio :label="2">指定商品</el-radio>
                                <el-radio :label="4" v-if="store_is_open==1">指定门店</el-radio>
                                <el-radio :label="7" v-if="hotel_is_open==1">指定酒店  </el-radio>
                                <el-radio :label="8">兑换券 </el-radio>
                                <el-radio :label="9">选择商品 + 选择门店 </el-radio>
                            </el-radio-group>

                            <!-- 分类 -->
                            <div v-show="form.use_type==1">
                                <el-tag v-for="(tag,index) in category_names" :key="index" closable @close="closeCategory(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>
                            <!-- 商品 -->
                            <div v-show="form.use_type==2">
                                <el-tag v-for="(tag,index) in goods_names" :key="index" closable @close="closeGoods(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>
                            <!-- 门店 -->
                            <div v-show="form.use_type==4">
                                <el-tag v-for="(tag,index) in store_names" :key="index" closable @close="closeStore(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>
                            <!-- 酒店 -->
                            <div v-show="form.use_type==7">
                                <el-tag v-for="(tag,index) in hotel_names" :key="index" closable @close="closeHotel(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>
                            <!-- 兑换券 -->
                            <div v-show="form.use_type==8">
                                <el-tag v-for="(tag,index) in goods_name" :key="index" closable @close="closeGoods(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>
                            <!-- 门店+商品 -->
                            <div v-show="form.use_type==9&&use_conditions.good_names.length>0" v-if="store_is_open==1">
                                商品：
                                <el-tag v-for="(tag,index) in use_conditions.good_names" :key="index" closable @close="closeGoods(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>
                            <div v-show="form.use_type==9&&use_conditions.store_names.length>0" v-if="store_is_open==1">
                                门店：
                                <el-tag v-for="(tag,index) in use_conditions.store_names" :key="index" closable @close="closeStore(index)" style="margin-right:5px;">
                                    [[tag]]
                                </el-tag>
                            </div>
                            <div v-show="form.use_type==9">
                                <el-checkbox v-model="use_conditions.is_all_good" @change="selectAllGoods">全选商品</el-checkbox>
                                <el-checkbox v-model="use_conditions.is_all_store" @change="selectAllStores">全选门店</el-checkbox>
                            </div>
                            <div class="tip" v-show="form.use_type==1">供应商商品不可使用!</div>
                            <div class="tip" v-show="form.use_type==2">供应商商品不可使用!</div>
                            <div class="tip" v-show="form.use_type==9">
                                供应商商品不可使用!
                            </div>
                            <div>
                                <el-button size="mini" @click="openDia(1)" v-show="form.use_type==1">选择分类</el-button>
                                <el-button size="mini" @click="openDia(2)" v-show="form.use_type==2">选择商品</el-button>
                                <el-button size="mini" @click="openDia(4)" v-show="form.use_type==4">选择门店</el-button>
                                <el-button size="mini" @click="openDia(7)" v-show="form.use_type==7">选择酒店</el-button>
                                <el-button size="mini" @click="openDia(8)" v-show="form.use_type==8">选择兑换商品</el-button>
                                <div v-show="form.use_type==9" v-if="store_is_open==1">
                                    <el-button size="mini" @click="openDia(2)">选择商品</el-button>
                                    <el-button size="mini" @click="openDia(4)">选择门店</el-button>
                                </div>
                            </div>

                            <div class="tip" v-show="form.use_type==0">如选择此项,则支持商城所有商品使用!</div>
                        </el-form-item>
                        <el-form-item label="是否可领取" prop="get_type">
                            <el-radio v-model.number="form.get_type" :label="1">可以</el-radio>
                            <el-radio v-model.number="form.get_type" :label="0">不可以</el-radio>
                            <div class="tip">是否可以在领券中心领取 (或者只能手动发放)</div>

                            <div>
                                <el-input v-model.number="form.get_max" style="width:70%" >
                                    <template slot="prepend">每人限领</template>
                                    <template slot="append">张</template>
                                </el-input>
                                <div class="tip" >每人限领数量 (-1为不限制数量)</div>

                                <el-checkbox v-model="form.get_limit_type">每人每日限领</el-checkbox>
                                <br>
                                <el-input v-model.number="form.get_limit_max" style="width:70%" v-show="form.get_limit_type==true">
                                    <template slot="prepend">每人每日限领</template>
                                    <template slot="append">张</template>
                                </el-input>
                                <div class="tip" v-show="form.get_limit_type==true">每人每日限领数量 (-1为不限制数量)</div>
                            </div>
                        </el-form-item>

                        <el-form-item label="发放总数" prop="total">
                            @if($id)
                                <el-input v-model="form.total" style="width:70%;" disabled></el-input>
                                <el-button v-if="form.total > 0" type="primary" plain @click="couponAddCount(id)">点击添加优惠券</el-button>
                                <el-button type="primary" @click="dialogTableVisible = true">查看优惠券新增记录</el-button>
                                <el-dialog title="优惠券新增记录" :visible.sync="dialogTableVisible">
                                    <el-table :data="form.coupon_increase_records">
                                        <el-table-column property="created_at" label="日期"></el-table-column>
                                        <el-table-column property="count" label="新增优惠券数量"></el-table-column>
                                    </el-table>
                                </el-dialog>
                            @else
                                <el-input v-model="form.total" style="width:70%;"></el-input>
                            @endif
                            <div class="tip">优惠券总数量，没有则不能领取或发放, -1 为不限制数量</div>
                        </el-form-item>


                        <div class="vue-head" v-show="integral_is_open==1" style="margin-bottom:20px">
                            <div class="vue-main-title">
                                <div class="vue-main-title-left"></div>
                                <div class="vue-main-title-content">优惠券兑换</div>
                            </div>

                            <div class="vue-main-form">
                                <el-form-item :label=`${integral_name}兑换优惠券` prop="">
                                    <el-switch v-model="form.is_integral_exchange_coupon" :active-value="1" :inactive-value="0"></el-switch>
                                </el-form-item>

                                <el-form-item v-show="form.is_integral_exchange_coupon == 1" label="兑换一张需要" prop="exchange_coupon_integral">
                                    <el-input v-model="form.exchange_coupon_integral" style="width:70%">
                                        <template slot="append">[[integral_name]]</template>
                                    </el-input>
                                </el-form-item>
                            </div>
                        </div>

                        <div class="vue-head" v-show="member_tags_is_open==1" style="margin-bottom:20px">
                            <div class="vue-main-title">
                                <div class="vue-main-title-left"></div>
                                <div class="vue-main-title-content">用户标签</div>
                            </div>

                            <div class="vue-main-form">
                                <el-form-item label="指定会员标签领取">
                                    <el-button size="mini" @click="openDia(10)">添加标签</el-button>
                                    <div>
                                        <el-tag v-for="(tag,index) in member_tags_names" :key="index" closable @close="closeMemberTags(index)" style="margin-right:5px;">
                                            [[tag]]
                                        </el-tag>
                                    </div>
                                </el-form-item>

                            </div>
                        </div>

                        <div class="vue-head" style="margin-bottom:20px">
                            <div class="vue-main-title">
                                <div class="vue-main-title-left"></div>
                                <div class="vue-main-title-content">领券说明</div>
                            </div>
                            <div class="vue-main-form">
                                <el-form-item label="领券说明">
                                    <tinymceee v-model="form.content" style="width:70%"></tinymceee>
                                </el-form-item>
                            </div>
                        </div>

                    </div>
                </div>
            </el-form>
            <!-- 分页 -->
            <div class="vue-page">
                <div class="vue-center">
                    <el-button type="primary" @click="submitForm('form')">提交</el-button>
                    <el-button @click="goBack">返回</el-button>
                </div>
            </div>
            <upload-img :upload-show="uploadShow" :name="chooseImgName" @replace="changeProp" @sure="sureImg"></upload-img>


            <el-dialog :visible.sync="category_show" width="60%" center title="选择分类">
                <div>
                    <div>
                        <el-input v-model="category_keyword" style="width:70%"></el-input>
                        <el-button type="primary" @click="searchCategory()">搜索</el-button>
                    </div>
                    <el-table :data="category_list" style="width: 100%;height:500px;overflow:auto" v-loading="loading">
                        <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                        <el-table-column label="分类名称">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center">
                                    <div style="margin-left:10px">[[scope.row.name]]</div>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">
                                <el-button @click="chooseCategory(scope.row)">
                                    选择
                                </el-button>

                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="category_show = false">取 消</el-button>
                </span>
            </el-dialog>
            <el-dialog :visible.sync="goods_show" width="60%" center title="选择商品">
                <div>
                    <div>
                        <el-input v-model="goods_keyword" style="width:70%"></el-input>
                        <el-button type="primary" @click="searchGoods()">搜索</el-button>
                    </div>
                    <el-table :data="goods_list" style="width: 100%;height:500px;overflow:auto" v-loading="loading">
                        <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                        <el-table-column label="商品信息">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center">
                                    <div style="margin-left:10px">[[scope.row.title]]</div>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">
                                <el-button @click="chooseGoods(scope.row)">
                                    选择
                                </el-button>

                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <el-row style="background-color:#fff;">
                    <el-col :span="24" align="center" migra style="padding:15px 5% 15px 0" v-loading="loading">
                        <el-pagination background  @current-change="currentChange"
                                       :current-page="current_page"
                                       layout="prev, pager, next"
                                       :page-size="Number(page_size)" :current-page="current_page" :total="page_total"></el-pagination>
                    </el-col>
                </el-row>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="goods_show = false">取 消</el-button>
                </span>
            </el-dialog>
            <el-dialog :visible.sync="store_show" width="60%" center title="选择门店">
                <div>
                    <div>
                        <el-input v-model="store_keyword" style="width:70%"></el-input>
                        <el-button type="primary" @click="searchStore()">搜索</el-button>
                    </div>
                    <el-table :data="store_list" style="width: 100%;height:500px;overflow:auto" v-loading="loading">
                        <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                        <el-table-column label="门店名称">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center">
                                    <div style="margin-left:10px">[[scope.row.store_name]]</div>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">
                                <el-button @click="chooseStore(scope.row)">
                                    选择
                                </el-button>

                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="store_show = false">取 消</el-button>
                </span>
            </el-dialog>
            <el-dialog :visible.sync="hotel_show" width="60%" center title="选择酒店">
                <div>
                    <div>
                        <el-input v-model="hotel_keyword" style="width:70%"></el-input>
                        <el-button type="primary" @click="searchHotel()">搜索</el-button>
                    </div>
                    <el-table :data="hotel_list" style="width: 100%;height:500px;overflow:auto" v-loading="loading">
                        <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                        <el-table-column label="酒店名称">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center">
                                    <div style="margin-left:10px">[[scope.row.hotel_name]]</div>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">
                                <el-button @click="chooseHotel(scope.row)">
                                    选择
                                </el-button>

                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="hotel_show = false">取 消</el-button>
                </span>
            </el-dialog>

            <el-dialog :visible.sync="member_tags_show" width="60%" center title="添加标签">
                <div>
                    <div>
                        <el-input v-model="member_tags_keyword" style="width:70%"></el-input>
                        <el-button type="primary" @click="searchMemberTags()">搜索</el-button>
                    </div>
                    <el-table :data="member_tags_list" style="width: 100%;height:500px;overflow:auto" v-loading="loading">
                        <el-table-column label="ID" prop="id" align="center" width="100px"></el-table-column>
                        <el-table-column label="标签名称">
                            <template slot-scope="scope">
                                <div v-if="scope.row" style="display:flex;align-items: center">
                                    <div style="margin-left:10px">[[scope.row.title]]</div>
                                </div>
                            </template>
                        </el-table-column>

                        <el-table-column prop="refund_time" label="操作" align="center" width="320">
                            <template slot-scope="scope">
                                <el-button @click="chooseMemberTags(scope.row)">
                                    选择
                                </el-button>

                            </template>
                        </el-table-column>
                    </el-table>
                </div>
                <span slot="footer" class="dialog-footer">
                    <el-button @click="member_tags_show = false">取 消</el-button>
                </span>
            </el-dialog>
        </div>
    </div>
    @include('public.admin.uploadImg')
    <script src="{{resource_get('static/yunshop/tinymce4.7.5/tinymce.min.js')}}"></script>
    @include('public.admin.tinymceee')
    <script>
        const category_url = '{!! yzWebFullUrl('goods.category.get-search-categorys-json') !!}';
        const goods_url = '{!! yzWebFullUrl('goods.goods.get-search-goods-json') !!}';
        const store_url = '{!! yzWebFullUrl('goods.goods.get-search-store-json') !!}';
        const hotel_url = '{!! yzWebFullUrl('goods.goods.get-search-hotel-json') !!}';
        const member_tags_url = '{!! yzWebFullUrl('goods.goods.get-search-member-tags-json') !!}';
        var app = new Vue({
            el:"#app",
            delimiters: ['[[', ']]'],
            name: 'test',
            data() {
                let id = {!! $id?:0 !!};
                console.log(id);
                return{
                    loading:false,
                    search_loading:false,
                    all_loading:false,
                    page_total:0,
                    page_size:0,
                    current_page:0,
                    id:id,
                    hotel_is_open:0,
                    store_is_open:0,
                    integral_is_open:0,
                    member_tags_is_open:0,
                    integral_name:'',
                    form:{
                        display_order:'0',
                        name:'',
                        status:0,
                        use_type:0,
                        enough:'',
                        level_limit:-1,
                        time_days:'0',
                        time_limit:0,
                        is_complex:0,
                        coupon_method:1,
                        get_type:0,
                        get_max:'1',
                        get_limit_type:false,
                        get_limit_max:1,
                        total:'1',
                        discount:'0',
                        deduct:'0',
                        time:[],
                        coupon_increase_records:[],
                        is_integral_exchange_coupon:0,
                        exchange_coupon_integral:''
                    },

                    category_ids : [],
                    category_names:[],
                    goods_ids:[],
                    goods_names:[],
                    store_ids:[],
                    store_names:[],
                    goods_id:[],
                    goods_name:[],
                    hotel_ids:[],
                    hotel_names:[],
                    member_list:[],
                    goods_list:[],
                    member_tags_ids:[],
                    member_tags_names:[],
                    member_tags_list:[],

                    goodsShow:false,
                    chooseGoodsItem:{},//选中的商品
                    // 分类
                    category_show:false,
                    category_list:[],
                    category_keyword:'',
                    // 商品
                    goods_show:false,
                    goods_keyword:'',
                    // 门店
                    store_show:false,
                    store_list:[],
                    store_keyword:'',
                    // 酒店
                    hotel_show:false,
                    hotel_list:[],
                    hotel_keyword:'',

                    // 标签
                    member_tags_show:false,
                    member_tags_keyword:'',

                    keyword:'',
                    submit_url:'',
                    showVisible:false,

                    uploadShow:false,
                    chooseImgName:'',

                    uploadImg1:'',
                    rules:{
                        name:{ required: true, message: '请输入优惠券名称'},
                        enough:{ required: true, message: '请输入使用条件 - 订单金额'},
                    },
                    real_search_form:'',

                    //* 门店+商品
                    use_conditions:{
                        is_all_store:0,
                        is_all_good:0,
                        good_ids:[],
                        good_names:[],
                        store_ids:[],
                        store_names:[]
                    },
                    dialogTableVisible: false

                }
            },
            created() {


            },
            mounted() {
                if(this.id) {
                    this.submit_url = '{!! yzWebFullUrl('coupon.coupon.edit') !!}';
                }
                else {
                    this.submit_url = '{!! yzWebFullUrl('coupon.coupon.create') !!}';
                }
                this.getData();

            },
            methods: {
                getData() {
                    let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                    this.$http.post(this.submit_url,{id:this.id}).then(function (response) {
                            if (response.data.result){
                                this.hotel_is_open = response.data.data.hotel_is_open;
                                this.store_is_open = response.data.data.store_is_open;
                                this.integral_is_open = response.data.data.integral_is_open;
                                this.member_tags_is_open = response.data.data.member_tags_is_open;
                                this.integral_name = response.data.data.integral_name;
                                this.member_list = response.data.data.memberlevels || [];
                                if(this.id && response.data.data.coupon) {
                                    let coupon = response.data.data.coupon;
                                    this.form.display_order = coupon.display_order;
                                    this.form.name = coupon.name;
                                    this.form.status = coupon.status;
                                    this.form.use_type = coupon.use_type;
                                    this.form.enough = coupon.enough;
                                    this.form.level_limit = coupon.level_limit;
                                    this.form.time_days = coupon.time_days;
                                    this.form.time_limit = coupon.time_limit;
                                    this.form.is_complex = coupon.is_complex;
                                    this.form.coupon_method = coupon.coupon_method;
                                    this.form.use_type = coupon.use_type;
                                    this.form.get_type = coupon.get_type;
                                    this.form.get_max = coupon.get_max;
                                    this.form.get_limit_type = coupon.get_limit_type ? true : false;
                                    this.form.get_limit_max = coupon.get_limit_max;
                                    this.form.total = coupon.total;
                                    this.form.discount = coupon.discount;
                                    this.form.deduct = coupon.deduct;
                                    this.form.coupon_increase_records = coupon.coupon_increase_records;
                                    this.form.is_integral_exchange_coupon = coupon.is_integral_exchange_coupon;
                                    this.form.exchange_coupon_integral = coupon.exchange_coupon_integral;
                                    this.form.content = coupon.content;

                                    this.category_ids = response.data.data.category_ids || [];
                                    this.category_names = response.data.data.category_names || [];


                                    this.store_ids = response.data.data.store_ids || [];
                                    this.store_names = response.data.data.store_names || [];

                                    this.member_tags_ids = response.data.data.member_tags_ids || [];
                                    this.member_tags_names = response.data.data.member_tags_names || [];

                                    if(this.form.use_type==2) {
                                        this.goods_ids = response.data.data.goods_ids || [];
                                        this.goods_names = response.data.data.goods_names || [];
                                    }
                                    else if(this.form.use_type==8) {
                                        this.goods_id = response.data.data.goods_ids || [];
                                        this.goods_name = response.data.data.goods_names || [];
                                    }else if(this.form.use_type==9) {
                                        const isSelectedAllGoods=Boolean(response.data.data.coupon.use_conditions.is_all_good);
                                        const isSelectedAllStores=Boolean(response.data.data.coupon.use_conditions.is_all_store);
                                        let goodIds=[];
                                        const goodNames=[];
                                        if(isSelectedAllGoods===false){
                                            goodIds=response.data.data.coupon.use_conditions.good_ids;
                                            const goodsData=response.data.data.goods;
                                            for (const goodsItem of goodsData) {
                                                goodNames.push(goodsItem['title']);
                                            }
                                        }

                                        let storeIds=[];
                                        const storeNames=[];
                                        if(isSelectedAllStores===false){
                                            storeIds=response.data.data.coupon.use_conditions.store_ids;
                                            const storeData=response.data.data.store;
                                            for (const storeItem of storeData) {
                                                storeNames.push(storeItem['store_name']);
                                            }
                                        }

                                        this.use_conditions.good_ids = goodIds || [];
                                        this.use_conditions.good_names = goodNames || [];
                                        this.use_conditions.store_ids = storeIds || [];
                                        this.use_conditions.store_names = storeNames || [];
                                        this.use_conditions.is_all_good =isSelectedAllGoods
                                        this.use_conditions.is_all_store = isSelectedAllStores
                                        console.log( this.use_conditions);
                                    }
                                    if(this.goods_names) {
                                        this.goods_names.forEach((item,index) => {
                                            if(item) {
                                                this.goods_names[index] = this.escapeHTML(item);
                                            }
                                        })
                                    }
                                    if(this.goods_name) {
                                        this.goods_name.forEach((item,index) => {
                                            if(item) {
                                                this.goods_name[index] = this.escapeHTML(item);
                                            }
                                        })
                                    }
                                    if(response.data.data.hotel_ids) {
                                        response.data.data.hotel_ids.forEach((item,index) => {
                                            this.hotel_names.push(item.hotel_name);
                                            this.hotel_ids.push(item.id);
                                        })
                                    }
                                    if(response.data.data.timeend) {
                                        this.form.time = [];
                                        this.form.time[0] = response.data.data.timestart*1000;
                                        this.form.time[1] = response.data.data.timeend*1000;
                                        console.log(this.form)
                                    }

                                }
                                if(!response.data.data.timeend) {
                                    this.form.time.push(Math.round(new Date()),Math.round(new Date())+(24*60*60*1000*7))
                                }
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

                openDia(type) {
                    if(type==1) {
                        this.category_show = true;
                    }
                    else if(type==2) {
                        this.goods_show = true;
                    }
                    else if(type==4) {
                        this.store_show = true;
                    }
                    else if(type==7) {
                        this.hotel_show = true;
                    }
                    else if(type==8) {
                        this.goods_show = true;
                    }else if(type==9) {
                        this.goods_show = true;
                    } else if(type==10) {
                        this.member_tags_show = true;
                    }
                },
                currentChange(val) {
                    this.loading = true;
                    this.$http.post(goods_url,{page:val,keyword:this.real_search_form,except_supplier:1}).then(function (response){
                            if (response.data.result){
                                let datas = response.data.data.goods;
                                this.goods_list=datas.data
                                this.page_total = datas.total;
                                this.page_size = datas.per_page;
                                this.current_page = datas.current_page;
                                this.real_search_form=this.goods_keyword;
                                this.goods_list.forEach((item,index) => {
                                    if(item.title) {
                                        item.title = this.escapeHTML(item.title);
                                    }
                                });
                                this.loading = false;
                            } else {
                                this.$message({message: response.data.msg,type: 'error'});
                            }
                        },function (response) {
                            console.log(response);
                            this.loading = false;
                        }
                    );
                },
                searchGoods() {
                    let that = this;
                    this.loading = true;
                    this.$http.post(goods_url,{keyword:this.goods_keyword,except_supplier:1}).then(response => {
                        if (response.data.result) {
                            let datas = response.data.data.goods;
                            this.goods_list=datas.data
                            this.page_total = datas.total;
                            this.page_size = datas.per_page;
                            this.current_page = datas.current_page;
                            this.real_search_form=this.goods_keyword;
                            this.goods_list.forEach((item,index) => {
                                if(item.title) {
                                    item.title = this.escapeHTML(item.title);
                                }
                            });
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        this.loading = false;
                    },response => {
                        this.loading = false;
                    });
                },
                chooseGoods(item) {
                    // 兑换券
                    if(this.form.use_type == 8) {
                        if(this.goods_id&&this.goods_id.length>=1) {
                            this.$message.error("兑换券不能添加多个商品");
                            return;
                        }
                        else {
                            this.goods_id.push(item.id)
                            this.goods_name.push(item.title)
                        }
                        return;
                    }
                    // 指定商品
                    let is_exist = 0;
                    if(this.form.use_type == 9){
                        this.use_conditions.good_ids.some((item1,index) => {
                            if(item1 == item.id) {
                                is_exist = 1;
                                this.$message.error("请勿重复选择");
                                return true;
                            }
                        });
                        if(is_exist == 1) {
                            return;
                        }
                        this.use_conditions.good_ids.push(item.id);
                        this.use_conditions.good_names.push(item.title);
                        this.use_conditions.is_all_good=false;
                    }else{
                        this.goods_ids.some((item1,index) => {
                            if(item1 == item.id) {
                                is_exist = 1;
                                this.$message.error("请勿重复选择");
                                return true;
                            }
                        });
                        if(is_exist == 1) {
                            return;
                        }
                        this.goods_ids.push(item.id);
                        this.goods_names.push(item.title);
                    }
                },
                closeGoods(index) {
                    if(this.form.use_type == 2){
                        this.goods_ids.splice(index,1)
                        this.goods_names.splice(index,1)
                    }
                    else if(this.form.use_type == 8) {
                        this.goods_id = [];
                        this.goods_name = [];
                    }else if(this.form.use_type == 9) {
                        this.use_conditions.good_ids.splice(index,1)
                        this.use_conditions.good_names.splice(index,1)
                    }
                },

                searchCategory() {
                    let that = this;
                    this.loading = true;
                    this.$http.post(category_url,{keyword:this.category_keyword}).then(response => {
                        if (response.data.result) {
                            this.category_list = response.data.data;
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        this.loading = false;
                    },response => {
                        this.loading = false;
                    });
                },
                chooseCategory(item) {
                    let is_exist = 0
                    this.category_ids.some((item1,index) => {
                        if(item1 == item.id) {
                            is_exist = 1;
                            this.$message.error("请勿重复选择");
                            return true;
                        }
                    })
                    if(is_exist == 1) {
                        return;
                    }
                    this.category_ids.push(item.id)
                    this.category_names.push(item.name)
                    console.log(this.category_names)
                },
                closeCategory(index) {
                    console.log(index)
                    this.category_ids.splice(index,1)
                    this.category_names.splice(index,1)

                },

                // 门店
                searchStore() {
                    let that = this;
                    this.loading = true;
                    this.$http.post(store_url,{keyword:this.store_keyword}).then(response => {
                        if (response.data.result) {
                            this.store_list = response.data.data;
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        this.loading = false;
                    },response => {
                        this.loading = false;
                    });
                },
                chooseStore(item) {
                    let is_exist = 0
                    if(this.form.use_type==9){
                        this.use_conditions.store_ids.some((item1,index) => {
                            if(item1 == item.id) {
                                is_exist = 1;
                                this.$message.error("请勿重复选择");
                                return true;
                            }
                        });
                        if(is_exist == 1) {
                            return;
                        }
                        this.use_conditions.store_ids.push(item.id)
                        this.use_conditions.store_names.push(item.store_name)
                        this.use_conditions.is_all_store=false;
                    }else{
                        this.store_ids.some((item1,index) => {
                            if(item1 == item.id) {
                                is_exist = 1;
                                this.$message.error("请勿重复选择");
                                return true;
                            }
                        });
                        if(is_exist == 1) {
                            return;
                        }
                        this.store_ids.push(item.id)
                        this.store_names.push(item.store_name)
                    }
                    console.log(this.store_names)
                },
                closeStore(index) {
                    if(this.form.use_type===9){
                        this.use_conditions.store_ids.splice(index,1)
                        this.use_conditions.store_names.splice(index,1)
                        return;
                    }
                    this.store_ids.splice(index,1)
                    this.store_names.splice(index,1)
                },
                // 门店

                // 酒店
                searchHotel() {
                    let that = this;
                    this.loading = true;
                    this.$http.post(hotel_url,{keyword:this.hotel_keyword}).then(response => {
                        if (response.data.result) {
                            this.hotel_list = response.data.data;
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        this.loading = false;
                    },response => {
                        this.loading = false;
                    });
                },
                chooseHotel(item) {
                    let is_exist = 0
                    this.hotel_ids.some((item1,index) => {
                        if(item1 == item.id) {
                            is_exist = 1;
                            this.$message.error("请勿重复选择");
                            return true;
                        }
                    })
                    if(is_exist == 1) {
                        return;
                    }
                    this.hotel_ids.push(item.id)
                    this.hotel_names.push(item.hotel_name)
                },
                closeHotel(index) {
                    console.log(index)
                    this.hotel_ids.splice(index,1)
                    this.hotel_names.splice(index,1)
                },
                // 酒店
                submitForm(formName) {
                    let that = this;
                    if(this.form.coupon_method == 1) {
                        if(!this.form.deduct) {
                            this.$message.error("立减不能为空")
                            return;
                        }
                        if(!this.form.discount) {
                            this.$message.error("折扣不能为空")
                            return;
                        }
                    }
                    let json = {
                        coupon:{
                            display_order:this.form.display_order,
                            name:this.form.name,
                            status:this.form.status || '0',
                            use_type:this.form.use_type || '0',
                            enough:this.form.enough,
                            level_limit:this.form.level_limit,
                            time_limit:this.form.time_limit || '0',
                            time_days:this.form.time_days,
                            is_complex:this.form.is_complex,
                            coupon_method:this.form.coupon_method,
                            get_type:this.form.get_type,
                            get_max:this.form.get_max,
                            get_limit_type:this.form.get_limit_type ? 1 : 0,
                            get_limit_max:this.form.get_limit_max,
                            total:this.form.total,
                            deduct:this.form.deduct || '0',
                            discount:this.form.discount || '0',
                            content:this.form.content,
                        },
                        category_ids:this.category_ids,
                        category_names:this.category_names,
                        goods_ids:this.goods_ids,
                        goods_names:this.goods_names,
                        store_ids:this.store_ids,
                        store_names:this.store_names,
                        goods_id:this.goods_id,
                        goods_name:this.goods_name,
                        hotel_ids:this.hotel_ids,
                        hotel_names:this.hotel_names,
                        member_tags_ids:this.member_tags_ids,
                        member_tags_names:this.member_tags_names,
                        is_integral_exchange_coupon:this.form.is_integral_exchange_coupon,
                        exchange_coupon_integral:this.form.exchange_coupon_integral,
                        time:{start:"",end:""}
                    };
                    if(this.form.use_type===9){
                        json['coupon']['use_conditions']={
                            is_all_store:Number(this.use_conditions.is_all_store),
                            is_all_good:Number(this.use_conditions.is_all_good),
                            good_ids:this.use_conditions.good_ids,
                            store_ids:this.use_conditions.store_ids
                        }
                    }

                    if(this.form.time && this.form.time.length>0) {
                        json.time.start = this.form.time[0]/1000;
                        json.time.end = this.form.time[1]/1000;
                    }
                    if(this.id) {
                        json.id = this.id
                    }
                    this.$refs[formName].validate((valid) => {
                        if (valid) {
                            let loading = this.$loading({target:document.querySelector(".content"),background: 'rgba(0, 0, 0, 0)'});
                            this.$http.post(this.submit_url,json).then(response => {
                                if (response.data.result) {
                                    this.$message({type: 'success',message: '操作成功!'});
                                    this.goBack();
                                } else {
                                    let msg = response.data.msg;
                                    let tips = '';
                                    try {
                                        msg = JSON.stringify(res);
                                    }catch (e) {
                                    }
                                    if(msg instanceof Object){
                                        for (let key in msg){
                                            tips += msg[key][0];
                                        }
                                    }
                                    this.$message({message: tips ? tips : msg,type: 'error'});
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

                //标签
                searchMemberTags() {
                    let that = this;
                    this.loading = true;
                    this.$http.post(member_tags_url,{keyword:this.member_tags_keyword}).then(response => {
                        if (response.data.result) {
                            this.member_tags_list = response.data.data;
                        } else {
                            this.$message({message: response.data.msg,type: 'error'});
                        }
                        this.loading = false;
                    },response => {
                        this.loading = false;
                    });
                },
                chooseMemberTags(item) {
                    let is_exist = 0
                    this.member_tags_ids.some((item1,index) => {
                        if(item1 == item.id) {
                            is_exist = 1;
                            this.$message.error("请勿重复选择");
                            return true;
                        }
                    })
                    if(is_exist == 1) {
                        return;
                    }
                    this.member_tags_ids.push(item.id)
                    this.member_tags_names.push(item.title)
                },
                closeMemberTags(index) {
                    console.log(index)
                    this.member_tags_ids.splice(index,1)
                    this.member_tags_names.splice(index,1)

                },

                goBack() {
                    history.go(-1)
                },
                goParent() {
                    window.location.href = `{!! yzWebFullUrl('coupon.coupon.index') !!}`;
                },
                openUpload(str) {
                    this.chooseImgName = str;
                    this.uploadShow = true;
                },
                changeProp(val) {
                    if(val == true) {
                        this.uploadShow = false;
                    }
                    else {
                        this.uploadShow = true;
                    }
                },
                sureImg(name,image,image_url) {
                    console.log(name)
                    console.log(image)
                    console.log(image_url)
                    this.form[name] = image;
                    this.form[name+'_url'] = image_url;
                },
                clearImg(str) {
                    this.form[str] = "";
                    this.form[str+'_url'] = "";
                    this.$forceUpdate();
                },
                // 字符转义
                escapeHTML(a) {
                    a = "" + a;
                    return a.replace(/&amp;/g, "&").replace(/&lt;/g, "<").replace(/&gt;/g, ">").replace(/&quot;/g, "\"").replace(/&apos;/g, "'");;
                },
                selectAllGoods(checked){
                    if(checked){
                        this.use_conditions.good_ids = [];
                        this.use_conditions.good_names =  [];
                    }
                },
                selectAllStores(checked){
                    if(checked){
                        this.use_conditions.store_ids =  [];
                        this.use_conditions.store_names = [];
                    }
                },
                couponAddCount(id) {
                    const that = this
                    this.$prompt('输入增加的优惠劵数量', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                        inputPattern: /^[0-9]*$/,
                        inputErrorMessage: '请输入整型'
                    }).then(({ value }) => {
                        let params = {
                            id: id,
                            total: value
                        }
                        that.$http.post("{!! yzWebFullUrl('coupon.coupon.add-coupon-count') !!}", params).then(response => {
                            if (response.data.result === 1) {
                                that.form.total = response.data.data.total
                                this.$message({
                                    type: 'success',
                                    message: '成功添加优惠券: ' + value + '张'
                                });
                            }else{
                                this.$message({
                                    type: 'fail',
                                    message: '添加失败, 请重试'
                                });
                            }
                        }), function (res) {
                            console.log(res);
                        };

                    }).catch(() => {
                        this.$message({
                            type: 'info',
                            message: '取消优惠券添加'
                        });
                    });
                }
            },
        })

    </script>
@endsection


