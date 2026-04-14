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

use Carbon\Carbon;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\PasswordToken;
use Flarum\User\User;
use FoF\PwnedPasswords\HibpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class PasswordResetTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-pwned-passwords');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
            ],
        ]);
    }

    private function mockHibpClient(bool $isPwned): HibpClient&MockObject
    {
        $mock = $this->createMock(HibpClient::class);
        $mock->method('isPwned')->willReturn($isPwned);

        $this->app()->getContainer()->instance(HibpClient::class, $mock);

        return $mock;
    }

    private function createPasswordToken(int $userId): PasswordToken
    {
        $token = PasswordToken::generate($userId);
        $token->created_at = Carbon::now();
        $token->save();

        return $token;
    }

    #[Test]
    public function password_reset_is_blocked_when_new_password_is_pwned(): void
    {
        $this->mockHibpClient(true);

        $token = $this->createPasswordToken(2);

        $response = $this->send(
            $this->request('POST', '/savePassword', [
                'json' => [
                    'passwordToken' => $token->token,
                    'password' => 'pwned-password',
                    'password_confirmation' => 'pwned-password',
                ],
            ])
        );

        // Should redirect back to the reset form with an error
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('resetPassword', $response->getHeaderLine('Location'));
    }

    #[Test]
    public function password_reset_succeeds_when_new_password_is_clean(): void
    {
        $this->mockHibpClient(false);

        $token = $this->createPasswordToken(2);

        $response = $this->send(
            $this->request('POST', '/savePassword', [
                'json' => [
                    'passwordToken' => $token->token,
                    'password' => 'clean-unique-password-99!',
                    'password_confirmation' => 'clean-unique-password-99!',
                ],
            ])
        );

        $this->assertNotEquals(302, $response->getStatusCode());

        // Flag should be cleared
        $user = User::find(2);
        $this->assertFalse((bool) $user->has_pwned_password);
    }
}
