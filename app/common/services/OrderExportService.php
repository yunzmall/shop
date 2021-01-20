<?php
/**
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/7/25
 * Time: 上午11:31
 */

namespace app\common\services;

class OrderExportService extends ExportService
{
    protected function exportBuilder()
    {
        $export_data = $this->export_data;

        return \Excel::create($this->file_name, function ($excel) use ($export_data) {
            $excel->setTitle('Office 2005 XLSX Document');
            $excel->setCreator('芸众商城')
                ->setLastModifiedBy("芸众商城")
                ->setSubject("Office 2005 XLSX Test Document")
                ->setDescription("Test document for Office 2005 XLSX, generated using PHP classes.")
                ->setKeywords("office 2005 openxml php")
                ->setCategory("report file");
            $excel->sheet('info', function ($sheet) use ($export_data) {
                $startRow = 2;  // 表格起始行标
                $endRow = 2;    // 表格结束行标
                $rowKey = $startRow;
                foreach ($export_data as $k => $v) {
                    if ($k == 0) {
                        // 表头
                        foreach ($v as $key => $value) {
                            $columnKey = $this->getColumnKey($key) . '1';
                            $sheet->cell($columnKey, function ($cell) use ($value) {
                                $cell->setValue($value);
                            });
                        }
                    } else {
                        $skipCol = 0;    // 需要跳过合并单元格的列数
                        $needMerge = 0;  // 是否需要合并单元格
                        // 判断是否有需要合并的单元格
                        foreach ($v as $key => $value) {
                            if (is_array($value)) {
                                $rowCount = count($value);
                                if ($rowCount > 1) {
                                    $endRow += $rowCount - 1;  // 表格结束行标后移，跳过合并单元格
                                    $needMerge = 1;
                                }
                                $skipCol = count(reset($value)) - 1;
                            }
                        }
                        $isSkip = 0;  // 是否需要跳过合并单元格
                        foreach ($v as $key => $values) {
                            if (is_array($values)) {
                                $isSkip = 1;
                                foreach ($values as $value) {
                                    $cellKey = $key;
                                    foreach ($value as $item) {
                                        $columnKey = $this->getColumnKey($cellKey);
                                        $sheet->cell($columnKey . $rowKey, function ($cell) use ($item) {
                                            $cell->setValue($item);
                                        });
                                        ++$cellKey;
                                    }
                                    ++$rowKey;
                                }
                            } else {
                                $columnKey = $isSkip ? $this->getColumnKey($key + $skipCol) : $this->getColumnKey($key);
                                $sheet->cell($columnKey . $startRow, function ($cell) use ($values) {
                                    $cell->setValue($values);
                                });
                                if ($needMerge) {
                                    $mergeRow = $columnKey . $startRow . ':' . $columnKey . $endRow;
                                    $sheet->mergeCells($mergeRow);
                                    $sheet->cells($mergeRow, function ($cells) use ($values) {
                                        if (is_numeric($values)) {
                                            $cells->setAlignment('right');
                                        }
                                    });
                                }
                            }
                        }
                        $startRow = ++$endRow;
                    }
                }
            });
        });
    }

    /**
     * 计算单元格列坐标
     *
     * @param $rowKey
     *
     * @return string
     */
    private function getColumnKey($rowKey)
    {
        $key = floor($rowKey / 26);
        if ($key > 0) {
            return chr($key + 64) . chr($rowKey % 26 + 65);
        } else {
            return chr($rowKey + 65);
        }
    }
}