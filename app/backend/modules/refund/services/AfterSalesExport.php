<?php
/**
 * Created by PhpStorm.
 * User: blank
 * Date: 2022/11/4
 * Time: 14:59
 */

namespace app\backend\modules\refund\services;


use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class AfterSalesExport implements FromArray, WithStyles,WithHeadings, WithTitle
{
    public $data;//订单数据
    public $column = 14; //总行数
    public $goodsNum = []; //一个订单的商品数量

    protected $page = 1;

    public function __construct($data, $page = 1)
    {
        $this->data = $data;
        $this->page = $page;
    }

    //设置宽度
//    public function columnWidths(): array
//    {
//        return array('A' => 10,'B' => 13,'C' => 35, 'D'=> 12,'E'=>10,'F'=>18);
//    }

    public function title(): string
    {
        return '第' . $this->page . '页';
    }

    public function headings(): array
    {
        return [
            ['售后编号', '订单编号', '会员编号', '会员昵称', '订单类型',
                '售后类型', '售后状态', '退款金额', '商品名称', '退款数量',
                '申请时间', '完成时间', '退款原因', '标识'],
        ];
    }

    public function array(): array
    {
        $list = [];
        foreach ($this->data as $key => $value) {
            $this->goodsNum[] = count($value['goods'])?:1;


            if ($value['goods']) {
                foreach ($value['goods'] as $good) {

                    $title = $good['goods_title'];

                    if ($good['goods_option_title']) {
                        $title .= ":{$good['goods_option_title']}";
                    }

                    $list[] = [
                        $value['refund_sn'],
                        $value['order_sn'],
                        $value['uid'],
                        $value['nickname'],
                        $value['order_type_name'],
                        $value['refund_type_name'],
                        $value['status_name'],
                        $value['price'],
                        $title,
                        $good['refund_total'],
                        $value['create_time'],
                        $value['refund_time'],
                        $value['reason'],
                        $value['part_refund_name'],
                    ];
                }
            } else {
                $list[] = [
                    $value['refund_sn'],
                    $value['order_sn'],
                    $value['uid'],
                    $value['nickname'],
                    $value['order_type_name'],
                    $value['refund_type_name'],
                    $value['status_name'],
                    $value['price'],
                    '无记录',
                    '全额退款',
                    $value['create_time'],
                    $value['refund_time'],
                    $value['reason'],
                    $value['part_refund_name'],
                ];
            }

        }

        return $list;
    }

    public function styles(Worksheet $sheet)
    {


        for ($i = 0; $i < $this->column; $i++) {

            $y = ($i / 26);
            if ($y >= 1) {
                $y = intval($y);
                $cell[] = chr($y + 64) . chr($i - $y * 26 + 65);
            } else {
                $cell[] = chr($i + 65);
            }
        }


//        $sheet->mergeCells('A1:' . array_pop($cell) . '1'); //合并单元格
//
//        $alignment = $sheet->getStyle('A1')->getAlignment();
//        $alignment->setHorizontal(Alignment::HORIZONTAL_CENTER); //水平居中
//        $alignment->setVertical(Alignment::VERTICAL_CENTER);////垂直居中
//
//        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);//字体加粗


        $cell = array_filter($cell, function ($v) {
            return !in_array($v, ['I', 'J']);
        });
        foreach ($cell as $item) {
            $start = 2;
            foreach ($this->goodsNum as $key => $value) {
                $end = $start + $value - 1;
                if ($value > 1) {
                    $sheet->mergeCells($item . $start . ':' . $item . $end); //合并单元格
                }
                $start = $end + 1;

            }
        }

    }
}