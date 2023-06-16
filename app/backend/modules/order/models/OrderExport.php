<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/11/19
 * Time: 18:07
 */

namespace app\backend\modules\order\models;


use  Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

//临时使用
class OrderExport  implements FromArray,WithStyles
{

    public $data;//订单数据
    public $status; //sheet名称（订单状态）
    public $column; //总行数
    public $goodsNum = []; //一个订单的商品数量
    public $mergeRows = [];//每笔订单需要单独合并单元格的数据

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {



        $this->column = count($this->data[0]);
        $list = [];
        foreach ($this->data as $key => $value) {
            if ($key > 0) {
                $column_array = [];
                $column = [];
                $orderMergeColumn = [
                    'total_row' => 1, //订单总合并列数
                    'goods_category' => [], //每个商品合并列数
                ];
                foreach ($value as $column_key => $column_value) {
                    if (is_array($column_value)) {

                        foreach ($column_value as $goods_key => $goods_value) {
                            $goods_column = $column;
                            $goods_column_array = [];
                            foreach ($goods_value as $cate_key => $cate_value) {

                                if (is_array($cate_value)) {
                                    $orderMergeColumn['goods_category'][] = count($cate_value);

                                    foreach ($cate_value as $val) {
                                        $goods_column_array[] = array_merge($goods_column, $val);
                                    }

                                } else {
                                    if ($goods_column_array) {
                                        array_walk($goods_column_array, function (&$goods_column_array, $key, $value) {
                                            $goods_column_array[] = $value;
                                        }, $cate_value);
                                    } else {
                                        $goods_column[] = $cate_value;
                                    }
                                }
                            }
                            if ($goods_column_array) {
                                $column_array = array_merge($column_array, $goods_column_array);
                            } else {
                                $column_array = array_merge($column_array, [$goods_column]);
                            }
                        }
                        $orderMergeColumn['total_row'] = max(count($column_value), array_sum($orderMergeColumn['goods_category']));
                        $this->pushMergeNum($orderMergeColumn);
                    } else {

                        if ($column_array) {
                            foreach ($column_array as $k => $v) {
                                $column_array[$k][] = $column_value;
                            }
                        } else {
                            $column[] = $column_value;
                        }
                    }
                }
                if ($column_array) {
                    $list = array_merge($list, $column_array);
                } else {
                    $list = array_merge($list, [$column]);
                }
            } else {
                $list[] = $value;
            }
        }

//        dd($list, $this->mergeRows);
        return $list;
    }

    public function pushMergeNum($data)
    {
        $this->mergeRows[] = $data;
    }


    public function styles(Worksheet $sheet)
    {

        //获取对应个数表格编号
        for($i=0;$i< $this->column;$i++) {

            $y = ($i / 26);
            if ($y >= 1) {
                $y = intval($y);
                $cell[] = chr($y + 64).chr($i - $y * 26 + 65);
            } else {
                $cell[] = chr($i + 65);
            }
        }


//        $cell = array_filter($cell, function ($v) {
//            return !in_array($v, ['L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W']);
//        });
//        $second = ['L', 'M', 'N', 'O', 'P', 'Q', 'R', 'V', 'W'];


        //不需要总合并合并的列
        $not_cells = ['L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y'];

        //商品需要合并的列
        $goods_yes_cells = ['L', 'M', 'N', 'O', 'P', 'Q', 'R','S','W','X', 'Y'];

        //设置样式
        $alignment = $sheet->getStyle('A1')->getAlignment();
        $alignment->setHorizontal(Alignment::HORIZONTAL_CENTER); //水平居中
        $alignment->setVertical(Alignment::VERTICAL_CENTER);////垂直居中


        foreach ($cell as $item) {
            $start = 2;
            $category_start = 2;

            foreach ($this->mergeRows as $key => $rowConfig) {
                $end = $start + $rowConfig['total_row'] - 1;

                if (!in_array($item, $not_cells)) {
                    //复制样式
                    $sheet->duplicateStyle($sheet->getStyle('A1'), $item . $start . ':' . $item . $end);
                    if ($rowConfig['total_row'] > 1) {
                        $sheet->mergeCells($item . $start . ':' . $item . $end); //合并单元格
                    }
                }


                if (in_array($item, $goods_yes_cells) && $rowConfig['goods_category']) {
                    foreach ($rowConfig['goods_category'] as $category_num) {
                        $category_end = $category_start + $category_num - 1;

                        if ($category_num > 1) {
                            $sheet->mergeCells($item . $category_start . ':' . $item . $category_end); //合并单元格
                        }

                        $category_start = $category_end + 1;
                    }
                }

                $start = $end + 1;

            }
        }




//        foreach ($cell as $item) {
//            $start = 2;
//            foreach ($this->goodsNum as $key => $value) {
//                $end = $start + array_sum($value['cate_num']) - 1;
//
//                //复制样式
//                $sheet->duplicateStyle($sheet->getStyle('A1'), $item . $start . ':' . $item . $end);
//
//                if (array_sum($value['cate_num']) > 1) {
//                    $sheet->mergeCells($item . $start . ':' . $item . $end); //合并单元格
//                }
//
//                $start = $end + 1;
//
//            }
//        }
//        foreach($second as $item){
//            $start = 2;
//            foreach ($this->goodsNum as $key => $value) {
//                foreach($value['cate_num'] as $k=>$v){
//                    $end = $start + $v - 1;
//                    if ($v > 1) {
//                        $sheet->mergeCells($item . $start . ':' . $item . $end); //合并单元格
//                    }
//                    $start = $end + 1;
//                }
//            }
//        }

    }
}
