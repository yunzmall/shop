<!-- 插件控制 -->

<div>
    <div class="vue-head">
        <div class="vue-main-title">
            <div class="vue-main-title-left"></div>
            <div class="vue-main-title-content"> 是否含税</div>
        </div>

        @if ($orderTaxFees)
            @foreach ($orderTaxFees as $taxFee)
                <div class="vue-main-form">
                    <el-form-item label="是否含税：" prop="">否</el-form-item>
                </div>

                <div class="vue-main-form">
                    <el-form-item label="不含税优惠比例：" prop="">
                        {!! $taxFee['rate'] !!}
                    </el-form-item>
                </div>
            @endforeach
        @else
            <div class="vue-main-form">
                <el-form-item label="是否含税：" prop="">是</el-form-item>
            </div>
        @endif


    </div>
</div>


