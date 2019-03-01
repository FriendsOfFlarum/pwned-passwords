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

use Flarum\Api\JsonApiResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Reflar\PwnedPasswords\Password;
use Tobscure\JsonApi\Document;
use Tobscure\JsonApi\Exception\Handler\ResponseBag;
use Zend\Diactoros\Uri;

class PreventPwnedPassword implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $data = $request->getParsedBody();
        $registerUri = new Uri(app()->url('/register'));
        $resetUri = new Uri(app()->url('/reset'));
        $path = $request->getUri()->getPath();

        if ($request->getMethod() === 'POST') {
            if ($path === $registerUri->getPath() || $path === $resetUri->getPath()) {
                if (Password::isPwned($data['password'])) {
                    $translator = app('translator');
                    $error = new ResponseBag('422', [
                        [
                            'status' => '422',
                            'code' => 'validation_error',
                            'source' => [
                                'pointer' => '/data/attributes/password',
                            ],
                            'detail' => $translator->trans('reflar-pwned-passwords.error'),
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
