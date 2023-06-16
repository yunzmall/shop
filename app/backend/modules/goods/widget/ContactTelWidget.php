<?php
/**
 * Created by PhpStorm.
 * Date: 2023/5/9
 * Time: 14:17
 */

namespace app\backend\modules\goods\widget;

use app\backend\modules\goods\models\ContactTel;

class ContactTelWidget extends BaseGoodsWidget
{
    public $group = 'tool';

    public $widget_key = 'contact_tel';

    public $code = 'contact_tel';

    public function getData(): array
    {
        $contact_tel = '';
        $tel = ContactTel::where('goods_id', $this->goods->id)->first();

        if ($tel) {
            $contact_tel = $tel['contact_tel'] ?: '';
        }

        return ['contact_tel' => $contact_tel,];
    }

    public function pluginFileName(): string
    {
        return 'contact_tel';
    }

    public function pagePath()
    {
        return $this->getPath('resources/views/goods/assets/js/components/');
    }
}