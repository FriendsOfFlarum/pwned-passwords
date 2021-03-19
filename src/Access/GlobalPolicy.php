<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) 2019-2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Access;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

class GlobalPolicy extends AbstractPolicy
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function can(User $actor)
    {
        if ((bool) (int) $this->settings->get('fof-pwned-passwords.revokeAdminAccess') && (bool) $actor->has_pwned_password && $actor->isAdmin()) {
            return $this->deny();
        }
    }
}
