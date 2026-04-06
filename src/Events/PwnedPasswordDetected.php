<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Events;

use Flarum\User\User;

class PwnedPasswordDetected
{
    public function __construct(public User $user, public string $type)
    {
    }
}
