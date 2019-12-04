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

use Flarum\Api\JsonApiResponse;
use FoF\PwnedPasswords\Password;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\Exception\Handler\ResponseBag;
use Zend\Diactoros\Uri;

class PreventPwnedPassword implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $uri = new Uri(app()->url('/register'));
        $path = $request->getUri()->getPath();

        if ('POST' === $request->getMethod()) {
            if ($path === $uri->getPath()) {
                if (Password::isPwned($data['password'])) {
                    $translator = app('translator');
                    $error = new ResponseBag('422', [
                        [
                            'status' => '422',
                            'code'   => 'validation_error',
                            'source' => [
                                'pointer' => '/data/attributes/password',
                            ],
                            'detail' => $translator->trans('fof-pwned-passwords.error'),
                        ],
                    ]);
                    $document = new Document();
                    $document->setErrors($error->getErrors());

                    return new JsonApiResponse($document, $error->getStatus());
                }
            }
        }

        return $handler->handle($request);
    }
}
