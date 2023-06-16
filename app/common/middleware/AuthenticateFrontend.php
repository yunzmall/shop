<?php
/**
 * Created by PhpStorm.
 *
 *
 *
 * Date: 2021/10/19
 * Time: 15:00
 */

namespace app\common\middleware;

use app\common\exceptions\ShopException;
use app\common\exceptions\UniAccountNotFoundException;
use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\models\Member;
use app\common\modules\shop\models\Shop;
use app\common\traits\JsonTrait;
use app\frontend\modules\member\services\factory\MemberFactory;
use Closure;

class AuthenticateFrontend
{
    use JsonTrait;

    public function handle($request, Closure $next)
    {
        if (empty(\YunShop::app()->account)) {
            throw new UniAccountNotFoundException('无此公众号', ['login_status' => -2]);
        }
        $mid = Member::getMid();
        $type = request()->input('type');
        $relation_status = Shop::current()->memberRelation['status'];
        $memberService = MemberFactory::create($type);
        $is_login = $memberService->checkLogged();

        //登录状态
        if ($is_login) {
            if (\app\frontend\models\Member::current()->yzMember->is_black) {
                return $this->errorJson('黑名单用户，请联系管理员', ['login_status' => -1]);
            }
            //发展下线
            Member::chkAgent(\YunShop::app()->getMemberId(), $mid);
        } else {
            $method = request()->route()->getActionMethod();
            $controller = $request->route()->getController();
            //验证是否需要登录
            if (($relation_status == 1 && !in_array($method, $controller->getIgnoreAction()))
                || ($relation_status == 0 && !in_array($method, $controller->getPublicAction()))
            ) {
                $this->jumpUrl($type, $mid);
            }
        }
        return $next($request);
    }

    /**
     * @param $type
     * @param $mid
     * @return bool|\Illuminate\Http\JsonResponse
     */
    protected function jumpUrl($type, $mid)
    {
        if (empty($type) || $type == 'undefined') {
            $type = Client::getType();
        }

        $scope = request()->input('scope', '');

        $queryString = ['type' => $type, 'i' => \YunShop::app()->uniacid, 'mid' => $mid, 'scope' => $scope];
        if (($scope == 'home' && !$mid) || $scope == 'pass') {
            return true;
        }
        if (in_array($type, [MemberFactory::LOGIN_MINI_APP, MemberFactory::LOGIN_DOUYIN, MemberFactory::LOGIN_MINI_APP_FACE])) {
            return $this->errorJson('请登录', ['login_status' => 0, 'login_url' => Url::absoluteApi('member.login.index', $queryString)]);
        }
        if (in_array($type, [MemberFactory::LOGIN_MOBILE, MemberFactory::LOGIN_APP_YDB, MemberFactory::LOGIN_Native, MemberFactory::LOGIN_APP_ANCHOR, MemberFactory::LOGIN_APP_LSP_WALLET])) {
            return $this->errorJson('请登录', ['login_status' => 1, 'login_url' => '', 'type' => $type, 'i' => \YunShop::app()->uniacid, 'mid' => $mid, 'scope' => $scope]);
        }

        return $this->errorJson('请登录', ['login_status' => 0, 'login_url' => Url::absoluteApi('member.login.index', $queryString)]);

    }
}