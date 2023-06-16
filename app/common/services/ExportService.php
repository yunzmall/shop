<?php
/**
 * Author:
 * Date: 2017/7/25
 * Time: 上午11:31
 */

namespace app\common\services;



use app\common\exceptions\ShopException;
use app\common\helpers\Url;
use app\exports\FromArray;

class ExportService
{
    protected $file_name;
    protected $export_data;
    private $page_count;
    public $builder_model;
    private $export_page;
    protected $page_size = 500;

    public function __construct($builder, $export_page = 1,$pageSize=500)
    {
        $this->export_page = $export_page;
        $builder_count = $builder->count();
        $this->page_count = ceil($builder_count / $this->page_size);
        $this->builder_model = $builder->skip(($export_page - 1) * $this->page_size)->take($this->page_size)->get();
        $this->page_size=$pageSize;
    }

    private function swith()
    {
        if ($this->page_count > 1) {
            $this->bigExcel();
        } else {
        	return $this->smallExcel();
        }

    }

    public function getExportBuilder()
    {
        return new FromArray($this->export_data);
    }

    private function smallExcel()
    {
		return app('excel')->download($this->getExportBuilder(),$this->file_name.'.xlsx');
    }

    private function bigExcel()
    {
		app('excel')->store($this->getExportBuilder(),$this->file_name.'.xlsx','export');
    }


    public function export($file_name, $export_data, $route = null, $type = 'export')
    {
        //每次导出单独建文件夹，防止有用户同时导出时互相影响
        if(!request()->input('export_dir')&&$this->page_count>1){
            request()->offsetSet('export_dir', time().'_'.rand(1111,9999).'/');
        }
        $this->file_name =request()->input('export_dir').$file_name;
        $this->export_data = $export_data;
		if ($this->page_count > 1) {
			$this->bigExcel();
		} else {
			$response = $this->smallExcel();
			return $response->send();
		}

        if ($this->export_page == $this->page_count) {
            setlocale(LC_ALL,'zh_CN.GBK');
            if (!file_exists(storage_path('framework/laravel-excel/'))){
                mkdir(storage_path('framework/laravel-excel/'));
            }
            $filename = storage_path('framework/laravel-excel/' . time() . 'down.zip');
            $time = time();
            $zip = new \ZipArchive(); // 使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
            if ($zip->open ( $filename, \ZipArchive::CREATE ) !== TRUE) {
                exit ( '无法打开文件，或者文件创建失败' );
            }
            //$fileNameArr 就是一个存储文件路径的数组 比如 array('/a/1.jpg,/a/2.jpg....');
            $fileNameArr = file_tree(storage_path('exports').'/'.request()->input('export_dir'));

            foreach ($fileNameArr as $val ) {
                // 当你使用addFile添加到zip包时，必须确保你添加的文件是存在的，否则close时会返回FALSE，而且使用addFile时，即使文件不存在也会返回TRUE
                if(file_exists(storage_path('exports'.'/'.request()->input('export_dir') . basename($val)))){
                    $zip->addFile (storage_path('exports'.'/'.request()->input('export_dir')) . basename($val), basename($val) ); // 第二个参数是放在压缩包中的文件名称，如果文件可能会有重复，就需要注意一下
                }
            }

            $zip->close (); // 关闭
            foreach ($fileNameArr as $val ) {
                file_delete(storage_path('exports'.'/'.request()->input('export_dir') . basename($val)));
            }
            //下面是输出下载;
            if (config('app.framework') == 'platform') {
                $url = "https://". $_SERVER['HTTP_HOST'].'/storage/framework/laravel-excel/' . $time ."down.zip";
            } else {
                $url = "https://". $_SERVER['HTTP_HOST'].'/addons/yun_shop/storage/framework/laravel-excel/' . $time ."down.zip";
            }
            $backurl = "https://". $_SERVER['HTTP_HOST']. config('app.isWeb') . "?c=site&a=entry&m=yun_shop&do=4302&route=" . $route;
            if ($this->page_count > 1) {
                rmdir(storage_path('exports' . '/' . request()->input('export_dir')));
            }
            echo '<div style="border: 6px solid #e0e0e0;width: 12%;margin: 0 auto;margin-top: 12%;padding: 26px 100px;box-shadow: 0 0 14px #a2a2a2;color: #616161;"><a style="color:red;text-decorationnone;"  href="'.$url.'">点击获取下载文件</a><a style="color:#616161"  href="'.$backurl.'">返回</a><div>';
            exit;
        } else {
            echo '<div style="border: 6px solid #e0e0e0;width: 12%;margin: 0 auto;margin-top: 12%;padding: 26px 100px;box-shadow: 0 0 14px #a2a2a2;color: #616161;">共'.$this->page_count.'个excel文件, 已完成'.$this->export_page. '个。 <div>';
            $this->export_page += 1;

            $params = [];
//            $filts_params = ['c', 'a', 'm', 'do', 'route'];
//            foreach (request()->input() as $key => $val) {
//                if (!in_array($key, $filts_params)) {
//                         $params[$key] = $val;
//                }
//            }

            $request_params = request()->except(['c', 'a', 'm', 'do', 'route']);

            foreach ($request_params as $key => $val) {
                if (is_array($val)) {
                    if (key($val) === 0) {
                        $params[$key] = implode(',', $val);
                    } else {
                        foreach ($val as $v_key => $value) {
                            $params[$key][$v_key] = (is_array($value) && key($value) === 0) ? implode(',', $value) : $value;
                        }
                    }

                } else {
                    $params[$key] = $val;
                }
            }
            $params[$type] = 1;
            $params['export_page'] = $this->export_page;
            $url = Url::absoluteWeb(\Request::query('route'), $params);

            echo '<meta http-equiv="Refresh" content="1; url='.$url.'" />';
            exit;
        }
    }

    /*
     * 消除特殊字符.
     */
    public function eliminateSpecialSymbol($data)
    {
        return preg_replace("/[ '.,:;*?~`!@#$%^&+=)(<>{}]|\]|\[|\/|\\\|\"|\|/", '', $data);
    }

    /*
     * 防止科学计数法.
     */
    public function unScientificNotation($param)
    {
        return $param . "\t";
    }
}