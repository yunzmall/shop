<?php
/**
 * Created by PhpStorm.
 * User: dingran
 * Date: 2019/1/25
 * Time: 下午1:35
 */

namespace app\common\helpers;


class CategoryHelper
{
    public static function tplLinkCategoryShow()
    {
        return Cache::remember('tpl:link_category', 7200, function () {
            $html = '';
            $first_category = \app\backend\modules\goods\models\Category::getCategoryFirstLevel()->toArray();
            $second_category = \app\backend\modules\goods\models\Category::getCategorySecondLevel()->toArray();
            $third_category = \app\backend\modules\goods\models\Category::getCategoryThirdLevel()->toArray();

            $html .= '<div class="mylink-con">';

            foreach ($first_category as $goodcate_parent) //一级分类
            {
                $href = yzAppFullUrl('catelist/' . $goodcate_parent['id']);

                $html .= <<<EOF
                      <div class="mylink-line">{$goodcate_parent['name']}<div class="mylink-sub">
                            <a href="javascript:;" id="category-{$goodcate_parent['id']}" class="mylink-nav"
                              ng-click="chooseLink(1, 'category-{$goodcate_parent['id']}')"
                              nhref="{$href}">选择</a>
                         </div>
                      </div>
EOF;

                foreach ($second_category as $key => $value) 
                {
                  if ($value['parent_id'] == $goodcate_parent['id']) 
                  {
                        $href = yzAppFullUrl('catelist/' . $value['id']);
                        $html .= <<<EOF
                       <div class="mylink-line">
                          <span style='height:10px; width: 10px; margin-left: 10px; margin-right: 10px; display:inline-block; border-bottom: 1px dashed #ddd; border-left: 1px dashed #ddd;'></span>
                               {$value['name']}
                          <div class="mylink-sub">
                              <a href="javascript:;" id="category-{$value['id']}" class="mylink-nav"
                                   ng-click="chooseLink(1, 'category-{$value['id']}')"
                                   nhref="{$href}">选择</a>
                          </div>
                       </div>
EOF;

                    foreach ($third_category as $k => $v) //三级分类
                    {
                      if ($v['parent_id'] == $value['id']) 
                      {
                        $href = yzAppFullUrl('catelist/' . $v['id']);
                            $html .= <<<EOF
                            <div class="mylink-line">
                                <span style='height:10px; width: 10px; margin-left: 30px; margin-right: 10px; display:inline-block; border-bottom: 1px dashed #ddd; border-left: 1px dashed #ddd;'></span>
                                    {$v['name']}
                                <div class="mylink-sub">
                                    <a href="javascript:;" id="category-{$v['id']}" class="mylink-nav"
                                          ng-click="chooseLink(1, 'category-{$v['id']}')"
                                          nhref="{$href}">选择</a>
                                </div>
                             </div>
EOF;
                      }
                    }
                  }
                }
            }
            

            $html .= '</div>';

            return $html;
        });
    }

    public static function tplGoodsCategoryShow()
    {
        return Cache::remember('tpl:goods_category', 7200, function () {
            $html = '';
            $first_category = \app\backend\modules\goods\models\Category::getCategoryFirstLevel()->toArray();
            $second_category = \app\backend\modules\goods\models\Category::getCategorySecondLevel()->toArray();
            $third_category = \app\backend\modules\goods\models\Category::getCategoryThirdLevel()->toArray();

            $html .= '<div class="mylink-con">';

                foreach ($first_category as $goodcate_parent) {
                    $html .= <<<EOF
                          <div class="mylink-line">
                              {$goodcate_parent['name']}
                              <div class="mylink-sub">
                                  <a href="javascript:;" id="" class="" ng-click="selectCategoryGoods(focus,'{$goodcate_parent['id']}')">选择</a>
                              </div>
                          </div>
EOF;

                

                        foreach ($second_category as $value) {
                          if ($value['parent_id'] == $goodcate_parent['id']) 
                          {
                            # code...
                          
                            $html .= <<<EOF
                           <div class="mylink-line">
                                  <span style='height:10px; width: 10px; margin-left: 10px; margin-right: 10px; display:inline-block; border-bottom: 1px dashed #ddd; border-left: 1px dashed #ddd;'></span>
                                  {$value['name']}
                                  <div class="mylink-sub">
                                     <a href="javascript:;" class="mylink-nav" ng-click="selectCategoryGoods(focus,'{$value['id']}')">选择</a>
                                  </div>
                           </div>
EOF;

                            foreach ($third_category as $v) 
                            {
                                if ($v['parent_id'] == $value['id']) 
                                {
                                    $html .= <<<EOF
                               <div class="mylink-line">
                                    <span style='height:10px; width: 10px; margin-left: 30px; margin-right: 10px; display:inline-block; border-bottom: 1px dashed #ddd; border-left: 1px dashed #ddd;'></span>
                                    {$v['name']}
                                     <div class="mylink-sub">
                                         <a href="javascript:;" class="mylink-nav"  ng-click="selectCategoryGoods(focus,'{$v['id']}')">选择</a>
                                     </div>
                               </div>
EOF;

                                }
                            }
                          }
                            
                      }
                    
                }
            

            $html .= <<<EOF
</div>
EOF;

            return $html;
        });
    }
}
