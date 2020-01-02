<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Events;

use Flarum\User\User;

class PwnedPasswordDetected
{
    /**
     * @var User
     */
    public $user;

    public $type;

    public function __construct(User $user = null, string $type)
    {
        $this->user = $user;
        $this->type = $type;
    }
}
