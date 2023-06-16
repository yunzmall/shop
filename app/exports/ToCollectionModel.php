<?php
/**
 * Created by PhpStorm.
 *
 * 
 *
 * Date: 2021/11/22
 * Time: 11:38
 */

namespace app\exports;


use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ToCollectionModel implements ToCollection
{
    public function collection(Collection $collection)
    {
        return $collection;
    }
}