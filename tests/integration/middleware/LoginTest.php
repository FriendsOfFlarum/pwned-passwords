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

use Flarum\Extend\Csrf;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\PwnedPasswords\HibpClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class LoginTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-pwned-passwords');

        $this->extend(
            (new Csrf())->exemptRoute('login')
        );

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

    private function enableLoginCheck(): void
    {
        $this->app()->getContainer()->make(SettingsRepositoryInterface::class)
            ->set('fof-pwned-passwords.enableLoginCheck', true);
    }

    #[Test]
    public function login_succeeds_with_clean_password(): void
    {
        $this->mockHibpClient(false);
        $this->enableLoginCheck();

        $response = $this->send(
            $this->request('POST', '/login', [
                'json' => [
                    'identification' => 'normal',
                    'password'       => 'too-obscure',
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        // Flag should NOT be set
        $user = User::find(2);
        $this->assertFalse((bool) $user->has_pwned_password);
    }

    #[Test]
    public function login_marks_user_when_password_is_pwned(): void
    {
        $this->mockHibpClient(true);
        $this->enableLoginCheck();

        $response = $this->send(
            $this->request('POST', '/login', [
                'json' => [
                    'identification' => 'normal',
                    'password'       => 'too-obscure',
                ],
            ])
        );

        // Login still succeeds — user is not blocked, just flagged
        $this->assertEquals(200, $response->getStatusCode());

        $user = User::find(2);
        $this->assertTrue((bool) $user->has_pwned_password);
    }

    #[Test]
    public function login_check_is_skipped_when_setting_disabled(): void
    {
        $mock = $this->mockHibpClient(true);

        // enableLoginCheck NOT called — setting defaults to false

        $mock->expects($this->never())->method('isPwned');

        $response = $this->send(
            $this->request('POST', '/login', [
                'json' => [
                    'identification' => 'normal',
                    'password'       => 'too-obscure',
                ],
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function already_flagged_user_is_not_rechecked_on_login(): void
    {
        $mock = $this->mockHibpClient(true);
        $this->enableLoginCheck();

        // Pre-flag the user (after app is booted via mockHibpClient above)
        User::where('id', 2)->update(['has_pwned_password' => true]);

        // Should not call isPwned again since has_pwned_password is already true
        $mock->expects($this->never())->method('isPwned');

        $this->send(
            $this->request('POST', '/login', [
                'json' => [
                    'identification' => 'normal',
                    'password'       => 'too-obscure',
                ],
            ])
        );
    }
}
