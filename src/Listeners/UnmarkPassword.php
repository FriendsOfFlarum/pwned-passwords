<?php

/*
 * This file is part of reflar/pwned-passwords.
 *
 * Copyright (c) 2019 ReFlar.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Reflar\PwnedPasswords\Listeners;

use Flarum\User\Event\PasswordChanged;
use Illuminate\Events\Dispatcher;

class UnmarkPassword
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(PasswordChanged::class, [$this, 'unmarkPassword']);
    }

    public function unmarkPassword(PasswordChanged $event)
    {
        $user = $event->user;
        $user->has_pwned_password = false;
        $user->save();
    }
}