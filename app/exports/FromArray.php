<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/10/29
 * Time: 16:47
 */

namespace app\exports;

use Maatwebsite\Excel\Concerns\FromArray as BaseFromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FromArray implements BaseFromArray, WithStyles
{
	private $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function array(): array
	{
		return $this->data;
	}

    /**
     * 样式设置
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {

    }
}