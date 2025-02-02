<?php declare(strict_types = 1);

namespace JosephAjibodu\PhpNoFramework\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;

final class HelloController implements RequestHandlerInterface
{
    public function __construct(
        private Response $response
    ) {}
    
    public function handle(Request $request): Response
    {
        $name = $request->getAttribute('name', 'Stranger');
        $body = $this->response->getBody();

        $body->write('Hello ' . $name . '!');
        
        return $this->response
            ->withBody($body)
            ->withStatus(200);
    }
}