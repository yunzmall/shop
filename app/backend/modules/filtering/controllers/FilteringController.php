<?php

namespace app\backend\modules\filtering\controllers;

use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\components\BaseController;
use app\common\facades\Setting;
use app\common\helpers\PaginationHelper;
use app\common\helpers\Url;
use app\backend\modules\filtering\models\Filtering;

/**
 *
 */
class FilteringController extends UploadVerificationBaseController
{
    protected $pageSize = 15;

    public function index()
    {

//        $list = Filtering::getList()->paginate($this->pageSize)->toArray();
//
//        $pager = PaginationHelper::show($list['total'], $list['current_page'], $list['per_page']);

//        return view('filtering.index', [
//            'list' => $list['data'],
//            'pager' => $pager,
//        ])->render();
        return view('filtering.index')->render();
    }

    public function filteringData()
    {
        $list = Filtering::getList()->paginate($this->pageSize)->toArray();

        return $this->successJson('ok', $list);
    }


    public function FilterValue()
    {
//        $filteringGroup = Filtering::find(request()->parent_id);
//        if(!$filteringGroup) {
//            return $this->message('无此标签组或已经删除','','error');
//        }
//
//        $filteringValue = Filtering::getList(\YunShop::request()->parent_id)->paginate($this->pageSize)->toArray();
//
//        $pager = PaginationHelper::show($filteringValue['total'], $filteringValue['current_page'], $filteringValue['per_page']);
//
//        return view('filtering.value', [
//            'list' => $filteringValue['data'],
//            'pager' => $pager,
//            'parent' => $filteringGroup,
//        ])->render();

        return view('filtering.value', [
            'parent_id' => request()->parent_id
        ])->render();

    }

    public function filterList()
    {
        $filteringGroup = Filtering::find(request()->parent_id);
        if (!$filteringGroup) {
            return $this->errorJson('无此标签组或已经删除');
        }

        $filteringValue = Filtering::getList(request()->parent_id)->paginate($this->pageSize)->toArray();

        $data = [
            'list' => $filteringValue,
            'parent' => $filteringGroup,
        ];
        return $this->successJson('ok', $data);
    }

    public function editView()
    {
        return view('filtering.form', [
            'parent_id' => request()->parent_id,
            'id' => request()->id,
        ])->render();
    }


    public function create()
    {
        $parent_id = request()->parent_id ? request()->parent_id : '0';

        $url = Url::absoluteWeb('filtering.filtering.index');
        if ($parent_id) {
            $parent = Filtering::find($parent_id);
            if (!$parent) {
                return $this->errorJson('无此标签组或已经删除');
            }

            $url = Url::absoluteWeb('filtering.filtering.filter-value', ['parent_id' => $parent_id]);
        }

        $filter = request()->filter;
        if ($filter) {
            $filtering = new Filtering();

            $filtering->parent_id = $parent_id;
            $filtering->fill($filter);
            $filtering->uniacid = \YunShop::app()->uniacid;
            $validator = $filtering->validator();
            if ($validator->fails()) {
                //检测失败
                $this->errorJson($validator->messages());
            } else {
                //数据保存
                if ($filtering->save()) {
                    //显示信息并跳转
                    return $this->successJson('创建成功', $url);
                } else {
                    $this->errorJson('创建失败');
                }
            }
        }
        $data = [
            'item' => $filtering,
            'parent_id' => $parent_id,
            'parent' => isset($parent) ? $parent : collect([]),
        ];

        return $this->successJson('ok', $data);
//        return view('filtering.form', [
//            'item' => $filtering,
//            'parent_id' => $parent_id,
//            'parent' => isset($parent) ? $parent : collect([]),
//        ])->render();
    }

    public function edit()
    {
        $filtering = Filtering::find(request()->id);
        if (!$filtering) {
            return $this->errorJson('无此记录或已被删除');
        }
        $url = Url::absoluteWeb('filtering.filtering.index');
        $parent_id = $filtering->parent_id;
        if ($parent_id) {
            $parent = Filtering::find($parent_id);
            if (!$parent) {
                return $this->errorJson('无此标签组或已经删除');
            }
            $url = Url::absoluteWeb('filtering.filtering.filter-value', ['parent_id' => $parent_id]);
        }

        $filter = request()->filter;
        if ($filter) {
            $filtering->fill($filter);
            $filtering->uniacid = \YunShop::app()->uniacid;
            $validator = $filtering->validator();
            if ($validator->fails()) {
                //检测失败
                $this->errorJson($validator->messages());
            } else {
                //数据保存
                if ($filtering->save()) {
                    //显示信息并跳转
                    return $this->successJson('修改成功');
                } else {
                    $this->errorJson('修改失败');
                }
            }
        }

        $data = [
            'item' => $filtering,
            'parent_id' => $parent_id,
            'parent' => isset($parent) ? $parent : collect([]),
        ];

        return $this->successJson('ok', $data);
//        return view('filtering.form', [
//            'item' => $filtering,
//            'parent_id' => $parent_id,
//            'parent' => isset($parent) ? $parent : collect([]),
//        ])->render();
    }

    public function setOpen()
    {
        $tag_id = request()->tag_id;
        $show = request()->show ? 1 : 0;

        $model = Filtering::find($tag_id);
        if (!$model) {
            return $this->errorJson('标签不存在或已被删除');
        }

        if ($model->parent_id === 0) {
            return $this->errorJson('此为标签组，请选择标签');
        }

        if ($model->is_front_show == $show) {
            return $this->successJson('修改成功');
        }

        $model->is_front_show = $show;
        if (!$model->save()){
            return $this->errorJson('修改失败,请重试');
        }

        return $this->successJson('修改成功');
    }

    public function getSet()
    {
        $set = Setting::get('goods.goods-tag-set');
        if (!$set){
            $set = ['is_search_show' => 1];
        }

        return $this->successJson('成功',$set);
    }

    public function saveSet()
    {
        $data = [
            'is_search_show' => request()->is_search_show ? 1 : 0
        ];
        $res = Setting::set('goods.goods-tag-set',$data);

        if (!$res){
            return $this->errorJson('保存失败');
        }

        return $this->successJson('保存成功');
    }

    /**
     * 获取搜索标签组
     * @return html
     */
    public function getSearchLabel()
    {

        $keyword = \YunShop::request()->keyword;
        $filter_group = Filtering::searchFilterGroup($keyword);
        return view('filtering.query', [
            'filter_group' => $filter_group->toArray(),
        ])->render();
    }

    public function getSearchLabelV2()
    {
        $keyword = request()->keyword;
        $filter_group = Filtering::searchFilterGroup($keyword);
        return $this->successJson('ok', $filter_group->toArray());
    }

    public function del()
    {
        $filtering = Filtering::find(request()->id);
        if (!$filtering) {
            return $this->errorJson('无此数据或已经删除', '', 'error');
        }

        $result = Filtering::del(request()->id);

        if ($result) {
            return $this->successJson('删除成功');
        } else {
            return $this->errorJson('删除失败');
        }

    }

}