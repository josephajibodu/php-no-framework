<?php declare(strict_types = 1);

namespace JosephAjibodu\PhpNoFramework\Controllers;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class AnotherController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $response = (new Response())->withStatus(200);
        $response->getBody()->write('This works too!');
        return $response;
    }
}