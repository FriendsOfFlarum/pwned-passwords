<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Middleware;

use Flarum\Http\UrlGenerator;
use Flarum\User\PasswordToken;
use FoF\PwnedPasswords\Events\PwnedPasswordDetected;
use FoF\PwnedPasswords\Password;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\MessageBag;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CheckPasswordReset implements MiddlewareInterface
{
    /**
     * @var UrlGenerator
     */
    private $url;

    /**
     * @var EventDispatcher
     */
    private $events;

    public function __construct(UrlGenerator $url, EventDispatcher $events)
    {
        $this->url = $url;
        $this->events = $events;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getAttribute('routeName') === 'savePassword') {
            $data = $request->getParsedBody();
            $token = PasswordToken::findOrFail(Arr::get($data, 'passwordToken'));

            if (Arr::has($data, 'password') && Password::isPwned($data['password'])) {
                $translator = resolve('translator');
                $request->getAttribute('session')->put('errors', new MessageBag([$translator->trans('fof-pwned-passwords.error')]));
                $this->events->dispatch(new PwnedPasswordDetected($token->user, 'passwordReset'));

                return new RedirectResponse($this->url->to('forum')->route('resetPassword', ['token' => $token->token]));
            }
        }

        return $handler->handle($request);
    }
}
