<?php declare(strict_types=1);

$builder = new \DI\ContainerBuilder;

$builder->addDefinitions([
    \Psr\Http\Message\ServerRequestInterface::class => fn () => \Laminas\Diactoros\ServerRequestFactory::fromGlobals(),
    \Psr\Http\Message\ResponseInterface::class => fn () => new \Laminas\Diactoros\Response(),
    \FastRoute\Dispatcher::class => fn () => \FastRoute\simpleDispatcher(require __DIR__ . '/routes.php'),
]);

return $builder->build();