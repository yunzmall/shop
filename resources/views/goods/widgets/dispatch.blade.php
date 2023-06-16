<link href="{{static_url('yunshop/goods/goods.css')}}" media="all" rel="stylesheet" type="text/css"/>

<div class="form-group" id="dispatch_info">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">运费设置</label>
    <div class="col-sm-6 col-xs-6">
        <label class="radio-inline">
            <input type="radio" name="widgets[dispatch][dispatch_type]" value="1"
                   @if ( $dispatch['dispatch_type'] == 1) checked="true" @endif /> 统一邮费
        </label>

        <div class="input-group form-group">
            <input type="text" name="widgets[dispatch][dispatch_price]" style="" id="dispatchprice"
                   class="form-control"
                   value="{{ $dispatch['dispatch_price'] }}"/>
            <span class="input-group-addon">元</span>
        </div>

        <label class="radio-inline">
            <input type="radio" name="widgets[dispatch][dispatch_type]" value="0"
                   @if ( empty($dispatch['dispatch_type'])) checked="true" @endif /> 运费模板
        </label>
        <div style="width: auto; float: left; margin-left: 10px;" id="type_dispatch">
            <select class="form-control tpl-category-parent" id="dispatchid" name="widgets[dispatch][dispatch_id]">
                <option value="0">默认模板</option>
                @foreach ($dispatch_templates as $dispatch_item)
                    <option value="{{ $dispatch_item['id'] }}"
                            @if ( $dispatch['dispatch_id'] == $dispatch_item['id']) selected="true" @endif>{{ $dispatch_item['dispatch_name'] }}</option>
                @endforeach
            </select>
        </div>

    </div>

</div>
<div class="form-group" id="dispatch_info">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">可选配送方式</label>
    <div class="col-sm-6 col-xs-6">
        <div class="form-group">
            <div class="col-sm-8 col-xs-12">
                @foreach($dispatch['dispatchTypesSetting'] as $dispatchTypeSetting)
                    <label class="radio-inline">
                        <input type="checkbox" name="widgets[dispatch][dispatch_type_ids][]"
                               value="{{$dispatchTypeSetting['id']}}"
                               @if ($dispatchTypeSetting['enable']) checked="checked" @endif />
                        {{$dispatchTypeSetting['name']}}
                    </label>
                @endforeach
            </div>

        </div>
    </div>

</div>
@if(app('plugins')->isEnabled('electronics-bill'))
<div class="form-group" id="dispatch_info">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">商品简称</label>
    <div class="col-sm-6 col-xs-6">
        <div class="form-group">
            <div class="col-sm-8 col-xs-12">
                <label class="radio-inline">
                    <input type="text" name="widgets[dispatch][abbreviation]"
                           value="{{$dispatch['abbreviation']}}"/>
                </label>
            </div>
        </div>
    </div>
</div>
@endif
{{--@section('supplier_show_dispatch')--}}
{{--<div class="form-group">--}}
{{--<label class="col-xs-12 col-sm-3 col-md-2 control-label">是否支持货到付款</label>--}}
{{--<div class="col-sm-6 col-xs-6">--}}
{{--<label class="radio-inline"><input type="radio" name="widgets[dispatch][is_cod]" value="0"--}}
{{--@if ( empty($dispatch['is_cod']) || $dispatch['is_cod'] == 0) checked="true" @endif />--}}
{{--不支持</label>--}}
{{--<label class="radio-inline"><input type="radio" name="widgets[dispatch][is_cod]" value="1"--}}
{{--@if ( $dispatch['is_cod'] == 1) checked="true" @endif /> 支持</label>--}}
{{--</div>--}}
{{--</div>--}}
{{--@show--}}


