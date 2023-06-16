<?php
/**
 * Created by PhpStorm.
 * 
 *
 *
 * Date: 2021/11/22
 * Time: 11:28
 */

namespace app\exports;


use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class ExcelService
{
    /**
     * 数组数据导出通用类
     * @param array $exportData 导出数据
     * @param string $fileName 导出文件名
     * @param string|null $fileExt 文件扩展名
     * @param array $headers 下载请求头部信息
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function fromArrayExport($exportData, $fileName, $fileExt = null, $headers =[])
    {
        if (!self::isFileExtension($fileName)) {
            $fileName .= '.'.\Maatwebsite\Excel\Excel::XLSX;

        }

        return Excel::download(new FromArray($exportData),$fileName,$fileExt,$headers)->send();
    }

    /**
     * 自定义数据导出通用类
     * @param object $exportData 导出类
     * @param string $fileName 导出文件名
     * @param string|null $fileExt 文件扩展名
     * @param array $headers 下载请求头部信息
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public static function customExport($exportModel, $fileName, $fileExt = null, $headers =[])
    {
        if (!self::isFileExtension($fileName)) {
            $fileName .= '.'.\Maatwebsite\Excel\Excel::XLSX;

        }

        return Excel::download($exportModel,$fileName,$fileExt,$headers)->send();
    }


    /**
     * 数据导出并且保存到指定磁盘通用类
     * @param object $exportModel 导出类
     * @param string $fileName 导出文件名
     * @param string $disk 磁盘标识路由 默认 export
     * @param null $fileExt 文件扩展名
     * @param array $diskOptions 磁盘选项
     * @return bool
     */
    public static function storeExport($exportModel, $fileName,$disk = 'export', $fileExt = null, $diskOptions = [])
    {
        if (!self::isFileExtension($fileName)) {
            $fileName .= '.'.\Maatwebsite\Excel\Excel::XLSX;

        }

        return Excel::store($exportModel,$fileName,$disk, $fileExt,$diskOptions);
    }

    /**
     * 导入 返回数组
     * @param mixed $file  文件路径或者文件上传类
     * @return array
     */
    public static function importToArray($file)
    {
        $result = Excel::toArray(new \app\exports\ToArrayModel(),$file);
        $importData = $result?$result:[];

        return $importData;
    }

    /**
     * 导入 返回对象
     * @param mixed $file  文件路径或者文件上传类
     * @return Collection
     */
    public static function importToCollection($file)
    {
        $result = Excel::toCollection(new \app\exports\ToCollectionModel(),$file);

        if ($result instanceof Collection) {
            return $result;
        }

        return new Collection([]);
    }


    /**判断是否有文件后缀名
     * @param $fileName
     * @return bool|string
     */
    public static function isFileExtension($fileName)
    {
        $index = strrpos($fileName, '.');
        if ($index !== false) {
            return substr($fileName, $index + 1);
        }
        return false;
    }

}