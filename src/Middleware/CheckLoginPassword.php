<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Middleware;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Command\RequestPasswordReset;
use Flarum\User\User;
use FoF\PwnedPasswords\Password;
use Illuminate\Contracts\Bus\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CheckLoginPassword implements MiddlewareInterface
{
    public function __construct(Dispatcher $bus, SettingsRepositoryInterface $settings)
    {
        $this->bus = $bus;
        $this->settings = $settings;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($this->settings->get('fof-pwned-passwords.enableLoginCheck')) {
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
        }

        return $response;
    }
}
