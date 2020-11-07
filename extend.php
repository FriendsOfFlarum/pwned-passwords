<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords;

use Flarum\Extend;
use FoF\Components\Extend\AddFofComponents;
use Illuminate\Contracts\Events\Dispatcher;

return [
    new AddFofComponents(),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Middleware('forum'))
        ->add(Middleware\PreventPwnedPassword::class)
        ->add(Middleware\CheckLoginPassword::class)
        ->add(Middleware\CheckPasswordReset::class),

    (new Extend\User()),

    function (Dispatcher $events) {
        $events->subscribe(Access\GlobalPolicy::class);

        $events->subscribe(Listeners\AddUserAttributes::class);
        $events->subscribe(Listeners\UnmarkPassword::class);
        $events->subscribe(Listeners\RevokeAccessWhenPasswordPwned::class);
    },
];
