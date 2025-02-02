<?php declare(strict_types = 1);

namespace JosephAjibodu\PhpNoFramework\Controllers;

use JosephAjibodu\PhpNoFramework\Template\Renderer;
use Psr\Http\Message\ResponseInterface as Response;

final class HelloController
{
    public function __invoke(
        Response $response, 
        Renderer $renderer,
        string $name = 'Stranger'
        ): Response
    {
        $body = $response->getBody();

        $data = [
            'name' => $name,
        ];

        $html = $renderer->render('hello', $data);

        $body->write($html);
        
        return $response->withBody($body)->withStatus(200);
    }
}