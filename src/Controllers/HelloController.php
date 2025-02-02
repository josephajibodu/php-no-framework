<?php declare(strict_types = 1);

namespace JosephAjibodu\PhpNoFramework\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class HelloController
{
    public function __invoke(Response $response, string $name = 'Stranger'): Response
    {
        $body = $response->getBody();

        $body->write('Hello ' . $name . '!');
        
        return $response->withBody($body)->withStatus(200);
    }
}