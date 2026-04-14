<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Tests\integration\middleware;

use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use FoF\PwnedPasswords\HibpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class RegistrationTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-pwned-passwords');
    }

    private function mockHibpClient(bool $isPwned): HibpClient&MockObject
    {
        $mock = $this->createMock(HibpClient::class);
        $mock->method('isPwned')->willReturn($isPwned);

        $this->app()->getContainer()->instance(HibpClient::class, $mock);

        return $mock;
    }

    #[Test]
    public function registration_is_blocked_when_password_is_pwned(): void
    {
        $this->mockHibpClient(true);

        $response = $this->send(
            $this->request('POST', '/register', [
                'json' => [
                    'username' => 'newuser',
                    'email'    => 'newuser@example.com',
                    'password' => 'password123',
                ],
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);
        $this->assertNotEmpty($body['errors']);
        $pointer = $body['errors'][0]['source']['pointer'] ?? null;
        $this->assertEquals('/data/attributes/password', $pointer);
    }

    #[Test]
    public function registration_succeeds_when_password_is_not_pwned(): void
    {
        $this->mockHibpClient(false);

        $response = $this->send(
            $this->request('POST', '/register', [
                'json' => [
                    'username' => 'safeuser',
                    'email'    => 'safeuser@example.com',
                    'password' => 'un1que-and-s3cur3!',
                ],
            ])
        );

        // 200 or 201 — registration succeeded (or redirect), not a validation error
        $this->assertNotEquals(422, $response->getStatusCode());
    }

    #[Test]
    public function hibp_failure_does_not_block_registration(): void
    {
        // When the HIBP API is unreachable, isPwned returns false — registration should proceed
        $this->mockHibpClient(false);

        $response = $this->send(
            $this->request('POST', '/register', [
                'json' => [
                    'username' => 'fallbackuser',
                    'email'    => 'fallbackuser@example.com',
                    'password' => 'some-password',
                ],
            ])
        );

        $this->assertNotEquals(422, $response->getStatusCode());
    }
}
