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

use Flarum\Group\Group;
use Flarum\User\User;
use FoF\PwnedPasswords\Listeners\PwnedPasswordPermissions;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PwnedPasswordPermissionsTest extends TestCase
{
    private PwnedPasswordPermissions $listener;

    protected function setUp(): void
    {
        parent::setUp();
        $this->listener = new PwnedPasswordPermissions();
    }

    private function makeUser(mixed $hasPwnedPassword): User
    {
        $user = new class() extends User {
        };
        $user->has_pwned_password = $hasPwnedPassword;

        return $user;
    }

    #[Test]
    public function it_reduces_groups_to_guest_when_password_is_pwned(): void
    {
        $result = ($this->listener)($this->makeUser(true), [1, 3, 4]);

        $this->assertEquals([Group::GUEST_ID], $result);
    }

    #[Test]
    public function it_leaves_groups_unchanged_when_password_is_not_pwned(): void
    {
        $originalGroups = [1, 3, 4];
        $result = ($this->listener)($this->makeUser(false), $originalGroups);

        $this->assertEquals($originalGroups, $result);
    }

    #[Test]
    public function it_leaves_groups_unchanged_when_flag_is_null(): void
    {
        $originalGroups = [1, 3];
        $result = ($this->listener)($this->makeUser(null), $originalGroups);

        $this->assertEquals($originalGroups, $result);
    }
}
