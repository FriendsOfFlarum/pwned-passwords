<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) 2019 - 2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\Extend;
use Flarum\User\Event\PasswordChanged;
use Flarum\User\User;
use FoF\PwnedPasswords\Listeners\UnmarkPassword;

return [
    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Middleware('forum'))
        ->add(Middleware\PreventPwnedPassword::class)
        ->add(Middleware\CheckLoginPassword::class)
        ->add(Middleware\CheckPasswordReset::class),

    (new Extend\Event())
        ->listen(PasswordChanged::class, UnmarkPassword::class),

    (new Extend\ApiSerializer(UserSerializer::class))
        ->attribute('hasPwnedPassword', function (UserSerializer $serializer, User $user) {
            return $user->has_pwned_password;
        }),

    (new Extend\User())
        ->permissionGroups(Listeners\RevokeAccessWhenPasswordPwned::class),

    (new Extend\Policy())
        ->globalPolicy(Access\GlobalPolicy::class),
];
