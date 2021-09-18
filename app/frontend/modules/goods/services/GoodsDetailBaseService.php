<?php
/**
 * Created by PhpStorm.
 * Name: 芸众商城系统
 * Author: 广州市芸众信息科技有限公司
 * Profile: 广州市芸众信息科技有限公司位于国际商贸中心的广州，专注于移动电子商务生态系统打造，拥有芸众社交电商系统、区块链数字资产管理系统、供应链管理系统、电子合同等产品/服务。官网 ：www.yunzmall.com  www.yunzshop.com
 * Date: 2021/4/25
 * Time: 10:03
 */

namespace app\frontend\modules\goods\services;


use app\common\exceptions\MemberNotLoginException;
use app\common\models\Goods;
use app\common\models\goods\Privilege;
use app\common\models\GoodsCategory;
use app\common\models\MemberLevel;
use app\common\services\goods\SaleGoods;
use app\common\traits\JsonTrait;
use app\frontend\models\Member;
use app\frontend\modules\coupon\controllers\MemberCouponController;
use app\frontend\modules\coupon\models\Coupon;
use app\frontend\modules\goods\models\Comment;
use app\frontend\modules\member\controllers\MemberFavoriteController;
use app\frontend\modules\member\controllers\MemberHistoryController;
use Illuminate\Database\Eloquent\Collection;
use Yunshop\Decorate\models\DecorateTempletModel;
use Yunshop\Designer\models\ViewSet;

abstract class GoodsDetailBaseService
{
	use JsonTrait;
	
	private $goods_model;

	private $detail_data;

	private $member;

	private $is_new_goods;

	private $plugin_id = -1;

	public function init(Goods $goods_model)
	{
		$this->goods_model = $goods_model;
		$this->detail_data = collect();
	}

	/**
	 * 商品详情数据
	 */
	public function getData()
	{
		//获取是哪个模板
		$view_type = $this->getViewType();
		switch ($view_type) {
			case 0:
				$this->getLoginGoodsData();
				break;
			case 1:
				$this->getNotLoginGoodsData();
				break;
		}
		$this->detail_data = $this->detail_data->merge(collect([
			'customer_service' => $this->getCustomerService(),		//获取客服
			'plugin'		   => $this->getAllPluginData(),        //插件配置
			'goods_type'	   => $this->goods_type,                //商品类型
			'get_goods'		   => $this->goods_model,        		//商品信息
		]));
	}

	public function getLoginGoodsData()
	{
		//获取用户信息
		$this->member = $this->getMemberModel();
		//获取商品信息
		$this->getGoods();
		//验证浏览权限
		$this->validatePrivilege();
		//商品验证
		$this->validateGoods();
		//商品基本信息处理
		$this->basicInformation();
		//会员折扣
		$this->goods_model->setRelation('memberDiscount',$this->setMemberDiscount());
		//商品营销设置
		$this->goods_model->setRelation('goodsSale',$this->setAllGoodsSale());

		//怕报错加的
        if (!miniVersionCompare('1.1.126') || !versionCompare('1.1.125')) {
            //2021/9/7商品详情删除，通过新的优惠卷列表判断
            $this->goods_model->offsetSet('availability',$this->setCouponAvailability());
        }
        //用户商品可用可领优惠卷列表
        $this->goods_model->setRelation('availableCoupon',$this->availableCoupon());

		//优惠券
		$this->goods_model->offsetSet('coupon',$this->setCouponData());

		//加入足迹
		$this->joinHistory();
		//是否已收藏
		$this->detail_data->put('is_favorite',$this->getIsFavorite());
	}

	public function getNotLoginGoodsData()
	{
		//获取商品信息
		$this->getGoods();
		//商品验证
		$this->validateGoods();
		//商品基本信息处理
		$this->basicInformation();
	}

	/**
	 * 	获取商品信息
	 */
	public function getGoods()
	{
		$this->goods_model->load([
			'hasManyParams' => function ($query) {
				return $query->select('goods_id', 'title', 'value')->orderby('displayorder','asc');
			},
			'hasManySpecs' => function ($query) {
				return $query->select('id', 'goods_id', 'title', 'description')->with(['hasManySpecsItem'=> function ($specs) {
					return $specs->select('id', 'title', 'specid', 'thumb')->where('show', 1)->orderBy('display_order', 'asc');
				}])->orderBy('display_order', 'asc');
			},
			'hasManyOptions' => function ($query) {
				return $query->select('id', 'goods_id', 'title', 'thumb', 'product_price', 'cost_price', 'market_price', 'stock', 'specs', 'weight');
			},
			'hasOneBrand' => function ($query) {
				return $query->select('id', 'logo', 'name', 'desc');
			},
			'hasOneShare',
			'hasOneGoodsDispatch',
			'hasOnePrivilege',
			'hasOneSale',
			'hasOneGoodsCoupon',
			'hasOneInvitePage',
			'hasOneGoodsLimitBuy',
			'hasOneGoodsVideo',
			'hasOneGoodsAdvertising',
		]);
		if ($this->is_new_goods == 0) {
			$this->goods_model->load(['hasManyDiscount' => function ($query) {
				return $query->where('level_id', $this->member->level_id);
			}]);
		}
	}

	/**
	 * 获取商品详情模板
	 * @return int
	 */
	private function getViewType()
	{
		$is_new_goods = 0;
		if (app('plugins')->isEnabled('decorate') && \Setting::get('plugin.decorate.is_open') == "1") {
			//商品模版
			if(!empty(\YunShop::request()->pc) && app('plugins')->isEnabled('pc-terminal')){
				$pc_status = \Yunshop\PcTerminal\service\SetService::getPcStatus(); //PC端开启状态
			}
			if (!empty($pc_status)) {
				$template = DecorateTempletModel::getList(['is_default'=>1,'type'=>6],'*',false);
				if ($template->code == 'PCGoods02') {
					$is_new_goods = 1;
				}
			} else{
				$template = DecorateTempletModel::getList(['is_default'=>1,'type'=>4],'*',false);
				if ($template->code == 'goods02') {
					$is_new_goods = 1;
				}
			}
		} elseif (app('plugins')->isEnabled('designer')) {
			//商品模版
			$code = ViewSet::uniacid()->where('type', 'goods')->first()->names;
			if ($code == '02') {
				$is_new_goods = 1;
			}
		}
		return $this->is_new_goods = $is_new_goods;
	}

	/**
	 * 获取客服设置
	 * @return mixed
	 */
	public function getCustomerService()
	{
		//获取全局配置
		$customer = app('GoodsDetail')->make('DetailsCollection')->where('customer',true)->first();
		if ($customer) {
			$customer_data = $customer->getAloneCustomer();
		}
		//获取商城配置
		$customer_data['cservice'] = $customer_data['cservice']?:$this->getDetailCustomerService();
		return $customer_data;
	}

	/**
	 * 获取商品默认客服配置
	 * @return mixed
	 */
	public function getDetailCustomerService()
	{
	    if (request()->type == 2) {
	        //小程序
            $cservice = \Setting::get('shop.shop')['cservice_mini']?:'';
        } else {
            $cservice = \Setting::get('shop.shop')['cservice'];
        }
        return $cservice;
    }

    /**
     * 获取所有插件配置
     * @return array
     */
    public function getAllPluginData()
    {
        $method = 'getPluginData';
        $plugin = [];
        app('GoodsDetail')->make('DetailsCollection')->each(function ($class) use ($method, &$plugin) {
            if (method_exists($class, $method)) {
                $plugin = array_merge($plugin, $class->$method());
            }
        });
        return $plugin;
    }

    /**
     * 验证商品
     * @return mixed
     */
    public function validateGoods()
    {
        if (empty($this->goods_model)) {
            return $this->errorJson('商品不存在.');
        }

        if ($this->goods_model->hasOneGoodsLimitBuy->status == 1 && $this->goods_model->hasOneGoodsLimitBuy->end_time < time()) {
            return $this->errorJson('商品限时购已到期.');
        }

        if (!$this->goods_model->status) {
            return $this->errorJson('商品已下架.');
        }
        //插件验证
        $method = 'validateGoodsPlugin';
        app('GoodsDetail')->make('DetailsCollection')->each(function ($class) use ($method, &$plugin) {
            if (method_exists($class, $method)) {
                $class->$method();
            }
        });
    }

    /**
     * 验证访问权限
     * @throws \app\common\exceptions\AppException
     */
    public function validatePrivilege()
    {
        Privilege::validatePrivilegeLevel($this->goods_model, $this->member);
        Privilege::validatePrivilegeGroup($this->goods_model, $this->member);
    }

    /**
     * 获取用户信息
     * @return \app\backend\modules\member\models\MemberShopInfo|void
     * @throws MemberNotLoginException
     * @throws \app\common\exceptions\AppException
     */
    public function getMemberModel()
    {
        try {
            return Member::current()->yzMember;
        } catch (MemberNotLoginException  $e) {
            if (\YunShop::request()->type == 1) {
                return;
            }
            throw new MemberNotLoginException($e->getMessage(), $e->getData());
        }
    }

    /**
     * 设置商品基础信息
     */
    public function basicInformation()
    {
        $this->goods_model->setHidden([
            'deleted_at',
            'created_at',
            'updated_at',
            'cost_price',
            'real_sales',
            'is_deleted',
            'reduce_stock_method'
        ]);
        $this->goods_model->offsetSet('is_added', \Setting::get('shop.member.added') ?: 1);
        //该商品下的推广
        $this->goods_model->offsetSet('show_push', $this->goods_model->hasOneSale ? SaleGoods::getPushGoods($this->goods_model->hasOneSale) : []);
        //商品评论
        $this->goods_model->offsetSet('get_comment', $this->setGoodsComment());
        //好评率
        $this->goods_model->offsetSet('favorable_rate', $this->setFavorableRate());
        //商品详情
        $this->goods_model->content = html_entity_decode($this->goods_model->content);
        //商品图片处理
        if ($this->goods_model->thumb) {
            $this->goods_model->thumb = yz_tomedia($this->goods_model->thumb);
        }
        if ($this->goods_model->thumb_url) {
            $thumb_url = unserialize($this->goods_model->thumb_url);
            foreach ($thumb_url as &$item) {
                $item = yz_tomedia($item);
            }
            $this->goods_model->thumb_url = $thumb_url;
        }
        //商品品牌处理
        $this->setBrand();

        //商品规格
        $this->setOption();

        //商品视频处理
        $this->setGoodsVideo();

        //分享处理
        $this->setGoodsShare();


    }

    /**
     * 商品品牌处理
     */
    public function setBrand()
    {
        if ($this->goods_model->hasOneBrand) {
            $this->goods_model->hasOneBrand->desc = html_entity_decode($this->goods_model->hasOneBrand->desc);
            $this->goods_model->hasOneBrand->logo = yz_tomedia($this->goods_model->hasOneBrand->logo);
        }
    }

    /**
     * 商品规格处理
     */
    public function setOption()
    {
        //商品规格图片处理
        foreach ($this->goods_model->hasManyOptions as $item) {
            $item->thumb = replace_yunshop(yz_tomedia($item->thumb));
        }
        //商品显示价格
        if ($this->goods_model->has_option) {
            $this->goods_model->min_price = $this->goods_model->hasManyOptions->min("product_price");
            $this->goods_model->max_price = $this->goods_model->hasManyOptions->max("product_price");
            $this->goods_model->stock = $this->goods_model->hasManyOptions->sum('stock');
        }
        //规格项图片
        $this->goods_model->hasManySpecs->transform(function ($specs) {
            $specs->hasManySpecsItem->transform(function ($item) {
                $item->thumb = yz_tomedia($item['thumb']);
                return $item;
            });
            $specs->setRelation('specitem', $specs->hasManySpecsItem);
            unset($specs->hasManySpecsItem);
            return $specs;
        });
    }

    /**
     * 商品视频处理
     */
    public function setGoodsVideo()
    {
        if ($this->goods_model->hasOneGoodsVideo->goods_video) {
            $this->goods_model->goods_video = yz_tomedia($this->goods_model->hasOneGoodsVideo->goods_video);
            $this->goods_model->video_image = $this->goods_model->hasOneGoodsVideo->video_image ? yz_tomedia($this->goods_model->hasOneGoodsVideo->video_image) : yz_tomedia($this->goods_model->thumb);
        } else {
            $this->goods_model->goods_video = '';
            $this->goods_model->video_image = '';
        }
    }

    /**
     * 商品分享处理
     */
    public function setGoodsShare()
    {
        if ($this->goods_model->hasOneShare) {
            $this->goods_model->hasOneShare->share_thumb = yz_tomedia($this->goods_model->hasOneShare->share_thumb);
        }
    }

    /**
     * 商品评论处理
     * @return array
     */
    public function setGoodsComment()
    {
        app('db')->cacheSelect = true;
        $pageSize = 5;
        $list = Comment::getCommentsByGoods($this->goods_model->id)->paginate($pageSize);
        if ($list->isEmpty()) {
            return [];
        }
        foreach ($list as &$item) {
            $item->nick_name = substrCut($item->nick_name);
            $item->reply_count = $item->hasManyReply->count('id');
            $item->head_img_url = $item->head_img_url ? replace_yunshop(yz_tomedia($item->head_img_url)) : yz_tomedia(\Setting::get('shop.shop.logo'));
        }
        //对评论图片进行处理，反序列化并组装完整图片url
        $list = $list->toArray();
        foreach ($list['data'] as &$item) {
            $item['images'] = unserialize($item['images']);
            foreach ($item['images'] as &$image) {
                $image = yz_tomedia($image);
            }
            foreach ($item['append'] as &$comment) {
                $comment['images'] = unserialize($comment['images']);
                foreach ($comment['images'] as &$image) {
                    $image = yz_tomedia($image);
                }
            }
            foreach ($item['has_many_reply'] as &$comment) {
                $comment['images'] = unserialize($comment['images']);
                foreach ($comment['images'] as &$image) {
                    $image = yz_tomedia($image);
                }
            }
        }
        return $list;
    }

    /**
     * 获取商品好评率
     * @return string
     */
    public function setFavorableRate()
    {
        //不跟订单关联的新好评率计算公式
        $total = \app\common\models\Comment::where(['goods_id' => $this->goods_model->id, 'type' => 1])->count('id');//总评论数
        if ($total <= 0) return '100%';
        $level_comment = \app\common\models\Comment::where(['goods_id' => $this->goods_model->id, 'type' => 1])->sum('level');//已评论的分数
        $mark = bcmul($total, 5, 2);//最高总评分  = 总条数 * 5
        //最终好评率 = （已评论分数/最高总评分）/100
        $have_comment = bcmul(bcdiv($level_comment, $mark, 2), 100, 2);
        return $have_comment . '%';
    }

    /**
     * 商品是否已收藏
     * @return mixed
     */
    public function getIsFavorite()
    {
        return (new MemberFavoriteController())->isFavorite(request(), true)['json'];
    }

    /**
     * 加入商品访问足迹
     */
    public function joinHistory()
    {
        (new MemberHistoryController())->store(request(), true);
    }

    /**
     * 获取商品优惠券信息
     * @return array
     */
    final public function setCouponData()
    {
        //优惠券价
        $goods_coupon = null;
        if (\Setting::get('shop.coupon.is_show_coupon')) {
            $coupon = \app\common\models\Coupon::uniacid()->where('use_type', 2)->where('get_type', 1)->get();
            foreach ($coupon as $key => $value) {
                if ($value->time_limit == 1 && (time() < $value->time_start || time() > $value->time_end)) {
                    continue;
                }
                if (in_array($this->goods_model->id, $value['goods_ids']) || $this->goods_model->id == $value['goods_ids']) {
                    $max_price = $this->goods_model->max_price ?: $this->goods_model->price;
                    if ($value['enough'] > 0 && $max_price < $value['enough']) {
                        return $goods_coupon;
                    }
                    if ($value['coupon_method'] == 1) {
                        $goods_coupon['deduct_price'] = bcsub($max_price, $value['deduct'], 2);  //立减折扣//抵扣金额
                        $goods_coupon['coupon_method'] = $value['coupon_method'];
                        $goods_coupon['deduct'] = $value['deduct'];
                        $goods_coupon['discount'] = $value['discount'];
                    } else if ($value['coupon_method'] == 2) {
                        $goods_coupon['deduct_price'] = bcmul($max_price, $value['discount'] / 10, 2); //打折优惠
                        $goods_coupon['coupon_method'] = $value['coupon_method'];
                        $goods_coupon['discount'] = $value['discount'];
                        $goods_coupon['deduct'] = $value['deduct'];
                    }
                    if ($goods_coupon['deduct_price'] < 0) {
                        $goods_coupon['deduct_price'] = 0;
                    }
                }
            }
        }
        return $goods_coupon;
    }

	/**
	 * 获取所有商品营销（包含插件）
	 * @return Collection
	 */
	final public function setAllGoodsSale()
	{
		$method = 'getGoodsSale';
		$sale = collect();
		app('GoodsDetail')->make('DetailsCollection')->each(function ($class) use ($method,&$sale) {
			if (method_exists($class,$method)) {
				$sale = $sale->merge($class->$method());
			}
		});
		$sale = $sale->sortBy('weight')->values();
		return collect([
			'sale_count' 		=> $sale->count(),
			'first_strip_key'	=> $sale->first()?:[],
			'sale'				=> $sale->all()
		]);
	}

	/**
	 * 获取会员折扣
	 * @return Collection
	 */
	final public function setMemberDiscount()
	{
		if ($this->goods_model->vip_price === null) {
			return new Collection();
		}
		$discount_switch = \Setting::get('shop.member.discount');
		$level_type = \Setting::get('shop.member.level_type');
		$display_page = \Setting::get('shop.member.display_page');
		if ($this->member->level) {
			$data = [
				'level_name' => $this->member->level->level_name,
				'discount_value' => $this->goods_model->vip_price,
				'discount' => $discount_switch,
				'next_level_price' => $this->goods_model->next_level_price,
				'next_level_name' => $this->goods_model->next_level_name,
				'level_type' => $level_type,
				'display_page' => $display_page
			];
		} else {
			$level = \Setting::get('shop.member.level_name');
			$level_name = $level ?: '普通会员';
			$data = [
				'level_name' => $level_name,
				'discount_value' => $this->goods_model->vip_price,
				'discount' => $discount_switch,
				'next_level_price' => $this->goods_model->next_level_price,
				'next_level_name' => $this->goods_model->next_level_name ?: MemberLevel::value('level_name'),
				'level_type' => $level_type,
				'display_page' => $display_page
			];
		}
		return new Collection($data);
	}

	/**
	 * 是否可用优惠券
	 * @return int
	 */
	final public function setCouponAvailability()
	{
		$memberLevel = $this->member->level_id;
		$now = strtotime('now');
		$coupons = Coupon::getCouponsForMember($this->member->member_id, $memberLevel, null, $now)
			->orderBy('display_order', 'desc')
			->orderBy('updated_at', 'desc')
			->get();
		if ($coupons->isEmpty()) {
			return 0;
		}

		foreach ($coupons as $v) {
			if (($v->total == MemberCouponController::NO_LIMIT) || ($v->has_many_member_coupon_count < $v->total)) {
				return 1;
			}
		}
		return 0;
	}

    /**
     * 商品可用和可领取优惠卷列表
     * @param $coupons
     */
    public function availableCoupon()
    {

        $memberLevel = $this->member->level_id;
        $now = strtotime('now');
        $coupons = Coupon::getCouponsForMember($this->member->member_id, $memberLevel, null, $now)
            ->orderBy('display_order', 'desc')
            ->orderBy('updated_at', 'desc')
            ->get();
        if ($coupons->isEmpty()) {
            return new Collection([]);
        }

        $goodsCategory = GoodsCategory::select('category_id')->where('goods_id', $this->goods_model->id)->get()->pluck('category_id')->toArray();
        $coupon_list = [];
        foreach ($coupons as $value) {
            if ($value->time_limit == 1 && (time() < $value->time_start || time() > $value->time_end)) {
                continue;
            }

            switch ($value->use_type) {
                case Coupon::COUPON_SHOP_USE: //商城通用
                    if (!in_array($this->goods_mode->plugin_id,[31,32,33,36,92,101])) {
                        $coupon_list[] = $value;
                    }
                    break;
                case Coupon::COUPON_GOODS_USE: //指定商品
                    if (in_array($this->goods_model->id, $value['goods_ids'])) {
                        $coupon_list[] = $value;
                    }
                    break;
                case Coupon::COUPON_GOODS_AND_STORE_USE:  //指定商品+指定门店
                    $use_conditions = unserialize($value->use_conditions);
                    if (($use_conditions['is_all_good'] && $this->goods_model->plugin_id == 0) || in_array($this->goods_model->id, $use_conditions['good_ids'])) {
                        $coupon_list[] = $value;
                    }
                    break;
                case Coupon::COUPON_CATEGORY_USE:  //指定分类
                    //商品分类存在该优惠卷中
                    if ($this->goods_mode->plugin_id != 92 && $value['category_ids'] && array_intersect($goodsCategory, $value['category_ids'])) {
                        $coupon_list[] = $value;
                    }
                    break;
                default:
            }

            if (count($coupon_list) > 9) {
                break;
            }
        }

        return new Collection($coupon_list);

    }

	public function __get($name)
	{
		//返回实例化类的属性
		return app('GoodsDetail')->make('GoodsDetailInstance')->$name;
	}

	public function __set($name,$value)
	{
		app('GoodsDetail')->make('GoodsDetailInstance')->$name = $value;
	}


	public function __call($method,$params)
	{
		return null;
	}

}