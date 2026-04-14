<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Tests\unit\Listeners;

use Flarum\User\Event\PasswordChanged;
use Flarum\User\User;
use FoF\PwnedPasswords\Listeners\ClearPwnedPasswordFlag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClearPwnedPasswordFlagTest extends TestCase
{
    private ClearPwnedPasswordFlag $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new ClearPwnedPasswordFlag();
    }

    #[Test]
    public function it_clears_the_flag_and_saves_when_password_changes(): void
    {
        $saved = false;

        $user = new class() extends User {
            public bool $has_pwned_password = true;
            public bool $savedCalled = false;

            public function save(array $options = []): bool
            {
                $this->savedCalled = true;

                return true;
            }
        };

        $event = new PasswordChanged($user);

        $this->listener->handle($event);

        $this->assertFalse($user->has_pwned_password);
        $this->assertTrue($user->savedCalled);
    }
}
