<?php

/*
 * This file is part of reflar/pwned-passwords.
 *
 * Copyright (c) 2019 ReFlar.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Reflar\PwnedPasswords;

use Flarum\Extend;
use Illuminate\Contracts\Events\Dispatcher;

return [
    new Extend\Locales(__DIR__ . '/locale'),
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js'),
    function (Dispatcher $events) {
        $events->subscribe(Listeners\AddMiddleware::class);
        $events->subscribe(Listeners\AddUserAttributes::class);
        $events->subscribe(Listeners\UnmarkPassword::class);
        $events->subscribe(Listeners\CheckPassword::class);
    },
];
