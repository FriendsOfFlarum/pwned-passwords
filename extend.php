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

use Illuminate\Contracts\Events\Dispatcher;

return [
    function (Dispatcher $events) {
        $events->subscribe(Listeners\AddMiddleware::class);
    },
];
