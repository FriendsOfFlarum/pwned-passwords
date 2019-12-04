<?php

/*
 * This file is part of reflar/pwned-passwords.
 *
 * Copyright (c) 2019 ReFlar.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Listeners;

use Flarum\Event\ConfigureMiddleware;
use FoF\PwnedPasswords\Middleware\CheckLoginPassword;
use FoF\PwnedPasswords\Middleware\CheckPasswordReset;
use FoF\PwnedPasswords\Middleware\PreventPwnedPassword;
use Illuminate\Contracts\Events\Dispatcher;

class AddMiddleware
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(ConfigureMiddleware::class, [$this, 'addMiddleware']);
    }

    public function addMiddleware(ConfigureMiddleware $event)
    {
        $event->pipe(app(PreventPwnedPassword::class));
        $event->pipe(app(CheckLoginPassword::class));
        $event->pipe(app(CheckPasswordReset::class));
    }
}
