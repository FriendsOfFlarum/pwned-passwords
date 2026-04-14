<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Providers;

use FoF\PwnedPasswords\HibpClient;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class PwnedPasswordServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HibpClient::class, function (Container $container) {
            return new HibpClient(
                new Guzzle(['timeout' => 10]),
                $container->make(LoggerInterface::class)
            );
        });
    }
}
