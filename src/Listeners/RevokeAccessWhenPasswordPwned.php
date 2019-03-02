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

use Carbon\Carbon;
use Flarum\Event\PrepareUserGroups;
use Flarum\Group\Group;
use Illuminate\Contracts\Events\Dispatcher;

class RevokeAccessWhenPasswordPwned
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(PrepareUserGroups::class, [$this, 'prepareUserGroups']);
    }

    public function prepareUserGroups(PrepareUserGroups $event)
    {
        if ($event->user->has_pwned_password) {
            $event->groupIds = [Group::GUEST_ID];
        }
    }
}