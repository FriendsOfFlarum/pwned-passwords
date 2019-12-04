<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords;

use GuzzleHttp\Client as Guzzle;

class Password
{
    public static function isPwned(string $password)
    {
        $client = new Guzzle();
        $sha1 = sha1($password);
        $range = substr($sha1, 0, 5);
        $response = $client->request('GET', 'https://api.pwnedpasswords.com/range/'.$range);
        $body = $response->getBody();
        $list = explode("\n", $body);

        foreach ($list as $line) {
            $hash = strtolower(strtok($line, ':'));

            if ($range.$hash === $sha1) {
                return true;
            }
        }

        return false;
    }
}
