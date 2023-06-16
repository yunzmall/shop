<?php
namespace app\backend\modules\goods\models;

/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/22
 * Time: 下午2:24
 */

use app\backend\modules\goods\models\Category as CategoryModel;
use app\common\modules\category\CategoryGroup;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class Category extends \app\common\models\Category
{
    static protected $needLog = true;

    /**
     * @return mixed
     */
    public static function getAllCategory()
    {
        return self::uniacid()->select(['id','name','parent_id','level'])
            ->orderBy('id', 'asc')
            ->where('plugin_id',0)
            ->get();
    }

    public static function getAllCategorys()
    {
        return self::uniacid()
            ->orderBy('id', 'asc')
            ->where('plugin_id',0);
    }

    /**
     * @return mixed
     */
    public static function getAllCategoryGroup()
    {
        $categorys = self::getAllCategory();

        $categoryMenus['parent'] = $categoryMenus['children'] = [];

        foreach ($categorys as $category) {
            !empty($category['parent_id']) ?
                $categoryMenus['children'][$category['parent_id']][] = $category :
                $categoryMenus['parent'][$category['id']] = $category;
        }

        return $categoryMenus;
    }


    public static function getAllCategoryGroupArray($pluginId = 0)
    {
        $models = CategoryModel::uniacid()->getQuery()->select(['id', 'name', 'enabled', 'parent_id'])->where(['plugin_id' => $pluginId ,'deleted_at' => null])->orderBy('id', 'asc')->get();
        $categoryGroup = new CategoryGroup($models);
        $categorys = (new \app\common\modules\category\Category(['id' => 0],$categoryGroup))->getChildrenTree();

        return $categorys;
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public static function getCategory($id)
    {
        return self::find($id);
    }

    /**
     * @param $id
     * @return mixed
     */
    public static function daletedCategory($id)
    {
        return self::where('id', $id)
            ->orWhere('parent_id', $id)
            ->delete();
    }

    public static function deletedAllCategory($id)
    {
        $res = self::with([
            'hasManyChildren' => function ($query) use ($id) {
                 return $query->select(['*'])
                     ->with(['hasManyChildren' => function ($query) use ($id) {
                         return $query->select(['*']);
                     }]);
            }])
            ->where('id', $id)
            ->first();

        if (!is_null($res)) {
            if (!$res->hasManyChildren->isEmpty()) {
                foreach ($res->hasManyChildren as $coll) {
                    if (!$coll->hasManyChildren->isEmpty()) {
                        foreach ($coll->hasManyChildren as $rows) {
                            $rows->delete();
                        }
                    }

                    $coll->delete();
                }
            }

            return $res->delete();
        }
    }

    /**
     *  定义字段名
     * 可使
     * @return array
     */
    public function atributeNames()
    {
        return [
            'name' => '分类名称',
            'display_order' => '排序',
        ];
    }

    /**
     * 字段规则
     * @return array
     */
    public function rules()
    {
        $rule = Rule::unique($this->table);
        return [
            'name' => ['required',  $rule->ignore($this->id)
                ->where('uniacid', \YunShop::app()->uniacid)
                ->where('parent_id', $this->parent_id)
                ->where('plugin_id', 0)
                ->where('deleted_at', null)],
            'display_order' => ['required','integer'],
        ];
    }

    /**
     * @param $keyword
     * @return mixed
     */
    public static function getCategorysByName($keyword)
    {
        return static::uniacid()->select('id', 'name', 'thumb')
            ->where('name', 'like', '%' . $keyword . '%')
            ->get();
    }

    /**
     * @param $keyword
     * @return mixed
     */
    public static function getNotOneCategorysByName($keyword)
    {
        return static::uniacid()->select('id', 'name', 'thumb')
            ->where('parent_id', '<>', 0)
            ->where('name', 'like', '%' . $keyword . '%')
            ->get();
    }

    public static function getMallCategorysByName($keyword)
    {
        return static::uniacid()->select('id', 'name', 'thumb')
            ->where('parent_id', '<>', 0)
            ->where('plugin_id',0)
            ->where('name', 'like', '%' . $keyword . '%')
            ->get();
    }

    //根据商品分类ID获取分类名称
    public static function getCategoryNameByIds($categoryIds){
        if(empty($categoryIds))
        {
            return '';
        }

        if(is_array($categoryIds)){
            $res = static::uniacid()
                ->select('name')
                ->whereIn('id', $categoryIds)
                ->orderByRaw(DB::raw("FIELD(id, ".implode(',', $categoryIds).')')) //必须按照categoryIds的顺序输出分类名称
                ->get()
                ->map(function($goodname){ //遍历
                    return $goodname['name'];
                })
                ->toArray();
        } else{
            $res = static::uniacid()
                ->select('name')
                ->where('id', '=', $categoryIds)
                ->first();
        }
        return $res;
    }

    /**
     * 一级菜单
     *
     * @return mixed
     */
    public function getCategoryFirstLevel()
    {
        return self::uniacid()
            ->where('level', 1)
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * 二级菜单
     *
     * @return mixed
     */
    public function getCategorySecondLevel()
    {
        return self::uniacid()
            ->where('level', 2)
            ->orderBy('id', 'asc')
            ->get();
    }


    /**
     * 三级菜单
     *
     * @return mixed
     */
    public function getCategoryThirdLevel()
    {
        return self::uniacid()
            ->where('level', 3)
            ->orderBy('id', 'asc')
            ->get();
    }

    public function getCategoryData()
    {
        $level  = \Setting::get('shop.category.cat_level') == 2 ? 3 : null;
        $data = self::uniacid()
            ->select('id', 'name', 'parent_id')
            ->where('level' ,'!=' , $level) //根据商城后台设置判断要不要显示三级分类
            ->get();

          $data->map(function ($item){
              if($item['parent_id'] == 0){
                  unset($item['parent_id']);
              }
              $item ['href'] = yzAppFullUrl('catelist/' . $item['id']);
        });
        return $data;
    }

    public function getCategorySmallData()
    {
        $level  = \Setting::get('shop.category.cat_level') == 2 ? 3 : null;
        $data = self::uniacid()
            ->select('id', 'name', 'parent_id')
            ->where('level' ,'!=' , $level) //根据商城后台设置判断要不要显示三级分类
            ->get();

        $data->map(function ($item){
            if($item['parent_id'] == 0){
                unset($item['parent_id']);
            }
            $item ['href'] = '/packageB/member/category/catelist/catelist?id='. $item['id'];
        });
        return $data;
    }


    public function parentIdGetCategorys($level,$parent_id,$pluginId=0)
    {
       return CategoryModel::uniacid()
            ->getQuery()
            ->select(['id', 'name', 'enabled', 'parent_id'])
            ->where('plugin_id',$pluginId)
            ->where('deleted_at', null)
            ->where('level', $level)
            ->where('parent_id', $parent_id)
            ->orderBy('id', 'asc');

    }

    public function parentGetCategorys($pluginId=0)
    {
        return CategoryModel::uniacid()
            ->getQuery()
            ->select(['id', 'name', 'enabled', 'parent_id'])
            ->where('plugin_id',$pluginId)
            ->where('deleted_at', null)
            ->where('level', 1)
            ->orderBy('id', 'asc');

    }


    public function getCateOrderByLevel($ids){


        $list = $this->orderExportCacheAllCategory()->whereIn('id', explode(',',$ids))->sortBy('level')->pluck('name')->all();

        $list = array_pad($list,3,'');
        return $list;

//        $list=self::uniacid()->withTrashed()->whereIn('id',explode(',',$ids))->orderBy('level','asc')->pluck('name')->toArray();
//        if(count($list)==2){
//            array_push($list,'');
//        }else if(count($list)==1){
//            array_push($list,'','');
//        }
//
//        if (empty($list)) {
//            array_push($list,'','','');
//        }

        return $list;
    }


    protected $cacheAllCategory;

    /**
     * 缓存商品分类减少订单导出时sql查询次数
     * @return \app\framework\Database\Eloquent\Collection
     */
    public function orderExportCacheAllCategory()
    {
        if (!isset($this->cacheAllCategory)) {

            $this->cacheAllCategory = self::select('id', 'name', 'level')->uniacid()->withTrashed()->get();
        }

        return $this->cacheAllCategory;
    }

}