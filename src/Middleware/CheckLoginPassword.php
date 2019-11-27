<?php

/*
 * This file is part of reflar/pwned-passwords.
 *
 * Copyright (c) 2019 ReFlar.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Reflar\PwnedPasswords\Middleware;

use Flarum\User\Command\RequestPasswordReset;
use Flarum\User\User;
use Illuminate\Contracts\Bus\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reflar\PwnedPasswords\Password;

class CheckLoginPassword implements MiddlewareInterface
{
    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        $data = $request->getParsedBody();
        $path = $request->getUri()->getPath();

        if ('POST' === $request->getMethod() && '/login' === $path) {
            $session = $request->getAttribute('session');
            $actor = User::find($session->get('user_id'));

            if ($actor && Password::isPwned($data['password'])) {
                if (!$actor->has_pwned_password) {
                    $this->bus->dispatch(new RequestPasswordReset($actor->email));
                    $actor->has_pwned_password = true;
                    $actor->save();
                }
            }
        }

        return $response;
    }
}
