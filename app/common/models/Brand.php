<?php
namespace app\common\models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/2/27
 * Time: 上午9:11
 */

class Brand extends BaseModel
{
    use SoftDeletes;

    public $table = 'yz_brand';

    protected $guarded = [''];

    protected $fillable = [''];
    
    /**
     * @param $pageSize
     * @return mixed
     */
    public static function getBrands($search = [])
    {
        //deleted_at字段是int所以加上whereNull
//        return self::uniacid()->whereNull('deleted_at');

        $result = self::uniacid()->whereNull('deleted_at');

        if ($search['id']){
            $result->where('id',$search['id']);
        }
        if ($search['name']){
            $result->where('name','like','%'.$search['name'] .'%');
        }
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function getBrand($id)
    {
        return self::where('id', $id)
            ->first();
    }

    public function keywordGetBrand($keyword)
    {
        return self::uniacid()->where('name','like','%'.$keyword .'%')->select(['id','name']);
    }
}
