<?php


namespace app\frontend\modules\orderGoods\stock\types;


class Never
{
    public function withhold()
    {
        return true;
    }

    function reduce()
    {
        return true;
    }

    public function rollback()
    {
        return true;
    }

    public function shouldWithhold()
    {
        return true;
    }

    public function withholdRecord()
    {
        return true;
    }


}