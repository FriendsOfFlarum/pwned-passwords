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

use Flarum\Foundation\Application;
use GuzzleHttp\Client as Guzzle;
use Throwable;

class Password
{
    public static function isPwned(string $password)
    {
        try {
            $client = new Guzzle(['verify' => !resolve(Application::class)->inDebugMode()]);
            $sha1 = sha1($password);
            $range = substr($sha1, 0, 5);
            $response = $client->request('GET', 'https://api.pwnedpasswords.com/range/'.$range);
            $body = $response->getBody();

            return (bool) stripos($body, substr($sha1, 5));
        } catch (Throwable $ignored) {
            return false;
        }
    }
}
