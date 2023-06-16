<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2021/11/20
 * Time: 10:57
 */

namespace app\exports;

use Maatwebsite\Excel\Concerns\ToArray;

class ToArrayModel implements ToArray
{
    /**
     * @param array $array
     */
    public function array(array $array)
    {
        return $array;
    }
}