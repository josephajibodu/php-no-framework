<?php declare(strict_types = 1);

namespace JosephAjibodu\PhpNoFramework\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class HelloController implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $name = $request->getAttribute('name', 'Stranger');
        $response = (new \Laminas\Diactoros\Response)->withStatus(200);
        $response->getBody()->write('Hello ' . $name . '!');
        return $response;
    }
}