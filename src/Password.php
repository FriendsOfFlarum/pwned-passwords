<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords;

use Throwable;

class Password
{
    public static function isPwned(string $password)
    {
        try {
            $sha1 = sha1($password);
            $range = substr($sha1, 0, 5);
            $body = file_get_contents('https://api.pwnedpasswords.com/range/'.$range);

            return (bool) stripos($body, substr($sha1, 5));
        } catch (Throwable $ignored) {
            return false;
        }
    }
}
