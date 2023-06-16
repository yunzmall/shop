<?php
namespace app\backend\modules\goods\controllers;


use app\backend\modules\uploadVerificate\UploadVerificationBaseController;
use app\common\helpers\Url;
use app\common\models\comment\CommentConfig;
use app\common\models\Goods;
use app\common\models\Member;

use app\backend\modules\goods\models\Comment;
use app\backend\modules\goods\services\CommentService;
use app\common\components\BaseController;
use app\common\helpers\PaginationHelper;
use app\common\models\MemberLevel;


/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/2/27
 * Time: 下午5:09
 */
class CommentController extends UploadVerificationBaseController
{
    /**
     * 评论设置
     */
    public function index()
    {
        return view('goods.comment.index')->render();
    }

    /**
     * 评论列表
     */
    public function list()
    {
        return view('goods.comment.list')->render();
    }

    /**
     * 审核列表
     */
    public function audit()
    {
        return view('goods.comment.audit')->render();
    }

    //评论设置数据&保存
    public function saveSet()
    {
        $data = request()->form;
        $config_data = CommentConfig::getSetConfig();

        if ($data) {
            if ($config_data) {
                $res = CommentConfig::find($config_data['id']);
                $res->delete();
            }
            $res = new CommentConfig();

            $resData = [
                'uniacid' => \YunShop::app()->uniacid,
                'is_comment_audit' => $data['is_comment_audit'],
                'is_default_good_reputation' => $data['is_default_good_reputation'],
                'is_order_comment_entrance' => $data['is_order_comment_entrance'],
                'is_additional_comment' => $data['is_additional_comment'],
                'is_score_latitude' => $data['is_score_latitude'],
                'top_sort' => $data['top_sort'],
                'is_order_detail_comment_show' => $data['is_order_detail_comment_show'],
            ];

            $res->fill($resData);
            $res->save();
        }

        return $this->successJson('success',[
            'data' => $config_data
        ]);
    }

    /**
     * 更改评论状态
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeCommentStatus()
    {
        $comment_id = request()->comment_id;
        $type = request()->type;

        switch ($type) {
            case 'show':
                $column = 'is_show';
                break;
            case 'top':
                $column = 'is_top';
                break;
            default:
                $column = '';
        }

        if (!$column || !$comment_id) {
            return $this->errorJson('参数错误');
        }

        $commentModel = Comment::uniacid()->find($comment_id);
        $commentModel->$column = request()->$column;
        $commentModel->save();

        return $this->successJson('success');
    }

    //修改审核状态
    public function changeAuditStatus()
    {
        $comment_id = request()->comment_id;
        $commentModel = Comment::uniacid()->find($comment_id);
        if ($commentModel->audit_status != Comment::wait_audit) {
            return $this->errorJson('该评论状态不可审核');
        }

        $commentModel->audit_status = Comment::pass_audit;

        //追评审核
        if ($commentModel->type == 3) {
            $commentModel::updatedAdditionalCommentId($commentModel->comment_id,$comment_id);
        }
        $commentModel->save();

        \Log::info('后台通过评论审核',\YunShop::app()->uid);

        return $this->successJson('success');
    }

    public function commentData()
    {
        $pageSize = 10;

        $search = CommentService::Search(request()->search);

        if (isset(request()->type) && request()->type == 'audit') {
            $list = Comment::getComments(request()->search,'audit')->where('audit_status',Comment::wait_audit)->paginate($pageSize)->toArray();
        } else {
            $list = Comment::getComments(request()->search)->whereIn('audit_status',[Comment::not_audit,Comment::pass_audit])->paginate($pageSize)->toArray();
        }

        foreach ($list['data'] as &$item) {
            if (request()->type == 'audit' && $item['type'] == 3) {
                $item['level'] = 5;//追评默认好评
            }
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
        return view('goods.comment.info', ['id' => request()->id, 'goods_id' => request()->goods_id, 'default_level' => \Setting::get('shop.member')['level_name']?:'普通会员','levels' => $this->getLevels()])->render();
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
            $comment_time = time();
            if ( $requestComment['time_state'] && $requestComment['comment_time'] > 0){
                $comment_time = $requestComment['comment_time'] / 1000;
            }
            unset($requestComment['time_state']);
            unset($requestComment['comment_time']);
            if (!CommentConfig::isScoreLatitude()) {
                unset($requestComment['score_latitude']);
            } else {
                //insert方法不走模型，手动转换json
                $requestComment['score_latitude'] = json_encode($requestComment['score_latitude']);
            }

            //将数据赋值到model
            $commentModel->setRawAttributes($requestComment);

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
                $commentData = $commentModel->getAttributes();
                $commentData['created_at'] = $comment_time;
                $commentData['updated_at'] = $comment_time;
                //数据保存
//                if ($commentModel->save()) {
                if ($commentModel->insert($commentData)) {
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
            'goods' => $goods,
            'is_score_latitude' => CommentConfig::isScoreLatitude()
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

        $requestComment = request()->comment;
        if ($requestComment) {
            $goods_id = $commentModel->goods_id;
            if (!empty(request()->goods_id) && request()->goods_id != $goods_id){
                if (!$goods = Goods::getGoodsById(request()->goods_id)) return $this->errorJson('选择的商品不存在或已删除');
                $goods_id = $goods->id;
            }
            $comment_time = 0;
            if ($requestComment['time_state'] && $requestComment['comment_time'] > 0){
                $comment_time = $requestComment['comment_time'] / 1000;
            }
            unset($requestComment['time_state']);
            unset($requestComment['comment_time']);

            if (!CommentConfig::isScoreLatitude()) {
                unset($requestComment['score_latitude']);
            } else {
                //insert方法不走模型，手动转换json
                $requestComment['score_latitude'] = json_encode($requestComment['score_latitude']);
            }

            //将数据赋值到model
            $commentModel->setRawAttributes($requestComment);
            if (empty($commentModel->nick_name)) {
                $commentModel->nick_name = Member::getRandNickName()->nick_name;
            }
            if (empty($commentModel->head_img_url)) {
                $commentModel->head_img_url = Member::getRandAvatar()->avatar;
            }

            $commentModel->images = isset($commentModel->images) && is_array($commentModel->images) ? serialize($commentModel->images) : serialize([]);

            if ($comment_time) $commentModel->created_at = $comment_time;
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
        $commentModel['comment_time'] = is_numeric($commentModel['created_at']) ? : strtotime($commentModel['created_at']);

        $goods = Goods::getGoodsById($commentModel->goods_id);
        $goods['thumb'] = yz_tomedia($goods['thumb']);

        $data = [
            'id' => $id,
            'comment' => $commentModel,
            'goods' => $goods,
            'score_latitude' => $commentModel['score_latitude'],
            'is_score_latitude' => CommentConfig::isScoreLatitude()
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
        return view('goods.comment.reply', ['id' => request()->id,'page_type' => request()->page_type])->render();
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

        $after_content = Comment::getAfterContent($commentModel['id']);
        if ($after_content) {
            $after_content = $after_content->toArray();

            $after_content['images'] = unserialize($after_content['images']);
            foreach ($after_content['images'] as &$image) {
                $image = yz_tomedia($image);
            }
        }

        if ($commentModel['type'] == 3) {
            $commentModel['level'] = 5;//追评默认好评
        }

        $data = [
            'comment' => $commentModel,
            'goods' => $goods,
            'page_type' => request()->page_type,
            'after_content' => $after_content,
            'score_latitude' => $commentModel['score_latitude'],
        ];

        if(!is_null($comment_detail_arr = \app\common\modules\shop\ShopConfig::current()->get('comment_detail_data'))){
            foreach ($comment_detail_arr as $v){
                $class    = array_get($v, 'class');
                $function = array_get($v, 'function');
                if ($other_data = $class::$function($commentModel)){
                    $data[$other_data['key']] = $other_data;
                }
            }
        }

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

            //主评论状态修改
            $commentStatusModel = Comment::uniacid()->find($id);
            $commentStatusModel->is_show = request()->is_show;
            $commentStatusModel->is_top = request()->is_top;
            $commentStatusModel->save();

            //内容为空
            if (empty($requestReply['reply_content'])) {
                return $this->successJson('修改状态成功');
            }
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

    private function getLevels()
    {
        $levels = MemberLevel::uniacid()
            ->select('id', 'level', 'level_name')
            ->get();
        return $levels;
    }
}
