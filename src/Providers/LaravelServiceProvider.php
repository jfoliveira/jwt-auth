<?php

/*
 * This file is part of jwt-auth.
 *
 * @author Sean Tymon <tymon148@gmail.com>
 * @copyright Copyright (c) Sean Tymon
 * @link https://github.com/tymondesigns/jwt-auth
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tymon\JWTAuth\Providers;

use Tymon\JWTAuth\Middleware\Check;
use Tymon\JWTAuth\Middleware\RefreshToken;
use Tymon\JWTAuth\Middleware\Authenticate;

class LaravelServiceProvider extends LumenServiceProvider
{
    /**
     * Boot the service provider.
     */
    public function boot()
    {
        $path = realpath(__DIR__ . '/../../config/config.php');

        $this->publishes([$path => config_path('jwt.php')], 'config');
        $this->mergeConfigFrom($path, 'jwt');

        $this->app['router']->middleware('jwt.auth', Authenticate::class);
        $this->app['router']->middleware('jwt.refresh', RefreshToken::class);
        $this->app['router']->middleware('jwt.check', Check::class);

        $this->extendAuthGuard();
    }
}
