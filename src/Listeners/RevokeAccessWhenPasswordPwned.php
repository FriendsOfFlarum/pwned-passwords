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

use Flarum\Event\PrepareUserGroups;
use Flarum\Group\Group;
use Flarum\User\User;
use Illuminate\Contracts\Events\Dispatcher;

class RevokeAccessWhenPasswordPwned
{
    public function __invoke(User $user, array $groupIds): array
    {
        if ($user->has_pwned_password) {
            $groupIds = [Group::GUEST_ID];
        }

        return $groupIds;
    }
}
