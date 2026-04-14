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

use Flarum\Api\Context;
use Flarum\Api\Resource\UserResource;
use Flarum\Api\Schema;
use Flarum\Extend;
use Flarum\User\Event\PasswordChanged;
use Flarum\User\User;
use FoF\PwnedPasswords\Listeners\ClearPwnedPasswordFlag;
use FoF\PwnedPasswords\Providers\PwnedPasswordServiceProvider;

return [
    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\ServiceProvider())
        ->register(PwnedPasswordServiceProvider::class),

    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Model(User::class))
        ->cast('has_pwned_password', 'bool'),

    (new Extend\Middleware('forum'))
        ->add(Middleware\PreventPwnedPassword::class)
        ->add(Middleware\CheckLoginPassword::class)
        ->add(Middleware\CheckPasswordReset::class),

    (new Extend\Event())
        ->listen(PasswordChanged::class, ClearPwnedPasswordFlag::class),

    (new Extend\Settings())
        ->default('fof-pwned-passwords.learnMoreUrl', 'https://haveibeenpwned.com/Passwords')
        ->serializeToForum('fofPwnedPasswordsLearnMoreUrl', 'fof-pwned-passwords.learnMoreUrl'),

    (new Extend\ApiResource(UserResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('hasPwnedPassword')
                ->visible(fn (User $user, Context $context) => $context->getActor()->id === $user->id || $context->getActor()->isAdmin()),
        ]),

    (new Extend\User())
        ->permissionGroups(Listeners\PwnedPasswordPermissions::class),

    (new Extend\Policy())
        ->globalPolicy(Access\PwnedPasswordPolicy::class),
];
