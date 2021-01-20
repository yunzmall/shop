<?php
namespace app\backend\modules\goods\controllers;


use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\helpers\Url;
use app\common\models\Goods;
use app\common\models\Member;

use app\backend\modules\goods\models\Comment;
use app\backend\modules\goods\services\CommentService;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;


/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/2/27
 * Time: 下午5:09
 */
class CommentController extends UploadVerificationBaseController
{
    /**
     * 评论列表
     */
    public function index()
    {
        //        $pageSize = 10;
        //
        //        $search = CommentService::Search(\YunShop::request()->search);
        //
        //        $list = Comment::getComments($search)->paginate($pageSize)->toArray();
        //        $pager = PaginationHelper::show($list['total'], $list['current_page'], $list['per_page']);
        //
        //        return view('goods.comment.list', [
        //            'list' => $list['data'],
        //            'total' => $list['total'],
        //            'pager' => $pager,
        //            'search' => $search,
        //        ])->render();
        return view('goods.comment.list')->render();
    }

    public function commentData()
    {
        $pageSize = 10;

        $search = CommentService::Search(request()->search);

        $list = Comment::getComments(request()->search)->paginate($pageSize)->toArray();

        foreach ($list['data'] as &$item) {
            $item['head_img_url'] = yz_tomedia($item['head_img_url']);
            $item['goods']['thumb'] = yz_tomedia($item['goods']['thumb']);
        }

        $data = [
            'list' => $list,
            'total' => $list['total'],
            'search' => $search,
        ];
        return $this->successJson('ok', $data);
    }

    public function editView()
    {
        return view('goods.comment.info', ['id' => request()->id, 'goods_id' => request()->goods_id])->render();
    }


    /**
     * 添加评论
     */
    public function addComment()
    {
        $goods_id = request()->goods_id;
        $goods = [];
        if (!empty($goods_id)) {
            $goods = Goods::getGoodsById($goods_id);
            if (!$goods) {
                return $this->message('未找到此商品或该商品已被删除', Url::absoluteWeb('goods.comment.index'));
            }
            $goods = $goods->toArray();
        }


        $commentModel = new Comment();
        $commentModel->goods_id = $goods_id;

        $requestComment = request()->comment;

        if ($requestComment) {
            $requestComment['goods_id'] = $goods_id;
            //将数据赋值到model
            $commentModel->setRawAttributes($requestComment);
            //            dd($commentModel);
            //其他字段赋值
            $commentModel->uniacid = \YunShop::app()->uniacid;
            if (empty($commentModel->nick_name)) {
                $commentModel->nick_name = Member::getRandNickName()->nickname;
            }
            if (empty($commentModel->head_img_url)) {
                $commentModel->head_img_url = Member::getRandAvatar()->avatar;
            }
            $commentModel = CommentService::comment($commentModel);
            //字段检测
            $validator = $commentModel->validator($commentModel->getAttributes());
            if ($validator->fails()) {
                $this->errorJson($validator->messages());
            } else {
                //数据保存
                if ($commentModel->save()) {
                    Goods::updatedComment($commentModel->goods_id);
                    //显示信息并跳转
                    return $this->successJson('评论创建成功');
                } else {
                    $this->errorJson('评论创建失败');
                }
            }
        }
        $goods['thumb'] = yz_tomedia($goods['thumb']);
        $data = [
            'comment' => $commentModel,
            'goods' => $goods
        ];
        return $this->successJson('ok', $data);

        //        return view('goods.comment.info', [
        //            'comment' => $commentModel,
        //            'goods' => $goods
        //        ])->render();
    }

    public function searchGoodsV2()
    {
        $keyword = request()->keyword;
        $goods = Goods::select('id', 'title', 'thumb')
            ->where('title', 'like', '%' . $keyword . '%')
            ->where('status', 1)
            ->get();

        if (!$goods->isEmpty()) {
            $goods = set_medias($goods->toArray(), array('thumb', 'share_icon'));
        }

        $data = [
            'goods' => $goods,
            'exchange' => request()->exchange,
        ];
        return $this->successJson('ok', $data);
        //        return view('goods.query', [
        //            'goods' => $goods,
        //            'exchange' => \YunShop::request()->exchange,
        //        ])->render();
    }

    public function searchGoods()
    {
        $keyword = \YunShop::request()->keyword;
        $goods = Goods::select('id', 'title', 'thumb')
            ->where('title', 'like', '%' . $keyword . '%')
            ->where('status', 1)
            ->get();

        if (!$goods->isEmpty()) {
            $goods = set_medias($goods->toArray(), array('thumb', 'share_icon'));
        }
        return view('goods.query', [
            'goods' => $goods,
            'exchange' => \YunShop::request()->exchange,
        ])->render();
    }

    /**
     * 修改评论
     */
    public function updated()
    {
        $id = request()->id;
        $commentModel = Comment::getComment($id)->first();
        if (!$commentModel) {
            return $this->errorJson('无此记录或已被删除');
        }

        $goods_id = $commentModel->goods_id;
        if (!empty(request()->goods_id) && request()->goods_id != $commentModel->goods_id){
            if($goods = Goods::getGoodsById(request()->goods_id)) $goods_id = $goods->id;
            else return $this->errorJson('选择的商品不存在或已删除');
        }

        $requesComment = request()->comment;
        if ($requesComment) {

            //将数据赋值到model
            $commentModel->setRawAttributes($requesComment);
            if (empty($commentModel->nick_name)) {
                $commentModel->nick_name = Member::getRandNickName()->nick_name;
            }
            if (empty($commentModel->head_img_url)) {
                $commentModel->head_img_url = Member::getRandAvatar()->avatar;
            }

            $commentModel = CommentService::comment($commentModel);


            $commentModel->goods_id = $goods_id;
            //字段检测
            $validator = $commentModel->validator($commentModel->getAttributes());
            if ($validator->fails()) {
                $this->errorJson($validator->messages());
            } else {
                //数据保存
                if ($commentModel->save()) {
                    //显示信息并跳转
                    return $this->successJson('评论保存成功');
                } else {
                    $this->errorJson('评论保存失败');
                }
            }
        }
        $commentModel['head_img_url_url'] = yz_tomedia($commentModel['head_img_url']);
        $commentModel['images'] = unserialize($commentModel['images']);
        $imgs = $commentModel['images'];
        foreach ($imgs as &$item) {
            $item = yz_tomedia($item);
        }
        $commentModel['images_url'] = $imgs;
        $goods['thumb'] = yz_tomedia($goods['thumb']);
        $data = [
            'id' => $id,
            'comment' => $commentModel,
            'goods' => $goods
        ];
        return $this->successJson('ok', $data);
        //        return view('goods.comment.info', [
        //            'id' => $id,
        //            'comment' => $commentModel,
        //            'goods' => $goods
        //        ])->render();
    }

    public function replyView()
    {
        return view('goods.comment.reply', ['id' => request()->id])->render();
    }

    /**
     * 评论回复
     */
    public function reply()
    {
        $id = intval(request()->id);
        $commentModel = Comment::getComment($id)->first();
        if (!$commentModel) {
            return $this->errorJson('无此记录或已被删除');
        }

        if (request()->reply) {
            return $this->createReply();
        }


        $commentModel = $commentModel->toArray();
        $goods = Goods::getGoodsById($commentModel['goods_id']);
        $commentModel['images'] = unserialize($commentModel['images']);
        foreach ($commentModel['images'] as $key=>$item) {
            $commentModel['images'][$key] = yz_tomedia($item);
        }

        foreach ($commentModel['has_many_reply'] as &$item) {
            $item['images'] = unserialize($item['images']);
            foreach ($item['images'] as &$it) {
                $it = yz_tomedia($it);
            }
        }
        $goods['thumb'] = yz_tomedia($goods['thumb']);
        $data = [
            'comment' => $commentModel,
            'goods' => $goods
        ];
        return $this->successJson('ok', $data);
        //        return view('goods.comment.reply', [
        //            'comment' => $commentModel,
        //            'goods' => $goods
        //        ])->render();
    }

    public function createReply()
    {
        $id = intval(request()->id);
        $commentModel = new Comment;

        $requestReply = request()->reply;
        if ($requestReply) {
            $member = Member::getMemberById($requestReply['reply_id']);
            $requestReply = CommentService::reply($requestReply, $member);
            //将数据赋值到model
            $commentModel->setRawAttributes($requestReply);
            $validator = $commentModel->validator($commentModel->getAttributes());
            //字段检测
            if ($validator->fails()) {
                return $this->errorJson($validator->messages());
            } else {
                //数据保存
                if (Comment::saveComment($commentModel->getAttributes())) {
                    //显示信息并跳转
                    //                    return $this->message('评论回复保存成功', Url::absoluteWeb('goods.comment.reply', ['id' => $id]));
                    return $this->successJson('评论回复保存成功');
                } else {
                    return $this->errorJson('评论回复保存失败');
                }
            }
        }
    }


    /**
     * 删除评论
     */
    public function deleted()
    {
        $comment = Comment::getComment(request()->id);
        if (!$comment) {
            return $this->errorJson('无此评论或已经删除');
        }

        $result = Comment::daletedComment(request()->id);
        if ($result) {
            return $this->successJson('删除评论成功');
        } else {
            return $this->errorJson('删除评论失败');
        }
    }
}
