<?php

namespace Reflar\PwnedPasswords\Listeners;

use Flarum\Event\ConfigureMiddleware;
use Illuminate\Contracts\Events\Dispatcher;
use Reflar\PwnedPasswords\Middleware\Register;

class AddMiddleware
{
    public function subscribe(Dispatcher $events)
    {
        $events->listen(ConfigureMiddleware::class, [$this, 'addMiddleware']);

    }

    public function addMiddleware(ConfigureMiddleware $event)
    {
        $event->pipe(app(Register::class));
    }
}
