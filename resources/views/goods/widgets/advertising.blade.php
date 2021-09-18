
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">广告宣传语</label>
    <div class="col-sm-9 col-xs-12">
        <label class='radio-inline'>
            <input type='radio' name='widgets[advertising][is_open]' value='1' @if($data->is_open == '1') checked="checked" @endif/>
            开启
        </label>
        <label class='radio-inline'>
            <input type='radio' name='widgets[advertising][is_open]' value='0' @if(empty($data->is_open)) checked="checked" @endif/>
            关闭
        </label>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">文案</label>
    <div class="col-sm-9 col-xs-12">
        <textarea class="form-control" type="text" name="widgets[advertising][copywriting]" style="resize: vertical;" onkeypress="cancalPreventDefault()">{{  $data->copywriting  }}</textarea>
        <p>商品详情显示</p>
        <p>最多可输入100个字</p>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">文案字体大小</label>
    <div class="col-sm-9 col-xs-12">
        <div class="table-responsive ">
            <div class="return-queue">
                <div class="input-group col-md-2">
                    {{--<div class="input-group-addon">减</div>--}}
                    <input type="number" oninput="if(value>50)value=50" name="widgets[advertising][font_size]" class="form-control" value="{{ $data->font_size }}" style="width: 136px;"/>
                    <div class="input-group-addon">px</div>
                </div>
                <span class="help-block">最大50px</span>
            </div>
        </div>
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">文案颜色</label>
    <div class="col-sm-9 col-xs-12 wid100">
        {!!tpl_form_field_color('widgets[advertising][font_color]',$data->font_color)!!}
    </div>
</div>
<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">H5链接</label>
    <div class="col-sm-9 col-xs-12">
        <div class="input-group ">
            <input class="form-control" type="text" data-id="PAL-00010" name="widgets[advertising][link]" placeholder="请填写指向的链接 (请以https://开头)" value="{{$data->link}}">
            <span class="input-group-btn">
                <button class="btn btn-default nav-link" type="button" data-id="PAL-00010" >选择链接</button>
            </span>
        </div>
    </div>
</div>

<div class="form-group">
    <label class="col-xs-12 col-sm-3 col-md-2 control-label">小程序链接</label>
    <div class="col-sm-9 col-xs-12">
        <div class="input-group">
            <input type="text" name="widgets[advertising][min_link]" data-id="PAL-00012" class="form-control" placeholder="请填写指向的链接" value="{{$data->min_link}}" />
            <span class="input-group-btn">
                <button class="btn btn-default nav-app-link" type="button" data-id="PAL-00012" >选择链接</button>
            </span>
        </div>
    </div>
</div>

@include('public.admin.mylink')
@include('public.admin.small')

<script>
    function cancalPreventDefault(){
        window.event.stopPropagation();
        return true;
    }
</script>