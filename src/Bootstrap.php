<?php declare(strict_types=1);

namespace JosephAjibodu\PhpNoFramework;

use FastRoute\Dispatcher;
use Invoker\InvokerInterface;
use JosephAjibodu\PhpNoFramework\Exceptions\InternalServerErrorException;
use JosephAjibodu\PhpNoFramework\Exceptions\MethodNotAllowedException;
use JosephAjibodu\PhpNoFramework\Exceptions\NotFoundException;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

require __DIR__ . '/../vendor/autoload.php';

$environment = getenv('ENVIRONMENT') ?: 'dev';

error_reporting(E_ALL);

$whoops = new Run;
if ($environment === 'dev') {
    $whoops->pushHandler(new PrettyPageHandler);
} else {
    $whoops->pushHandler(function (Throwable $e) {
        error_log("Error: ". $e->getMessage(), 0);
        echo 'Friendly error page and send an email to the developer';
    });
}
$whoops->register();

$container = require __DIR__ . '/../config/container.php';
assert($container instanceof \Psr\Container\ContainerInterface);

$request = $container->get(\Psr\Http\Message\ServerRequestInterface::class);
assert($request instanceof \Psr\Http\Message\ServerRequestInterface);

$dispatcher = $container->get(Dispatcher::class);
assert($dispatcher instanceof Dispatcher);

$routeDefinitionCallback = require __DIR__ . '/../config/routes.php';
$dispatcher = \FastRoute\simpleDispatcher($routeDefinitionCallback);

$routeInfo = $dispatcher->dispatch(
    $request->getMethod(),
    $request->getUri()->getPath(),
);

try {
    switch ($routeInfo[0]) {
        case Dispatcher::FOUND:
            $handler = $routeInfo[1];
            $args = $routeInfo[2] ?? [];
            foreach ($routeInfo[2] as $attributeName => $attributeValue) {
                $request = $request->withAttribute($attributeName, $attributeValue);
            }
            $args['request'] = $request;
            
            $invoker = $container->get(InvokerInterface::class);
            assert($invoker instanceof InvokerInterface);

            $response = $invoker->call($handler, $args);
            assert($response instanceof ResponseInterface);
            
            break;
        case Dispatcher::METHOD_NOT_ALLOWED:
            throw new MethodNotAllowedException;

        case Dispatcher::NOT_FOUND:
        default:
            throw new NotFoundException;
    }
} catch (MethodNotAllowedException) {
    $response = (new Response)->withStatus(405);
    $response->getBody()->write('Not Allowed');
} catch (NotFoundException) {
    $response = (new Response)->withStatus(404);
    $response->getBody()->write('Not Found');
} catch (Throwable $t) {
    throw new InternalServerErrorException($t->getMessage(), $t->getCode(), $t);
}

foreach ($response->getHeaders() as $name => $values) {
    $first = strtolower($name) !== 'set-cookie';
    foreach ($values as $value) {
        $header = sprintf('%s: %s', $name, $value);
        header($header, $first);
        $first = false;
    }
}

$statusLine = sprintf(
    'HTTP/%s %s %s',
    $response->getProtocolVersion(),
    $response->getStatusCode(),
    $response->getReasonPhrase()
);
header($statusLine, true, $response->getStatusCode());

echo $response->getBody();