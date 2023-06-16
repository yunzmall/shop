<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/4/25
 * Time: 10:03
 */

namespace app\frontend\modules\goods\services;


use app\backend\modules\goods\models\GoodsTradeSet;
use app\common\exceptions\MemberNotLoginException;
use app\common\facades\Setting;
use app\common\models\comment\CommentConfig;
use app\common\models\Goods;
use app\common\models\goods\GoodsSpecInfo;
use app\common\models\goods\Privilege;
use app\common\models\goods\Share;
use app\common\models\GoodsCategory;
use app\common\models\GoodsSetting;
use app\common\models\MemberLevel;
use app\common\models\SearchFiltering;
use app\common\services\goods\SaleGoods;
use app\common\traits\JsonTrait;
use app\frontend\models\GoodsOption;
use app\frontend\models\Member;
use app\frontend\models\OrderGoods;
use app\frontend\modules\coupon\controllers\MemberCouponController;
use app\frontend\modules\coupon\models\Coupon;
use app\frontend\modules\goods\models\Comment;
use app\frontend\modules\member\controllers\MemberFavoriteController;
use app\frontend\modules\member\controllers\MemberHistoryController;
use Carbon\Carbon;
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
        //会员价插件会导致商品类被重新绑定
        //$goods_model->vip_price_show = \app\common\models\Goods::find($goods_model->id)->vip_price;
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
        $this->goods_model->vip_price_show = \app\common\models\Goods::find($this->goods_model->id)->vip_price;
		$this->detail_data = $this->detail_data->merge(collect([
			'customer_service' => $this->getCustomerService(),        //获取客服
			'plugin' => $this->getAllPluginData(),        //插件配置
			'goods_type' => $this->goods_type,                //商品类型
			'get_goods' => $this->goods_model,                //商品信息
			'is_favorite' => $this->getIsFavorite(),            //是否已收藏
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
		$this->goods_model->setRelation('memberDiscount', $this->setMemberDiscount());
		//商品营销设置
		$this->goods_model->setRelation('goodsSale', $this->setAllGoodsSale());

		//怕报错加的
		if (!miniVersionCompare('1.1.126') || !versionCompare('1.1.125')) {
			//2021/9/7商品详情删除，通过新的优惠卷列表判断
			$this->goods_model->offsetSet('availability', $this->setCouponAvailability());
		}
		//用户商品可用可领优惠卷列表
		$this->goods_model->setRelation('availableCoupon', $this->availableCoupon());

		//优惠券
		$this->goods_model->offsetSet('coupon', $this->setCouponData());

		//加入足迹
		$this->joinHistory();

		$this->specInfo();

        // 联系电话
        $this->contactTel();
	}

	public function getNotLoginGoodsData()
	{
		//获取商品信息
		$this->getGoods();
		//商品验证
		$this->validateGoods();
		//商品基本信息处理
		$this->basicInformation();
		$this->specInfo();
        if (\YunShop::app()->getMemberId()) {
            //加入足迹
            $this->joinHistory();
        }
	}

	/**
	 *    获取商品信息
	 */
	public function getGoods()
	{
        $option_select = 'id,goods_id,title,thumb,product_price,cost_price,market_price,stock,specs,weight,product_sn';
		$this->goods_model->load([
			'hasManyParams' => function ($query) {
				return $query->select('goods_id', 'title', 'value')->orderby('displayorder', 'asc');
			},
			'hasManySpecs' => function ($query) {
				return $query->select('id', 'goods_id', 'title', 'description')->with(['hasManySpecsItem' => function ($specs) {
					return $specs->select('id', 'title', 'specid', 'thumb')->where('show', 1)->orderBy('display_order', 'asc');
				}])->orderBy('display_order', 'asc');
			},
			'hasManyOptions' => function ($query)use($option_select) {
				return $query->selectRaw($option_select);
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
		// 商品标签
		if (!$this->goods_model->hasManyGoodsFilter->isEmpty()){
            $filter_ids = $this->goods_model->hasManyGoodsFilter->pluck('filtering_id')->toArray();
            $this->goods_model->setRelation('hasManySearchFilter',SearchFiltering::getAllEnableFiltering()->whereIn('id',$filter_ids)->where('is_front_show',1)->values());
        }else{
            $this->goods_model->setRelation('hasManySearchFilter',collect(array()));
        }
		unset($this->goods_model->hasManyGoodsFilter);
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
			if (!empty(\YunShop::request()->pc) && app('plugins')->isEnabled('pc-terminal')) {
				$pc_status = \Yunshop\PcTerminal\service\SetService::getPcStatus(); //PC端开启状态
			}
			if (!empty($pc_status)) {
				$template = DecorateTempletModel::getList(['is_default' => 1, 'type' => 6], '*', false);
				if ($template->code == 'PCGoods02') {
					$is_new_goods = 1;
				}
			} else {
				$template = DecorateTempletModel::getList(['is_default' => 1, 'type' => 4], '*', false);
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
		$customer = app('GoodsDetail')->make('DetailsCollection')->where('customer', true)->first();
		if ($customer) {
			$customer_data = $customer->getAloneCustomer();
		}
		//获取商城配置
		$customer_data['cservice'] = $customer_data['cservice'] ?: $this->getDetailCustomerService();
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
			$cservice = \Setting::get('shop.shop')['cservice_mini'] ?: '';
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

		if (
            $this->hasOnePrivilege->buy_limit_status != 1
            && ($this->goods_model->hasOneGoodsLimitBuy->status == 1 && $this->goods_model->hasOneGoodsLimitBuy->end_time < time())
        ) {
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
        $show_push = $this->goods_model->hasOneSale ? SaleGoods::getPushGoods($this->goods_model->hasOneSale) : [];
        if ($show_push && $ids = array_column($show_push,'id')) {
            $options = GoodsOption::uniacid()->whereIn('goods_id',$ids)->get();
            foreach ($show_push as &$item) {
                if ($item['has_option']) {
                    $item['price'] = $options->where('goods_id',$item['id'])->min('product_price');
                }
            }
            unset($item);
        }
        $this->goods_model->offsetSet('show_push',$show_push);
		//商品评论
		$this->goods_model->offsetSet('get_comment', $this->setGoodsComment());
		//好评率
		$this->goods_model->offsetSet('favorable_rate', $this->setFavorableRate());

		$this->goods_model->offsetSet('min_buy_num',   $this->goods_model->hasOnePrivilege->min_buy_limit ?: 1);

		//商品详情
		$this->goods_model->content = changeUmImgPath($this->goods_model->content);
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

		//库存处理
		$this->setGoodsStock();

		$this->setCategoryToOption();

		//商品基础设置
		$this->setGoodsSetting();

        //商品评论设置
        $this->setCommentSetting();

        //商品交易设置
        $this->setGoodsTradeSet();

        //限时购处理
        $this->setGoodsBuyLimit();
    }

	/**
	 * 商品基础设置
	 * @return void
	 */
	public function setGoodsSetting()
	{
		$goodsSetting = GoodsSetting::getSet();
		//会员中心开关
		$this->goods_model->is_member_enter = isset($goodsSetting->is_member_enter) ? (int)$goodsSetting->is_member_enter : 1;
		$this->goods_model->detail_show = (int)$goodsSetting->detail_show ?? 0;
		//商品月销量处理
		$this->goods_model->is_month_sales = (int)$goodsSetting->is_month_sales ?? 0;
        //价格说明
        $this->goods_model->is_price_desc = isset($goodsSetting->is_price_desc) ? (int)$goodsSetting->is_price_desc : 0;
        $this->goods_model->price_desc_title = empty($goodsSetting->title) ? '价格说明' : $goodsSetting->title;
        $this->goods_model->explain = empty($goodsSetting->explain) ? '' : $goodsSetting->explain;

		if ($this->goods_model->is_month_sales) {
			$this->goods_model->month_sales = OrderGoods::uniacid()
				->select('total')
				->whereHas('hasOneOrder', function ($query) {
					return $query->select('id')->where('pay_time', '>', 0);
				})
				->where('goods_id', $this->goods_model->id)
				->whereBetween('created_at', [Carbon::now()->startOfMonth()->timestamp, Carbon::now()->endOfMonth()->timestamp])
				->sum('total');;
		}

        if (Setting::get('goods.profit_show_status')) {
            $goods_profit = bcsub($this->goods_model->price, $this->goods_model->cost_price, 2);
            if (bccomp($goods_profit, 0, 2) == -1) {
                $goods_profit = 0;
            }
            $this->goods_model->offsetSet('goods_profit', $goods_profit);
        }

        $hide_total_sales = 0;
        if (in_array($this->goods_model->plugin_id, [0, 67])) {
            $hide_total_sales = $this->goods_model->hide_goods_sales_alone ? $this->goods_model->hide_goods_sales : Setting::get('goods.hide_goods_sales');
        }
        $this->goods_model->offsetSet('hide_total_sales', $hide_total_sales ? 1 : 0);
	}

    /**
     * 评论设置
     */
    public function setCommentSetting(){
        $set=CommentConfig::getSetConfig();
        $this->goods_model->is_order_detail_comment_show=$set['is_order_detail_comment_show'];
    }

    /**
     * 商品交易设置
     */
    public function setGoodsTradeSet()
    {
        $goods_trade_set = GoodsTradeSet::where('goods_id', $this->goods_model->id)->first();
        if (!$goods_trade_set || !$goods_trade_set->arrived_day || !app('plugins')->isEnabled('address-code')) {
            $this->goods_model->show_time_word = '';
        } else {
            $arrived_day = $goods_trade_set->arrived_day;
            $arrived_word = $goods_trade_set->arrived_word;
            if ($arrived_day > 1) {
                $arrived_day -= 1;
                $time_format = Carbon::createFromTimestamp(time())->addDays($arrived_day)->format('Y-m-d');
            } else {
                $time_format = Carbon::createFromTimestamp(time())->format('Y-m-d');
            }
            $time_format .= " {$goods_trade_set->arrived_time}:00";
            $timestamp = strtotime($time_format);
            if ($timestamp < time()) {
                $timestamp += 86400;
            }
            $show_time = ltrim(date('m', $timestamp), '0').'月';
            $show_time .= ltrim(date('d', $timestamp), '0').'日';
            $show_time .= $goods_trade_set->arrived_time;
            $this->goods_model->show_time_word = str_replace('[送达时间]', $show_time, $arrived_word);
        }
    }

    /**
     * 商品限时购处理
     */
    public function setGoodsBuyLimit()
    {
        $this->goods_model->buy_limit = [
            'display_name' => '限时购',
            'start_time' => 0,//开始时间
            'end_time' => 0,//结束时间
            'buy_limit_type' => 'shop',//shop-商城的限时购，plugin-插件的限时购
            'status' => 0,//1-开启了限时购
        ];
        //商城限时购
        if ($this->goods_model->hasOnePrivilege->buy_limit_status == 1) {
            $start_times = [];
            $now_time = time();
            foreach ($this->goods_model->hasOnePrivilege->time_limits as $item) {
                $start_time = $item['time_limit'][0] / 1000;
                $end_time = $item['time_limit'][1] / 1000;

                if ($now_time >= $end_time) {
                    continue;
                }
                if ($start_times) {
                    //优先返回最近的时间
                    if ($start_time < $start_times[0]) {
                        $start_times = [$start_time,$end_time];
                    }
                } else {
                    $start_times = [$start_time,$end_time];
                }
            }

            $this->goods_model->buy_limit = [
                'display_name' => $this->goods_model->hasOnePrivilege->buy_limit_name ?: '限时购',
                'start_time' => (int)$start_times[0] ?: 0,//开始时间
                'end_time' => (int)$start_times[1] ?: 0,//结束时间
                'buy_limit_type' => 'shop',//shop-商城的限时购，plugin-插件的限时购
                'status' => 1,//1-开启了限时购
            ];
        } else {
            //限时购插件
            if ($this->goods_model->hasOneGoodsLimitBuy->status == 1) {
                $this->goods_model->buy_limit = [
                    'display_name' => $this->goods_model->hasOneGoodsLimitBuy->display_name ?: '限时购',
                    'start_time' => (int)$this->goods_model->hasOneGoodsLimitBuy->start_time ?: 0,//开始时间
                    'end_time' => (int)$this->goods_model->hasOneGoodsLimitBuy->end_time ?: 0,//结束时间
                    'buy_limit_type' => 'plugin',//shop-商城的限时购，plugin-插件的限时购
                    'status' => 1,//1-开启了限时购
                ];
            }
        }

    }

	public function setGoodsStock()
	{
		if ($this->goods_model->has_option) {//有规格
			$this->goods_model->offsetSet('show_stock', $this->goods_model->hasManyOptions->sum('stock'));
		} else {
			$this->goods_model->offsetSet('show_stock', $this->goods_model->stock);
		}
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
        $goods_profit_show = Setting::get('goods.profit_show_status');
		//商品规格图片处理
		foreach ($this->goods_model->hasManyOptions as $item) {
			$item->thumb = replace_yunshop(yz_tomedia($item->thumb));
            if ($goods_profit_show) {
                $item->goods_profit = bcsub($item->product_price, $item->cost_price, 2);
                $item->goods_profit = bccomp($item->goods_profit, 0, 2) == -1 ? 0 : $item->goods_profit;
            }
		}
		//商品显示价格
		if ($this->goods_model->has_option) {
			$this->goods_model->min_price = $this->goods_model->hasManyOptions->min("product_price");
			$this->goods_model->max_price = $this->goods_model->hasManyOptions->max("product_price");
			$this->goods_model->stock = $this->goods_model->hasManyOptions->sum('stock');
            if ($goods_profit_show) {
                $this->goods_model->min_goods_profit = $this->goods_model->hasManyOptions->min('goods_profit');
            }
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
		} else {
			$this->goods_model->setRelation('hasOneShare', new Share([
				'share_title' => '',
				'share_thumb' => '',
				'share_desc' => ''
			]));
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
		$list = Comment::getCommentsByGoods($this->goods_model->id,false)->paginate($pageSize);
		if ($list->isEmpty()) {
			return [];
		}
		foreach ($list as &$item) {
            //追评ID
            if ($item->content == '' && $item->additional_comment_id != 0) {
                $item->content = $item->append->content;
            }

			$item->nick_name = substrCut($item->nick_name);
			$item->reply_count = $item->hasManyReply()->count('id');
			$item->head_img_url = $item->head_img_url ? replace_yunshop(yz_tomedia($item->head_img_url)) : yz_tomedia(\Setting::get('shop.shop.logo'));
		    if(!$item->uid && $item->level_set){
		        //后台手动设置等级
                $level = MemberLevel::uniacid()->find($item->level_set);
                $item->level_name = $level->level_name ?? Setting::get('shop.member.level_name') ?? "普通会员";
            }else{
                $item->level_name = $item->hasOneMember->yzMember->level->level_name ?? Setting::get('shop.member.level_name') ?? "普通会员";
            }
		}
		//对评论图片进行处理，反序列化并组装完整图片url
		$list = $list->toArray();

        $list['is_show_good_reputation_text'] = 0;//默认好评已隐藏字样：0-不显示，1-显示
		if (Comment::isShowGoodReputationText($this->goods_model->id)) {
            $list['is_show_good_reputation_text'] = 1;
        }

		foreach ($list['data'] as &$item) {
			$item['images'] = unserialize($item['images']);
			foreach ($item['images'] as &$image) {
				$image = yz_tomedia($image);
			}
            $item['append']['images'] = unserialize($item['append']['images']);
            foreach ($item['append']['images'] as &$image) {
                $image = yz_tomedia($image);
            }
			foreach ($item['has_many_reply'] as &$comment) {
				$comment['images'] = unserialize($comment['images']);
				foreach ($comment['images'] as &$image) {
					$image = yz_tomedia($image);
				}
			}
		}

        $list['total_summary'] = $this->getCommentTotalSummary(Comment::getAllCommentTotal($this->goods_model->id));

        app('db')->cacheSelect = false;
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
     * 获取评论总数概括 100+,200+,1000+.......
     * @param $total
     * @return string
     */
    final public function getCommentTotalSummary($total): string
    {
        $numLen = strlen(floor($total));//总数的位数
        if ($total <= 100) {
            $summary_total = $total;
        } elseif ($total > 100 && $total < 10000) {
            if ($numLen == 3) {
                $numMsg = '00+';
            } else {
                $numMsg = '000+';
            }
            $summary_total = substr_replace($total, $numMsg, 1, $numLen);
        } else {
            $wanLen = 5;//一万的位数
            $wanNowLen = $numLen - ($wanLen - 1);//万现在的位数

            $summary_total = substr_replace($total, str_pad(substr($total, 0, 1), $wanNowLen, '0') . '万+', 0);
        }
        return (string)$summary_total;
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
				if ($value->time_limit == 1 && (time() < $value->time_start->getTimestamp() || time() > $value->time_end->getTimestamp())) {
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
		app('GoodsDetail')->make('DetailsCollection')->each(function ($class) use ($method, &$sale) {
			if (method_exists($class, $method)) {
				$sale = $sale->merge($class->$method());
			}
		});
		$sale = $sale->sortBy('weight')->values();
		return collect([
			'sale_count' => $sale->count(),
			'first_strip_key' => $sale->first() ?: [],
			'sale' => $sale->all()
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
	    $good_style_set = Setting::get('plugin.good_style.style')==2?2:1;
        $discount_switch = \Setting::get('shop.member.discount');
        $level_type = \Setting::get('shop.member.level_type');
        $display_page = \Setting::get('shop.member.display_page');
	    if ($good_style_set == 2) {
            if ($this->goods_model->has_option && $this->goods_model->hasManyOptions) {
                $option = $this->goods_model->hasManyOptions->sortBy('product_price')->first();
                list($all_level_prices, $can_upgrade) = $option->all_level_price;
            } else {
                list($all_level_prices, $can_upgrade) = $this->goods_model->all_level_price;
            }
            if ($couponInfo = $this->setCouponData()) {
                // 按照立减计算
                if ($couponInfo['coupon_method'] == 1) {
                    foreach ($all_level_prices as &$all_p) {
                        $all_p['price'] = max(bcsub($all_p['price'], $couponInfo['deduct'], 2),0);
                    }
                }
                // 按照打折计算
                if ($couponInfo['coupon_method'] == 2) {
                    $discount = bcdiv($couponInfo['discount'], 10, 2);
                    $all_p['price'] = max(bcmul($all_p['price'], $discount, 2),0);
                }
            }
            $data = [
                'all_level_price' => $all_level_prices,
                'level_type' => $level_type,
                'display_page' => $display_page,
                'discount' => $discount_switch,
                'can_upgrade' => $can_upgrade,
            ];
	        return new Collection($data);
        } else {
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
            if ($this->goods_model->has_option && $this->goods_model->hasManyOptions) {
                //todo 处理商品未设置基础价格vip价格需要显示规格最低价
                $option = $this->goods_model->hasManyOptions->sortBy('product_price')->first();
                $data['discount_value'] = $option->vip_price;
                $data['next_level_price'] = $option->next_vip_price;
            }
            // 当商品显示券后价时, 对应的会员显示也需要减去券后价来显示
            $couponInfo = $this->setCouponData();
            if ($couponInfo) {
                // 按照立减计算
                if ($couponInfo['coupon_method'] == 1) {
                    $data['discount_value'] = max(bcsub($data['discount_value'], $couponInfo['deduct'], 2),0);
                    $data['next_level_price'] = max(bcsub($data['next_level_price'], $couponInfo['deduct'], 2),0);
                }
                // 按照打折计算
                if ($couponInfo['coupon_method'] == 2) {
                    $discount = bcdiv($couponInfo['discount'], 10, 2);
                    $data['discount_value'] = max(bcmul($data['discount_value'], $discount, 2),0);
                    $data['next_level_price'] = max(bcmul($data['next_level_price'], $discount, 2),0);
                }
            }
            return new Collection($data);
        }
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
			if ($value->time_limit == 1 && (time() < $value->time_start->getTimestamp() || time() > $value->time_end->getTimestamp())) {
				continue;
			}

			switch ($value->use_type) {
				case Coupon::COUPON_SHOP_USE: //商城通用
					if (!in_array($this->goods_mode->plugin_id, [31, 32, 33, 36, 92, 101])) {
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

	//规格信息
	public function specInfo()
	{
		$spec_info = GoodsSpecInfo::uniacid()
			->select('goods_id', 'goods_option_id', 'info_img', 'content')
			->where('goods_id', $this->goods_model->id)
			->orderBy('sort', 'desc')
			->get()->toArray();
		$this->goods_model->offsetSet('spec_info', $spec_info);
	}

    /**
     * @description 联系电话
     * @return void
     */
    public function contactTel()
    {
        $tel = Setting::get('shop.contact.phone') ?: '';

        if ($this->goods_model->hasOneContactTel && $this->goods_model->hasOneContactTel->contact_tel) {
            $tel = $this->goods_model->hasOneContactTel->contact_tel;
        }

        $this->goods_model->offsetSet('contact_tel', $tel);
    }

	//处理分类关联规格
	public function setCategoryToOption()
	{
		$category_option_id = request()->category_option_id;
		if (!$category_option_id || !$this->goods_model->has_option) {
			return;
		}
		$option = GoodsOption::select('thumb')
			->where('id', $category_option_id)
			->where('goods_id', $this->goods_model->id)
			->first();
		if (!$option || !$option->thumb) {
			return;
		}
		$this->goods_model->thumb_url = array_merge([yz_tomedia($option->thumb)], $this->goods_model->thumb_url);
		$this->goods_model->thumb = yz_tomedia($option->thumb);
	}

	public function __get($name)
	{
		//返回实例化类的属性
		return app('GoodsDetail')->make('GoodsDetailInstance')->$name;
	}

	public function __set($name, $value)
	{
		app('GoodsDetail')->make('GoodsDetailInstance')->$name = $value;
	}


    public function __call($method, $params)
	{
		return null;
	}

}