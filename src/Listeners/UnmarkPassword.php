<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Listeners;

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
