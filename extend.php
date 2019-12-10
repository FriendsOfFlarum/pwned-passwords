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
use Illuminate\Contracts\Events\Dispatcher;
use FoF\Components\Extend\AddFofComponents;

return [
    new AddFofComponents(),
    new Extend\Locales(__DIR__.'/locale'),
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),
    function (Dispatcher $events) {
        $events->subscribe(Listeners\AddMiddleware::class);
        $events->subscribe(Listeners\AddUserAttributes::class);
        $events->subscribe(Listeners\UnmarkPassword::class);
        $events->subscribe(Listeners\RevokeAccessWhenPasswordPwned::class);
    },
];
