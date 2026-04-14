<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Access;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class PwnedPasswordPolicy extends AbstractPolicy
{
    public function __construct(protected SettingsRepositoryInterface $settings)
    {
    }

    public function can(User $actor): ?string
    {
        if ($actor->has_pwned_password && $this->settings->get('fof-pwned-passwords.revokeAdminAccess') && $actor->isAdmin()) {
            return $this->forceDeny();
        }

        return null;
    }
}
