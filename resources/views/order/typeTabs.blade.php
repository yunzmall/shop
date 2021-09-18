<div v-if="code == 'all'" class="vue-head">
    <a class="a-btn @if(request()->input('route') == 'order.order-list.index') a-colour2 @else a-colour1 @endif" href="{!! yzWebFullUrl('order.order-list.index') !!}">全部订单</a>
    @foreach((new \app\backend\modules\order\services\OrderViewService)->getOrderType() as $order_type)
        @if ($order_type['need_display'])
            <a  class="a-btn @if($order_type['route'] == request()->input('route')) a-colour2 @else a-colour1 @endif"
                href="{{ yzWebFullUrl($order_type['route'])}}">{{$order_type['name']}}</a>
        @endif
    @endforeach
</div>