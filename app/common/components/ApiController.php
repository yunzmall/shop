<?php
/**
 * Created by PhpStorm.
 * Author:
 * Date: 2017/3/28
 * Time: 上午10:49
 */

namespace app\common\components;

use app\common\exceptions\ShopException;
use app\common\exceptions\UniAccountNotFoundException;
use app\common\helpers\Client;
use app\common\helpers\Url;
use app\common\middleware\AuthenticateFrontend;
use app\common\middleware\BasicInformation;
use app\common\models\Member;
use app\common\models\UniAccount;
use app\common\modules\shop\models\Shop;
use app\frontend\modules\member\services\factory\MemberFactory;

class ApiController extends BaseController
{

    protected $publicController = [];
    protected $publicAction = [];
    protected $ignoreAction = [];

    public function __construct()
	{
		parent::__construct();
		$this->middleware([AuthenticateFrontend::class]);
	}

	/**
     * @throws ShopException
     * @throws UniAccountNotFoundException
     */
    public function preAction()
    {
        parent::preAction();
    }

	public function getPublicController(): array
	{
		return $this->publicController;
	}

	public function getPublicAction(): array
	{
		return $this->publicAction;
	}

	public function getIgnoreAction(): array
	{
		return $this->ignoreAction;
	}
}