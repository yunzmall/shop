<?php

/**
 * Created by PhpStorm.
 * Author: 芸众商城 www.yunzshop.com
 * Date: 2017/3/3
 * Time: 下午2:29
 */

namespace app\frontend\modules\goods\controllers;

use app\common\components\ApiController;
use app\common\components\UploadVerificationApiController;
use app\common\models\Goods;
use app\common\models\Member;
use app\common\models\OrderGoods;
use app\common\services\MiniFileLimitService;
use app\frontend\models\Order;
use app\frontend\modules\goods\models\Comment;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Yunshop\StoreCashier\common\models\StoreOrder;
class CommentController extends UploadVerificationApiController
{
    // 前端评论时，图片不能超过5张
    const COMMENT_IMAGE_COUNT = 5;

    public function getComment()
    {
        app('db')->cacheSelect = true;
        $goodsId = \YunShop::request()->goods_id;
        $pageSize = 20;
        $list = Comment::getCommentsByGoods($goodsId)->paginate($pageSize);//

        if ($list) {
            foreach ($list as &$item) {
                $item->reply_count = $item->hasManyReply->count('id');
                $item->head_img_url = $item->head_img_url ? replace_yunshop(yz_tomedia($item->head_img_url)) : yz_tomedia(\Setting::get('shop.shop.logo'));
            }
            //对评论图片进行处理，反序列化并组装完整图片url
            $list = $list->toArray();
            foreach ($list['data'] as &$item) {
                $item['nick_name'] = self::substrCut($item['nick_name']);
                self::unSerializeImage($item);
            }
//            $list['favorable_rate'] = $this->favorableRate($goodsId);
            return $this->successJson('获取评论数据成功!', $list);
        }
        return $this->errorJson('未检测到评论数据!', $list);
    }

    /*
     * 获取商品好评率
     */
    public function favorableRate($id)
    {
        $total = OrderGoods::where('goods_id',$id)->sum('id');
        $level_comment = \app\common\models\Comment::where(['goods_id' => $id])->sum('level');
        $comment = \app\common\models\Comment::where(['goods_id' => $id])->sum('id');
        $mark = bcmul($total,5,2);//总评分
        $no_comment = bcmul(bcsub($total,$comment,2) ,5,2);//未评分
        $have_comment = bcmul(bcdiv(bcadd($level_comment,$no_comment,2),$mark,2),100,2);//最终好评率
        return $have_comment.'%';

    }

    public function createComment()
    {
        $commentModel = new \app\common\models\Comment();
        $ingress = request()->ingress;
        $member = Member::getUserInfos(\YunShop::app()->getMemberId())->first();
        if (!$member) {
            return $this->errorJson('评论失败!未检测到会员数据!');
        }
        $commentStatus = '1';

        $comment = [
            'order_id' => \YunShop::request()->order_id,
            'goods_id' => \YunShop::request()->goods_id,
            'content' => \YunShop::request()->content,
            'level' => \YunShop::request()->level,
        ];

        if ($ingress && !empty($comment['content'])) {
            $check_result = (new MiniFileLimitService())->checkMsg($comment['content']);
            if ($check_result['errcode'] != 0) {
                return $this->errorJson('输入信息含有违法违规内容');
            }
        }

        if (!$comment['order_id']) {
            return $this->errorJson('评论失败!未检测到订单ID!');
        }
        if (!$comment['goods_id']) {
            return $this->errorJson('评论失败!未检测到商品ID!');
        }
        if (!$comment['content']) {
            return $this->errorJson('评论失败!未检测到评论内容!');
        }
        if (!$comment['level']) {
            return $this->errorJson('评论失败!未检测到评论等级!');
        }

        if (\YunShop::request()->images) {
            $comment['images'] = json_decode(\YunShop::request()->images);
            if (is_array($comment['images'])) {
                if (count($comment['images']) > self::COMMENT_IMAGE_COUNT) {
                    return $this->errorJson('追加评论失败!评论图片不能多于5张!');
                }
                $comment['images'] = serialize($comment['images']);
            } else {
                return $this->errorJson('追加评论失败!评论图片数据不正确!');
            }
        } else {
            $comment['images'] = serialize([]);
        }
        $plugin_id = \app\common\models\Order::select('plugin_id')->where('id',$comment['order_id'])->first();

        $is_open =app('plugins')->isEnabled('store-cashier');
        if ($is_open){
            $store_id = StoreOrder::select('store_id')->where('order_id',$comment['order_id'])->first();
        }


        $commentModel->setRawAttributes($comment);
        $commentModel->plugin_id = $plugin_id->plugin_id;
        $commentModel->plugin_table_id = $is_open ? $store_id->store_id : null;
        $commentModel->uniacid = \YunShop::app()->uniacid;
        $commentModel->uid = $member->uid;
        $commentModel->nick_name = $member->nickname;
        $commentModel->head_img_url = $member->avatar;
        $commentModel->type = '1';
        $res =  $this->insertComment($commentModel, $commentStatus);

        if(!is_null($event_arr = \app\common\modules\shop\ShopConfig::current()->get('after_comment_log'))){
            foreach ($event_arr as $v){
                $class    = array_get($v, 'class');
                $function = array_get($v, 'function');
                $class::$function(request());
            }
        }

        return $res;
    }

    public function appendComment()
    {
        $commentModel = new \app\common\models\Comment();
        $ingress = request()->ingress;
        $member = Member::getUserInfos(\YunShop::app()->getMemberId())->first();
        if (!$member) {
            return $this->errorJson('追加评论失败!未检测到会员数据!');
        }
        $commentStatus = '2';
        $id = \YunShop::request()->id;
        $append = $commentModel::find($id);
        if (!$append) {
            return $this->errorJson('追加评论失败!未检测到评论数据!');
        }

        $comment = [
            'order_id' => $append->order_id,
            'goods_id' => $append->goods_id,
            'content' => \YunShop::request()->content,
            'comment_id' => $append->id,
        ];

        if ($ingress && !empty($comment['content'])) {
            $check_result = (new MiniFileLimitService())->checkMsg($comment['content']);
            if ($check_result['errcode'] != 0) {
                return $this->errorJson('输入信息含有违法违规内容');
            }
        }

        if (!$comment['content']) {
            return $this->errorJson('追加评论失败!未检测到评论内容!');
        }

        if (\YunShop::request()->images) {
            $comment['images'] = json_decode(\YunShop::request()->images);
            if (is_array($comment['images'])) {
                if (count($comment['images']) > self::COMMENT_IMAGE_COUNT) {
                    return $this->errorJson('追加评论失败!评论图片不能多于5张!');
                }
                $comment['images'] = serialize($comment['images']);
            } else {
                return $this->errorJson('追加评论失败!评论图片数据不正确!');
            }
        } else {
            $comment['images'] = serialize([]);
        }

        $commentModel->setRawAttributes($comment);

        $commentModel->uniacid = \YunShop::app()->uniacid;
        $commentModel->uid = $member->uid;
        $commentModel->nick_name = $member->nickname;
        $commentModel->head_img_url = $member->avatar;
        $commentModel->reply_id = $append->uid;
        $commentModel->reply_name = $append->nick_name;
        $commentModel->type = '3';

        return $this->insertComment($commentModel, $commentStatus);

    }

    public function replyComment()
    {
        $commentModel = new \app\common\models\Comment();
        $member = Member::getUserInfos(\YunShop::app()->getMemberId())->first();
        if (!$member) {
            return $this->errorJson('回复评论失败!未检测到会员数据!');
        }

        $id = \YunShop::request()->id;
        $reply = $commentModel::find($id);
        if (!$reply) {
            return $this->errorJson('回复评论失败!未检测到评论数据!');
        }

        $comment = [
            'order_id' => $reply->order_id,
            'goods_id' => $reply->goods_id,
            'content' => \YunShop::request()->content,
            'comment_id' => $reply->comment_id ? $reply->comment_id : $reply->id,
        ];
        if (!$comment['content']) {
            return $this->errorJson('回复评论失败!未检测到评论内容!');
        }

//        if (isset($comment['images'] ) && is_array($comment['images'])) {
//            $comment['images'] = serialize($comment['images']);
//        } else {
//            $comment['images'] = serialize([]);
//        }
        if (\YunShop::request()->images) {
            $comment['images'] = json_decode(\YunShop::request()->images);
            if (is_array($comment['images'])) {
                if (count($comment['images']) > self::COMMENT_IMAGE_COUNT) {
                    return $this->errorJson('追加评论失败!评论图片不能多于5张!');
                }
                $comment['images'] = serialize($comment['images']);
            } else {
                return $this->errorJson('追加评论失败!评论图片数据不正确!');
            }
        } else {
            $comment['images'] = serialize([]);
        }

        $commentModel->setRawAttributes($comment);

        $commentModel->uniacid = \YunShop::app()->uniacid;
        $commentModel->uid = $member->uid;
        $commentModel->nick_name = $member->nickname;
        $commentModel->head_img_url = $member->avatar;
        $commentModel->reply_id = $reply->uid;
        $commentModel->reply_name = $reply->nick_name;
        $commentModel->type = '2';
        return $this->insertComment($commentModel);

    }

    public function insertComment($commentModel, $commentStatus = '')
    {
        $validator = $commentModel->validator($commentModel->getAttributes());
        if ($validator->fails()) {
            //检测失败
            return $this->errorJson($validator->messages());
        } else {
            //数据保存
            if ($commentModel->save()) {
                Goods::updatedComment($commentModel->goods_id);

                if ($commentStatus) {
                    OrderGoods::where('order_id', $commentModel->order_id)
                        ->where('goods_id', $commentModel->goods_id)
                        ->update(['comment_status' => $commentStatus, 'comment_id' => $commentModel->id]);
                }

                return $this->successJson('评论成功!',$commentModel);
            } else {
                return $this->errorJson('评论失败!');
            }
        }
    }


    public function getOrderGoodsComment()
    {
        $commentId = \YunShop::request()->comment_id;//评论主键ID
        $orderId = \YunShop::request()->order_id ?: 0;// 0为后台虚拟评论
        $goodsId = \YunShop::request()->goods_id;
        if (!$commentId) return $this->errorJson('获取评论失败!未检测到评论ID!');

        // 0
        if(empty($orderId)){
            $with = [
                'hasManyReply'=>function($query) {
                    $query->where('type', 2);
                },
                'hasOneGoods' => function($query) {
                    $query->select(['id','title','thumb','price']);
                }
            ];
            if (app('plugins')->isEnabled('live-install')){
                $with['hasOneLiveInstallComment'] = function ($query){
                    $query->select('id','comment_id','worker_score');
                };
            }
            $comment = Comment::uniacid()
                ->with($with)
                ->where('type', 1)
                ->where('id', $commentId)
                ->first();
            $comment['has_one_order_goods'] = $comment['hasOneGoods'];
            $comment['has_one_order_goods']['total'] = 1;
            $comment['has_one_order_goods']['thumb'] = yz_tomedia($comment['has_one_order_goods']['thumb']);
            $comment['head_img_url'] = yz_tomedia($comment['head_img_url']);
            unset($comment['hasOneGoods']);
        }else{
            $with = [
                'hasManyReply'=>function($query) {
                    $query->where('type', 2);
                },
                'hasOneOrderGoods' => function($query) use ($goodsId) {
                    $query->where('goods_id', $goodsId);
                },
            ];
            $comment = Comment::uniacid()
                ->with($with)
                ->where('type', 1)
                ->where('goods_id', $goodsId)
                ->where('id', $commentId)
                ->first();
        }

        if ($comment) {
            // 将图片字段反序列化
            $arrComment = $comment->toArray();

            if (!is_null($event_arr = \app\common\modules\shop\ShopConfig::current()->get('frontend_comment_detail'))) {
                foreach ($event_arr as $v) {
                    $class = array_get($v, 'class');
                    $function = array_get($v, 'function');
                    $res = $class::$function($arrComment);
                    foreach ($res as $vv) {
                        $arrComment[$vv['key']] = $vv;
                    }
                }
            }

            self::unSerializeImage($arrComment);
            return $this->successJson('获取评论数据成功!', $arrComment);
        }
        return $this->errorJson('未检测到评论数据!');
    }

    // 反序列化图片
    public static function unSerializeImage(&$arrComment)
    {
        $arrComment['images'] = unserialize($arrComment['images']);
        foreach ($arrComment['images'] as &$image) {
            $image = yz_tomedia($image);
        }
        if ($arrComment['append']) {
            foreach ($arrComment['append'] as &$comment) {
                $comment['images'] = unserialize($comment['images']);
                foreach ($comment['images'] as &$image) {
                    $image = yz_tomedia($image);
                }
            }
        }
        if ($arrComment['has_many_reply']) {
            foreach ($arrComment['has_many_reply'] as &$comment) {
                $comment['images'] = unserialize($comment['images']);
                foreach ($comment['images'] as &$image) {
                    $image = yz_tomedia($image);
                }
            }
        }
    }

    /**
     * 添加评论上传图片
     * @author
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload()
    {
        $file = request()->file('file');
        if (!$file) {
            return $this->errorJson('请传入正确参数.');
        }
        if ($file->isValid()) {
            // 获取文件相关信息
            $originalName = $file->getClientOriginalName(); // 文件原名
            $realPath = $file->getRealPath();   //临时文件的绝对路径
            $ext = $file->getClientOriginalExtension();
            $newOriginalName = md5($originalName . str_random(6)) . '.' . $ext;

            \Storage::disk('image')->put($newOriginalName, file_get_contents($realPath));

            return $this->successJson('上传成功', [
                'img'    => \Storage::disk('image')->url($newOriginalName),
            ]);
        } else {
            return $this->errorJson('上传失败!');
        }

    }

    /**
     * 只保留字符串首尾字符，隐藏中间用*代替（两个字符时只显示第一个）
     * @param string $user_name 姓名
     * @return string 格式化后的姓名
     */
    function substrCut($user_name){
        $strlen = mb_strlen($user_name, 'utf-8');
        $firstStr = mb_substr($user_name, 0, 1, 'utf-8');
        $lastStr = mb_substr($user_name, -1, 1, 'utf-8');
        if($strlen<2) {
            return $user_name;
        }
        else {
            return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
        }
    }


}