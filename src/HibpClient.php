<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords;

use Flarum\Foundation\Application;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;

class HibpClient
{
    public function __construct(
        private Guzzle $client,
        private LoggerInterface $logger
    ) {
    }

    public function isPwned(string $password): bool
    {
        $sha1 = sha1($password);
        $prefix = substr($sha1, 0, 5);
        $suffix = strtoupper(substr($sha1, 5));

        try {
            $response = $this->client->get('https://api.pwnedpasswords.com/range/'.$prefix, [
                'headers' => [
                    'Add-Padding' => 'true',
                    'User-Agent'  => 'fof/pwned-passwords (Flarum/'.Application::VERSION.')',
                ],
            ]);
            $body = (string) $response->getBody();
        } catch (GuzzleException $e) {
            $this->logger->warning('fof/pwned-passwords: API request failed: '.$e->getMessage());

            return false;
        }

        foreach (explode("\n", $body) as $line) {
            [$lineSuffix, $count] = explode(':', trim($line), 2) + [1 => '0'];

            if ((int) $count > 0 && strncasecmp($lineSuffix, $suffix, 35) === 0) {
                return true;
            }
        }

        return false;
    }
}
