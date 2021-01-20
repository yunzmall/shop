<?php
/**
 * Created by PhpStorm.
 * User: 17812
 * Date: 2020/9/16
 * Time: 9:55
 */

namespace app\backend\modules\coupon\controllers;


use app\common\components\BaseController;
use app\common\exceptions\AppException;
use app\common\exceptions\ShopException;
use app\common\models\coupon\CouponSlideShow;

class SlideShowController extends BaseController
{
    public function index()
    {
        $list = CouponSlideShow::uniacid()->paginate('', ['*'], '', (int)request()->page);
        $list = $list->toArray();
        return view('coupon.slide-show', [
            'list' => json_encode($list),
        ])->render();
    }

    public function search()
    {
        $list = CouponSlideShow::uniacid()->paginate('', ['*'], '', (int)request()->page);
        $list = $list->toArray();
        return $this->successJson('ok', $list);
    }

    public function add()
    {
        if (\Request::getMethod() == 'POST') {
            $data = request()->data;
            $model = new CouponSlideShow();
            $data['uniacid'] = \YunShop::app()->uniacid;
            $model->fill($data);
            $validator = $model->validator();
            if ($validator->fails()) {
                throw new AppException($validator->messages()->first());
            }
            if (!$model->save()) {
                throw new AppException('MySql error, please try again');
            } else {
                return $this->successJson('ok', '新增成功');
            }
        }
        return view('coupon.slide-show-add', [
        ])->render();
    }

    public function edit()
    {
        $id = request()->id;
        $Model = CouponSlideShow::uniacid()->where('id', $id)
            ->first();
        if (!$Model) {
            throw new ShopException('无此条数据');
        }
        if (\Request::getMethod() == 'POST') {
            $data = request()->data;
            $Model->fill($data);
            $validator = $Model->validator();
            if ($validator->fails()) {
                throw new AppException($validator->messages()->first());
            }
            if (!$Model->save()) {
                throw new AppException('MySql error, please try again');
            } else {
                return $this->successJson('ok', '修改成功');
            }
        }
        return view('coupon.slide-show-add', [
            'data' => json_encode($Model)
        ])->render();
    }

    public function del()
    {
        $id = request()->id;
        $Model = CouponSlideShow::uniacid()->where('id', $id)
            ->first();
        if (!$Model) {
            throw new ShopException('无此条数据');
        }

        CouponSlideShow::uniacid()->where('id', $id)->delete();

        return $this->successJson('ok', '删除成功');
    }

    public function editSort()
    {
        $id = request()->id;
        $Model = CouponSlideShow::uniacid()->where('id', $id)
            ->first();
        if (!$Model) {
            throw new ShopException('无此条数据');
        }
        $Model->sort = request()->sort;
        if (!$Model->save()) {
            throw new AppException('MySql error, please try again');
        } else {
            return $this->successJson('ok', '修改成功');
        }
    }
}