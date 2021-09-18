{{--<link href="{{static_url('yunshop/goods/goods.css')}}" media="all" rel="stylesheet" type="text/css"/>--}}

<style>
    .brand-select {
        position:relative;
        z-index:6
    }
    .brand-popup-mask {
        display:none;
        position:fixed;
        z-index:5;
        top:0;
        left:0;
        width:100%;
        height:100vh;
        background:transparent;
    }
    .brands-popup {
        display:none;
        position:absolute;
        z-index:6;
        padding:3px;
        width:95%;
        user-select: none;
        background:white;
        box-shadow: 0 3px 5px rgba(0,0,0,0.2);
        box-sizing: border-box;
        border-radius:0 0 3px 3px;
    }
    .brand-item {
        padding:5px;
        border-radius:3px;
    }
    .brand-item:hover {
        color:white;
        background:brown;
    }
</style>


<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">排序</label>
    <div class="col-sm-9 col-xs-12">
        <input type="text" name="goods[display_order]" id="displayorder" maxlength="9" onkeyup="this.value=this.value.replace(/\D/g,'')" onafterpaste="this.value=this.value.replace(/\D/g,'')"  class="form-control" value="{{$goods['display_order']}}" />
        <span class='help-block'>数字大的排名在前,默认排序方式为创建时间，注意：输入最大数为9位数，只能输入数字</span>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span >*</span>{{$lang['shopname']}}</label>
    <div class="col-sm-9 col-xs-12">
        <input type="text" name="goods[title]" id="goodsname" class="form-control" value="{{$goods['title']}}" />
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span>*</span>商品分类</label>
    <div class="col-sm-8 col-xs-12 category-container">

        {!!$catetory_menus!!}

    </div>

</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
    <div class="btn btn-info col-sm-2 col-xs-2 @if (isset($type) && $type == 'edit') editCategory @else plusCategory @endif">
        添加分类
    </div>
</div>



<div class="form-group brands-form-item">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">品牌</label>
    <div class="col-sm-9 col-xs-12">
        {{--<select name="goods[brand_id]" id="brand" style="width:95%">--}}
            <div class="brand-popup-mask" onclick="hiddenBrandPopup()"></div>
            <div class="brand-select">
                <input type="hidden" name="goods[brand_id]" @if ($goods['brand_id']) value="{{$goods['brand_id']}}" @endif />
                <input class="form-control" type="text" oninput="inputingBrandName(this.value)" placeholder="请输入品牌名称" onfocus="showBrandList()" value="{{$brands['name']}}" />
                <div class="brands-popup">
                    <ul class="brand-list">
                    </ul>
                </div>
            </div>

        <!-- <select name="goods[brand_id]" id="brand" style="width:95%">
            <option value="0">请选择品牌</option>
            @if (!empty($brands))
            @foreach ($brands as $brand)
                <option value="{{$brand['id']}}" @if ($brand['id'] == $goods['brand_id']) selected @endif>{{$brand['name']}}</option>
                @endforeach
        @endif
                </select>
                </select> -->
    </div>
</div>



<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商品类型</label>
    <div class="col-sm-9 col-xs-12">
        <div style="float: left" id="ttttype">
            {{-- 2019-01-16 删 onclick="$('#product').show();$('#type_virtual').hide();$('#divdeposit').hide();--}}
            <label for="isshow3" class="radio-inline"><input type="radio" name="goods[type]" value="1" id="isshow3" @if (empty($goods['type']) || $goods['type'] == 1) checked="true" @endif
                onclick="$('#need_address_idx').hide();$(':radio[name=\'goods[need_address]\'][value=\'0\']').prop('checked', true);" />
                实体商品
            </label>
            <label for="isshow4" class="radio-inline"><input type="radio" name="goods[type]" value="2" id="isshow4"  @if ($goods['type'] == 2) checked="true" @endif  onclick="$('#need_address_idx').show();" /> 虚拟商品</label>
        </div>
    </div>
</div>


{{--<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商品类型2</label>
    <div class="col-sm-9 col-xs-12">
        <div style="float: left" id="ttttype2">
            <label class="radio-inline"><input type="radio" name="goods[type2]" value="1" id="isshow3" @if (empty($goods['type2']) || $goods['type2'] == 1) checked="true" @endif/>
                普通商品
            </label>
            <input type="hidden" id="plugin_id" value="{{$plugin_id}}">
            @if (app('plugins')->isEnabled('lease-toy'))
                <label class="radio-inline"><input type="radio" name="goods[type2]" value="2" id="isshow4"  @if ($goods['type2'] == 2) checked="true" @endif/> 租赁商品</label>
            @endif
            @if (app('plugins')->isEnabled('video-demand'))
                <label class="radio-inline"><input type="radio" name="goods[type2]" value="3" id="isshow4"  @if ($goods['type2'] == 3) checked="true" @endif/> 课程商品</label>
            @endif
        </div>
    </div>
</div>--}}

<div id="need_address_idx" class="form-group" @if ($goods['type'] != 2) style="display: none"  @endif>
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">下单是否需要地址</label>
    <div class="col-sm-9 col-xs-12">
        <div style="float: left">
            <label class="radio-inline"><input type="radio" name="goods[need_address]" value="0" @if (empty($goods['need_address']) || $goods['need_address'] == 0) checked="true" @endif onclick="" /> 需要地址</label>
            <label class="radio-inline"><input type="radio" name="goods[need_address]" value="1" @if ($goods['need_address'] == 1) checked="true" @endif  onclick="" /> 不需要地址</label>
        </div>

    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span >*</span>商品单位</label>
    <div class="col-sm-6 col-xs-6">
        <input type="text" name="goods[sku]" id="unit" class="form-control" value="{{$goods['sku']}}" />
        <span class="help-block">如: 个/件/包</span>

    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商品属性</label>
    <div class="col-sm-9 col-xs-12" >
        <label for="isrecommand" class="checkbox-inline">
            <input type="checkbox" name="goods[is_recommand]" value="1" id="isrecommand" @if ($goods['is_recommand'] == 1) checked="true" @endif /> 推荐
        </label>
        <label for="isnew" class="checkbox-inline">
            <input type="checkbox" name="goods[is_new]" value="1" id="isnew" @if ($goods['is_new'] == 1) checked="true" @endif /> 新上
        </label>
        <label for="ishot" class="checkbox-inline">
            <input type="checkbox" name="goods[is_hot]" value="1" id="ishot" @if ($goods['is_hot'] == 1) checked="true" @endif /> 热卖
        </label>
        <label for="isdiscount" class="checkbox-inline">
            <input type="checkbox" name="goods[is_discount]" value="1" id="isdiscount" @if ($goods['is_discount'] == 1) checked="true" @endif /> 促销
        </label>

    </div>
</div>

{{--<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span >*</span>{{$lang['mainimg']}}</label>
    <div class="col-sm-9 col-xs-12 col-md-6 detail-logo">
        {!! app\common\helpers\ImageHelper::tplFormFieldImage('goods[thumb]', $goods['thumb']) !!}
        <span class="help-block">建议尺寸: 640 * 640 ，或正方型图片 </span>
    </div>
</div>--}}
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span >*</span>{{$lang['mainimg']}}</label>
    <div class="col-sm-9 col-xs-12 col-md-6 detail-logo">
		<div class="input-group ">
			<input type="text" name="goods[thumb]" value="{{$goods['thumb']}}" class="form-control" autocomplete="off">
			<span class="input-group-btn">
				<button class="btn btn-default" type="button" onclick="showImageDialog2(this,'');">选择图片</button>
			</span>
		</div>
		<div class="input-group " style="margin-top:.5em;">
			<img src="{{yz_tomedia($goods['thumb'])}}" onerror="this.src='{{static_url('resource/images/nopic.jpg')}}'; this.title='图片未找到.'" class="img-responsive img-thumbnail" width="150">
			<em class="close" style="position:absolute; top: 0px; right: -14px;" title="删除这张图片" onclick="deleteImage2(this)">×</em>
		</div>
        <span class="help-block">建议尺寸: 640 * 640 ，或正方型图片 </span>
    </div>
</div>
{{--<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">其他图片</label>
    <div class="col-sm-9  col-md-6 col-xs-12">
        {!! app\common\helpers\ImageHelper::tplFormFieldMultiImage('goods[thumb_url]',$goods['thumb_url']) !!}
        <span class="help-block">建议尺寸: 640 * 640 ，或正方型图片 </span>
    </div>
</div>--}}
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">其他图片</label>
    <div class="col-sm-9  col-md-6 col-xs-12">
        <div class="input-group">
            <input type="text" class="form-control" readonly="readonly" value="" placeholder="批量上传图片" autocomplete="off">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" onclick="showImageDialog2(this,'more','goods[thumb_url]',0);">选择图片</button>
                <input type="hidden" value="goods[thumb_url]">
            </span>
        </div>
        <div class="input-group multi-img-details" style='display: flex;flex-wrap: wrap;'>
            @foreach($goods['thumb_url'] as $item)
            <div class="multi-item">
                <div class="img_box">
                    <img src="{{yz_tomedia($item)}}" onerror="this.src='{{static_url('resource/images/nopic.jpg')}}'; this.title='图片未找到.'" class="img-responsive img-thumbnail" title="图片未找到.">
                </div>
                <input type="hidden" name="goods[thumb_url][]" value="{{$item}}">
                <em class="close" title="删除这张图片" onclick="deleteMultiImage2(this,0)">×</em>
            </div>
            @endforeach
            
        </div>
        <span class="help-block">建议尺寸: 640 * 640 ，或正方型图片 </span>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">首图视频</label>
    <div class="col-sm-9  col-md-6 col-xs-12">
        {!! app\common\helpers\ImageHelper::tplFormFieldVideo('widgets[video][goods_video]', $goods->hasOneGoodsVideo->goods_video) !!}
        {{--{!! tpl_form_field_video('widgets[video][goods_video]',$goods->hasOneGoodsVideo->goods_video) !!}--}}
        <span class="help-block">设置后商品详情首图默认显示视频，建议时长9-30秒</span>

    </div>
</div>
{{--<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">视频封面</label>
    <div class="col-sm-9 col-xs-12 col-md-6 detail-logo">
        {!! app\common\helpers\ImageHelper::tplFormFieldImage('widgets[video][video_image]', $goods->hasOneGoodsVideo->video_image) !!}
        <span class="help-block">不填默认商品主图</span>
    </div>
</div>--}}
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">视频封面</label>
    <div class="col-sm-9 col-xs-12 col-md-6 detail-logo">
		<div class="input-group ">
			<input type="text" name="widgets[video][video_image]" value="{{$goods->hasOneGoodsVideo->video_image}}" class="form-control" autocomplete="off">
			<span class="input-group-btn">
				<button class="btn btn-default" type="button" onclick="showImageDialog2(this,'');">选择图片</button>
			</span>
		</div>
		<div class="input-group " style="margin-top:.5em;">
			<img src="{{yz_tomedia($goods->hasOneGoodsVideo->video_image)}}" onerror="this.src='{{static_url('resource/images/nopic.jpg')}}'; this.title='图片未找到.'" class="img-responsive img-thumbnail" width="150">
			<em class="close" style="position:absolute; top: 0px; right: -14px;" title="删除这张图片" onclick="deleteImage2(this)">×</em>
		</div>
        <span class="help-block">不填默认商品主图</span>
    </div>
</div>

<div class="form-group">
    <label class=" col-sm-3 col-md-2 control-label">商品编号</label>
    <div class="col-sm-4 col-xs-12">
        <input type="text" name="goods[goods_sn]" id="productsn" class="form-control" value="{{$goods['goods_sn']}}" />
    </div>
</div>
<div class="form-group">
    <label class=" col-sm-3 col-md-2 control-label">商品条码</label>
    <div class="col-sm-4 col-xs-12">
        <input type="text" name="goods[product_sn]" id="productsn" class="form-control" value="{{$goods['product_sn']}}" />
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span ></span>商品价格</label>
    <div class="col-sm-9 col-xs-12 form-inline">
        <div class="input-group form-group col-sm-3">
            <span class="input-group-addon">现价</span>
            <input type="text" name="goods[price]" id="product_price" class="form-control" value="{{$goods['price'] ? : 0}}" />
            <span class="input-group-addon">元</span>
        </div>
        <div class="input-group form-group col-sm-3">
            <span class="input-group-addon">原价</span>
            <input type="text" name="goods[market_price]" id="market_price" class="form-control" value="{{$goods['market_price']? : 0}}" />
            <span class="input-group-addon">元</span>
        </div>
        <div class="input-group form-group col-sm-3">
            <span class="input-group-addon">成本</span>
            <input type="text" name="goods[cost_price]" id="costprice" class="form-control" value="{{$goods['cost_price'] ? : 0}}" />
            <span class="input-group-addon">元</span>
        </div>
        <span class='help-block'>尽量填写完整，有助于于商品销售的数据分析</span>

    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">重量</label>
    <div class="col-sm-6  col-xs-12">
        <div class="input-group col-md-3">
            <input type="text" name="goods[weight]" id="weight" class="form-control" value="{{$goods['weight']?$goods['weight']:0}}" />
            <span class="input-group-addon">克</span>
        </div>
        <div class='help-block'>商品重量设置为空或者0，则不计算重量</div>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">体积</label>
    <div class="col-sm-6  col-xs-12">
        <div class="input-group col-md-3">
            <input type="text" name="goods[volume]" id="volume" class="form-control" value="{{$goods['volume']?$goods['volume']:0}}" />
            <span class="input-group-addon" style="font-size: 20px;">m³</span>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span >*</span>第三方销量</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group  col-md-3 form-group col-sm-3">
            <input type="text" onkeyup="value=value.replace(/[^\d]/g,'')" name="goods[virtual_sales]" id="total" class="form-control" value="{{$goods['virtual_sales']}}" />
            <span class="input-group-addon">件</span>
        </div>
        <!--<span class="help-block">前端真实销量 = 虚拟销量 + 真实销量</span>-->
        <span class="help-block">第三方销量为平台在其他第三方平台销量同步汇总，前端显示销量=第三方销量+商城销量</span>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label"><span >*</span>库存</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group  col-md-3 form-group col-sm-3">
            <input type="text" name="goods[stock]" id="total" class="form-control" value="{{$goods['stock']}}" />
            <span class="input-group-addon">件</span>
        </div>
        <span class="help-block">商品的剩余数量, 如启用多规格或为虚拟卡密产品，则此处设置无效，请移至“商品规格”或“虚拟物品插件”中设置</span>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">减库存方式</label>
    <div class="col-sm-9 col-xs-12">
        <label for="totalcnf1" class="radio-inline"><input type="radio" name="goods[reduce_stock_method]" value="0" id="totalcnf1" @if($goods['withhold_stock'] > 0) disabled="disabled" @endif @if (empty($goods) || $goods['reduce_stock_method'] == 0) checked="true" @endif /> 拍下减库存</label>
        &nbsp;&nbsp;&nbsp;
        <label for="totalcnf2" class="radio-inline"><input type="radio" name="goods[reduce_stock_method]" value="1" id="totalcnf2" @if($goods['withhold_stock'] > 0) disabled="disabled" @endif @if (!empty($goods) && $goods['reduce_stock_method'] == 1) checked="true" @endif /> 付款减库存</label>
        &nbsp;&nbsp;&nbsp;
        <label for="totalcnf3" class="radio-inline"><input type="radio" name="goods[reduce_stock_method]" value="2" id="totalcnf3" @if($goods['withhold_stock'] > 0) disabled="disabled" @endif  @if (!empty($goods) && $goods['reduce_stock_method'] == 2) checked="true" @endif /> 永不减库存</label>
        <span class="help-block">拍下减库存：买家拍下商品即减少库存，存在恶拍风险。秒杀、超低价等热销商品，如需避免超卖可选此方式。<br/>
            付款减库存：买家拍下并完成付款方减少库存，存在超卖风险。如需减少恶拍、提高回款效率，可选此方式。
            <br/>
            订单支付前关闭或取消返还库存,订单支付后退换货的订单不返还库存。
        </span>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">预扣库存</label>
    <div class="col-sm-6 col-xs-12">
        <div class="input-group  col-md-3 form-group col-sm-3">
            <input type="text" disabled="disabled" id="total" class="form-control" value="{{$goods['withhold_stock']}}" />
            <span class="input-group-addon">件</span>
        </div>
        <span class="help-block">
            拍下减库存时,下单即锁定商品库存。<br/>
            付款减库存时,用户点击支付订单后,1分钟内锁定商品库存。
            <br/>
            存在预扣库存时，无法编辑商品库存和减库存方式设置。
        </span>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">不可退货退款</label>
    <div class="col-sm-9 col-xs-12">
        <label for="norefund1" class="radio-inline">
            <input type="radio" name="goods[no_refund]" value="1" id="norefund1" @if ($goods['no_refund'] == 1) checked="true" @endif /> 是</label>
        &nbsp;&nbsp;&nbsp;
        <label for="norefund2" class="radio-inline">
            <input type="radio" name="goods[no_refund]" value="0" id="norefund2"  @if ($goods['no_refund'] == 0) checked="true" @endif /> 否</label>
        <span class="help-block"></span>

    </div>
</div>

@include('public.admin.customImg')
<!-->
@if(\app\common\services\PermissionService::can('goods_goods_putaway'))
@section('isputaway')
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 col-md-2 control-label">{{$lang['isputaway']}}</label>
        <div class="col-sm-9 col-xs-12">
            <label for="isshow1" class="radio-inline"><input type="radio" name="goods[status]" value="1" id="isshow1" @if ($goods['status'] == 1) checked="true" @endif /> 是</label>
            <label for="isshow2" class="radio-inline"><input type="radio" name="goods[status]" value="0" id="isshow2" @if ($goods['status'] == 0) checked="true" @endif /> 否</label>
            <span class="help-block"></span>
        </div>
    </div>
@show
@endif
<script type="text/javascript">
    // $('#brand').select2();

    let searchTimeHandle=null;
    function showBrandList(){
        $(".brand-popup-mask").fadeIn();
        if($(".brand-list").children().length>0){
            $(".brands-popup").fadeIn();
        }
    }
    function selectBrand(id,name){
        if(id!==0){
            $(".brand-select input[name='goods[brand_id]']").val(id);
            $(".brand-select input.form-control").val(name);
        }
        hiddenBrandPopup();
    }
    function createBrandItem(id,name){
        const LiEl=document.createElement("li");
        LiEl.innerText=name;
        LiEl.className="brand-item";
        LiEl.setAttribute("data-id",id);
        LiEl.onclick=selectBrand.bind(null,id,name);
        return LiEl;
    }
    function inputingBrandName(value){
        if(!value){
            return;
        }
        $(".brand-list").html("");
        $(".brand-popup-mask").fadeIn();
        $(".brands-popup").fadeIn();
        if(searchTimeHandle){
            clearTimeout(searchTimeHandle);
        }
        searchTimeHandle=setTimeout(()=>{
            $(".brand-list").html("");
            $(".brand-list").append(createBrandItem(0,"查询中请稍等"));
            $.ajax("{!! yzWebFullUrl('goods.brand.search-brand') !!}&keyword="+value,{
                type:"POST",
                success({data,msg,result}){
                    $(".brand-list").html("");
                    if(result==0){
                        alert(msg);
                        return;
                    }
                    const lis=[];
                    if(data.length>0){
                        data.forEach(item=>{
                            lis.push(createBrandItem(item.id,item.name));
                        });
                    }else{
                        lis.push(createBrandItem(0,"未查询到相关品牌"));
                    }

                    $(".brand-list").append(lis);
                    searchTimeHandle=null;
                },
                error(xhr,message){
                    $(".brand-list").html("");
                    $(".brand-list").append(createBrandItem(0,"查询失败："+message));
                }
            })
        },300);
    }
    function hiddenBrandPopup(){
        $(".brands-popup").fadeOut();
        $(".brand-popup-mask").fadeOut();
    }


    $('.plusCategory').click(function () {
        appendHtml = $(this).parents().find('.tpl-category-container').html();

        $(this).parents().find('.category-container').append('<div class="row row-fix tpl-category-container">' + appendHtml + '<div>');
    });

    $('.editCategory').click(function () {
        appendHtml = $(this).parents().find('.tpl-category-container').html();

        $(this).parents().find('.category-container').append('<div class="row row-fix tpl-category-container">' + appendHtml + '<div>');
        $('.category-container').children(':last').children().children('select').find("option[value='0']").attr("selected",true)
        var seconde_category = $('.category-container').children(':last').children().children('select:eq(1)');
        var third_category = $('.category-container').children(':last').children().children('select:eq(2)');

        if (seconde_category.length > 0) {
            seconde_category.children(':gt(0)').remove();
        }
        if (third_category.length > 0) {
            third_category.children(':gt(0)').remove();
        }
    });

    $(document).on('click', '.delCategory', function () {
        var count = $(this).parents('.tpl-category-container').siblings('.tpl-category-container').length;

        if (count >= 1) {
            $(this).parents('.tpl-category-container').remove();
        } else {
            alert('商品分类必选');
        }


    });
</script>
{{--@section('js')--}}
{{--<script>--}}
{{--require(['select2'],function() {--}}
{{--$('#brand').select2();--}}
{{--})--}}
{{--</script>--}}
{{--@stop--}}