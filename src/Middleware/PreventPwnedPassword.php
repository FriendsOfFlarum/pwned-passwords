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

use Flarum\Foundation\ErrorHandling\JsonApiFormatter;
use Flarum\Foundation\ErrorHandling\Registry;
use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use FoF\PwnedPasswords\Events\PwnedPasswordDetected;
use FoF\PwnedPasswords\Password;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PreventPwnedPassword implements MiddlewareInterface
{
    /**
     * @var EventDispatcher
     */
    private $events;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(EventDispatcher $events, TranslatorInterface $translator)
    {
        $this->events = $events;
        $this->translator = $translator;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();

        if ($request->getAttribute('routeName') === 'register' && Arr::has($data, 'password') && Password::isPwned($data['password'])) {
            $actor = RequestUtil::getActor($request);
            $this->events->dispatch(new PwnedPasswordDetected($actor, 'registration'));

            return (new JsonApiFormatter())->format(
                resolve(Registry::class)->handle(
                    new ValidationException(['password' => $this->translator->trans('fof-pwned-passwords.error')])
                ),
                $request
            );
        }

        return $handler->handle($request);
    }
}
