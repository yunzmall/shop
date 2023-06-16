<?php

namespace app;

use app\common\middleware\Install;
use app\common\middleware\ShopRoute;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        ShopRoute::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            //EncryptCookies::class,
            //\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            //\Illuminate\Session\Middleware\StartSession::class,
            //\Illuminate\View\Middleware\ShareErrorsFromSession::class,
            //VerifyCsrfToken::class,
        ],
        'admin' => [
            //EncryptCookies::class,
            Install::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ],
        'api' => [
            'throttle:60,1',
        ],
        'business' => [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Session\Middleware\AuthenticateSession::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \app\common\middleware\Authenticate::class,
        'authAdmin' => \app\common\middleware\AuthenticateAdmin::class,
        'authShop' => \app\common\middleware\AuthenticateShop::class,
        'checkPasswordSafe' => \app\common\middleware\CheckPasswordSafe::class,
        'shopBootStrap' => \app\common\middleware\ShopBootstrap::class,
        'check' => \app\common\middleware\Check::class,
        'business' => \business\middleware\Business::class,
        'businessLogin' => \business\middleware\BusinessLogin::class,
        'AuthenticateFrontend'=>\app\common\middleware\AuthenticateFrontend::class,
    ];
}
