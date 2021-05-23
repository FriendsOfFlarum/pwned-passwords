<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) 2019 - 2021 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Middleware;

use Flarum\Http\AccessToken;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Command\RequestPasswordReset;
use Flarum\User\User;
use FoF\PwnedPasswords\Events\PwnedPasswordDetected;
use FoF\PwnedPasswords\Password;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CheckLoginPassword implements MiddlewareInterface
{
    /**
     * @var Dispatcher
     */
    private $bus;

    /**
     * @var SettingsRepositoryInterface
     */
    private $settings;

    /**
     * @var EventDispatcher
     */
    private $events;

    public function __construct(Dispatcher $bus, SettingsRepositoryInterface $settings, EventDispatcher $events)
    {
        $this->bus = $bus;
        $this->settings = $settings;
        $this->events = $events;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ((bool) (int) $this->settings->get('fof-pwned-passwords.enableLoginCheck')) {
            $data = $request->getParsedBody();
            $path = $request->getUri()->getPath();

            if ('POST' === $request->getMethod() && Str::endsWith($path, '/login') ) {
                $token = Arr::get($response->getPayload(), 'token');
                $user_id = AccessToken::where('token', $token)->get()->pluck('user_id');
                $actor = User::find($user_id)->first();

                if ($actor && Arr::has($data, 'password') && Password::isPwned($data['password']) && !$actor->has_pwned_password) {
                    $this->bus->dispatch(new RequestPasswordReset($actor->email));
                    $actor->has_pwned_password = true;
                    $actor->save();
                    $this->events->dispatch(new PwnedPasswordDetected($actor, 'login'));
                }
            }
        }

        return $response;
    }
}
