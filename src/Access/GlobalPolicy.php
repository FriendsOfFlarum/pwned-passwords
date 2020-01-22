<?php

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
