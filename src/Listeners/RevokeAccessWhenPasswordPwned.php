<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) 2019-2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Listeners;

use Flarum\Group\Group;
use Flarum\User\User;

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
