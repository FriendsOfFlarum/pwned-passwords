<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Access;

use Flarum\Event\GetPermission;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Contracts\Events\Dispatcher;

class GlobalPolicy
{
    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen(GetPermission::class, [$this, 'configureGlobalPermissions']);
    }

    public function configureGlobalPermissions(GetPermission $event)
    {
        if ((bool) (int) $this->settings->get('fof-pwned-passwords.revokeAdminAccess') && (bool) $event->actor->has_pwned_password && $event->actor->isAdmin()) {
            return false;
        }
    }
}
