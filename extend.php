<?php

namespace Reflar\PwnedPasswords;

use Illuminate\Contracts\Events\Dispatcher;

return [
    function (Dispatcher $events) {
        $events->subscribe(Listeners\AddMiddleware::class);
    },
];
