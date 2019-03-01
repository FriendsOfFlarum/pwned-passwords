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

use Flarum\User\Command\RequestPasswordReset;
use Flarum\User\Event\CheckingPassword;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Events\Dispatcher;
use Reflar\PwnedPasswords\Password;

class CheckPassword
{
    public function __construct(BusDispatcher $bus)
    {
        $this->bus = $bus;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(CheckingPassword::class, [$this, 'checkPassword']);
    }

    public function checkPassword(CheckingPassword $event)
    {
        $user = $event->user;
        if (Password::isPwned($event->password)) {
            if (!$user->has_pwned_password) {
                $this->bus->dispatch(new RequestPasswordReset($user->email));
            }
            $user->has_pwned_password = true;
        } else {
            $user->has_pwned_password = false;
        }
        $user->save();
    }
}
