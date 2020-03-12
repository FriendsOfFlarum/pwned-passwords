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

use Flarum\Foundation\ErrorHandling\JsonApiFormatter;
use Flarum\Foundation\ErrorHandling\Registry;
use Flarum\Foundation\ValidationException;
use FoF\PwnedPasswords\Events\PwnedPasswordDetected;
use FoF\PwnedPasswords\Password;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PreventPwnedPassword implements MiddlewareInterface
{
    /**
     * @var EventDispatcher
     */
    private $events;

    public function __construct(EventDispatcher $events)
    {
        $this->events = $events;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $uri = new Uri(app()->url('/register'));
        $path = $request->getUri()->getPath();

        if ($request->getMethod() === 'POST' && $uri->getPath() === $path && Arr::has($data, 'password') && Password::isPwned($data['password'])) {
            $actor = $request->getAttribute('actor');
            $this->events->dispatch(new PwnedPasswordDetected($actor, 'registration'));

            return (new JsonApiFormatter())->format(
                app(Registry::class)->handle(
                    new ValidationException(['password' => app('translator')->trans('fof-pwned-passwords.error')])
                ),
                $request
            );
        }

        return $handler->handle($request);
    }
}
