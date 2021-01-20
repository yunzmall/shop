<div class="panel panel-default">
    <div class="panel-heading">
        收货信息
    </div>
    <div class="panel-body">
        <div style="position: relative;">
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">收件人 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">
                        {{$order['address']['realname']}}
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">联系电话 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">
                        {{$order['address']['mobile']}}
                    </p>
                </div>
            </div>
            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">收货地址 :</label>
                <div class="col-sm-9 col-xs-12">
                    <p class="form-control-static">
                        {{$order['address']['address']}}
                    </p>
                </div>
            </div>
            <div class="" style="position: absolute;left: 500px;top: 40px;">
                @if ($order['status'] == 0 || $order['status'] == 1)
                <button class="btn btn-sm btn-success" onclick="addressUpdate('{{$order['id']}}')" data-toggle="modal" data-target="#modal_order_address_update">修改</button>
                @endif
                <a href="#" onclick="addressUpdateLog('{{$order['id']}}')" data-toggle="modal" data-target="#modal_order_address_update_list">修改记录</a>
            </div>
        </div>
        @if(isset($dispatch))
            @foreach($dispatch as $k=>$v)

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">包裹{{$k+1}}物流信息 :</label>

                <div class="col-sm-9 col-xs-12">
                    <div class="col-sm-1 col-xs-12 seeordergoods" data-toggle="modal"
                         data-target="#modal-seeordergoods" data-order_express_id="{{$v['order_express_id']}}">
                        <img src='{{$v['thumb']}}' style='width:100px;height:100px;padding:1px;border:1px solid #ccc'/>
                        <div style="position:relative;margin-top: -22px;background:rgba(0,0,0,0.6);width: 100px;text-align: center;color: #fffffd">共{{$v['count']}}件</div>
                    </div>
                    <span class="col-sm-1 col-xs-12 seeordergoods" data-order_express_id="{{$v['order_express_id']}}" style="color: red;margin-left: 10px; margin-top: 80px;" data-toggle="modal"
                          data-target="#modal-seeordergoods" >查看商品</span>
                    <div class="col-sm-12 col-xs-12">
                        <p class="form-control-static">公司:{{$v['company_name']}}</p>
                        <p class="form-control-static">运单号:{{$v['express_sn']}}</p>
                    </div>
                    <div class="col-sm-12 col-xs-12">

                    @foreach($v['data'] as $item)
                        <p class="form-control-static">[{{$item['time']}}] {{$item['context']}}</p>
                    @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>
<!-- 修改订单收货地址 -->
<div id="modal_order_address_update" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <form class="form-horizontal form" method="post">
        <input type='hidden' name='data[order_id]' value=''/>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                    <h3>修改收货信息</h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">收件人</label>
                        <div class="col-xs-6">
                            <input type="text" name="data[realname]" class="form-control" value=""/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">联系电话</label>
                        <div class="col-xs-6">
                            <input type="text" name="data[phone]" class="form-control" value=""/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">地址</label>
                        <div class="col-xs-6">
                            {!! app\common\helpers\AddressHelper::tplLinkedAddress(['data[province_id]','data[city_id]','data[district_id]','data[street_id]'], [])!!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-xs-12 col-sm-3 col-md-2 control-label">详细地址</label>
                        <div class="col-xs-6">
                            <input type="text" name="data[address]" class="form-control" value=""/>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="confirmUpdate()">确认</button>
                    <a href="#" class="btn btn-default" data-dismiss="modal" aria-hidden="true">取消</a>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- 地址修改列表 -->
<div id="modal_order_address_update_list" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                    <h3>修改记录</h3>
                </div>
                <div class="modal-body" style="max-height: 500px;overflow: auto;">
                    <table class="table table-hover">
                        <thead class="navbar-inner">
                        <tr>
                            <th style='width:10%;text-align: center;'>修改时间</th>
                            <th style='width:15%;text-align: center;'>修改前收货信息</th>
                            <th style='width:15%;text-align: center;'>修改后收货信息</th>
                        </tr>
                        </thead>
                        <tbody id="address-log-tbody">
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    {{--<button type="submit" class="btn btn-primary" name="address_update" value="yes">确认</button>--}}
                    {{--<a href="#" class="btn btn-default" data-dismiss="modal" aria-hidden="true">取消</a>--}}
                </div>
            </div>
        </div>
</div>
<script type="text/javascript" src="{{static_url('js/area/cascade_street.js')}}"></script>
<script>
    cascdeInit();
    function addressUpdate(order_id) {
        let modal = $('#modal_order_address_update');
        console.log(order_id);
        modal.find(':input[name="data[order_id]"]').val(order_id);
    }

    function confirmUpdate() {
        let modal = $('#modal_order_address_update');
        data = {
            "order_id": modal.find(':input[name="data[order_id]"]').val(),
            "realname": modal.find(':input[name="data[realname]"]').val(),
            "phone": modal.find(':input[name="data[phone]"]').val(),
            "province_id": modal.find(':input[name="data[province_id]"]').val(),
            "city_id": modal.find(':input[name="data[city_id]"]').val(),
            "district_id": modal.find(':input[name="data[district_id]"]').val(),
            "street_id" : modal.find(':input[name="data[street_id]"]').val(),
            "address": modal.find(':input[name="data[address]"]').val(),
        };

        console.log(data);

        $.post("{!! yzWebUrl('order.address-update.update') !!}", {data:data}, function(response){
            if (response.result == 1) {
                window.location.reload();
            } else {
                alert(response.msg);
            }
        },"json");
    }

    function addressUpdateLog(order_id) {
        let modal = $('#modal_order_address_update_list');

        console.log(order_id);
        $.get('{!! yzWebUrl('order.address-update.index') !!}',{order_id:order_id},function(response){
            console.log(response);
            if (response.result == 1) {
                let list = response.data;
                 let html = '';
                for (let i in list) {
                    html += ' <tr>' +
                        '<td style="text-align: center;white-space: normal;word-break:break-all">'+ list[i].created_at +'</td>' +
                        '<td style="text-align: center;white-space: normal;word-break:break-all">'+
                         list[i].old_name +'<br/>'+ list[i].old_phone + '<br/>' + list[i].old_address +
                        '</td>' +
                        '<td style="text-align: center;white-space: normal;word-break:break-all">' +
                        list[i].realname +'<br/>'+ list[i].phone + '<br/>' + list[i].new_address +
                        '</td>' +
                        '</tr>'
                }

                modal.find('#address-log-tbody').html(html);
            } else {

            }

        }, "json");

    }
</script>