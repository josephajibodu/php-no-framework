<?php declare(strict_types=1);

use FastRoute\Dispatcher;
use JosephAjibodu\PhpNoFramework\Template\MustacheRenderer;
use JosephAjibodu\PhpNoFramework\Template\Renderer;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$builder = new \DI\ContainerBuilder;

$builder->addDefinitions([
    ServerRequestInterface::class => fn () => ServerRequestFactory::fromGlobals(),
    ResponseInterface::class => fn () => new Response(),
    Dispatcher::class => fn () => \FastRoute\simpleDispatcher(require __DIR__ . '/routes.php'),
    Renderer::class => function (Mustache_Engine $mustache) {
        return new MustacheRenderer($mustache);
    },
    Mustache_Loader_FilesystemLoader::class => fn () => new Mustache_Loader_FilesystemLoader(__DIR__ . '/../views', ['extension' => '.html']),
    Mustache_Engine::class => fn (Mustache_Loader_FilesystemLoader $loader) => new Mustache_Engine([
        'loader' => $loader
    ]),
]);

return $builder->build();