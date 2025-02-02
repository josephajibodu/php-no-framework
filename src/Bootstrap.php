<?php declare(strict_types=1);

namespace JosephAjibodu\PhpNoFramework;

use FastRoute\Dispatcher;
use JosephAjibodu\PhpNoFramework\Exceptions\InternalServerErrorException;
use JosephAjibodu\PhpNoFramework\Exceptions\MethodNotAllowedException;
use JosephAjibodu\PhpNoFramework\Exceptions\NotFoundException;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
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

$request = ServerRequestFactory::fromGlobals();
$response = new Response();

$routeDefinitionCallback = require __DIR__ . '/../config/routes.php';
$dispatcher = \FastRoute\simpleDispatcher($routeDefinitionCallback);

$routeInfo = $dispatcher->dispatch(
    $request->getMethod(),
    $request->getUri()->getPath(),
);

try {
    switch ($routeInfo[0]) {
        case Dispatcher::FOUND:
            $className = $routeInfo[1];
            $handler = new $className($response);
            assert($handler instanceof RequestHandlerInterface);
            foreach ($routeInfo[2] as $attributeName => $attributeValue) {
                $request = $request->withAttribute($attributeName, $attributeValue);
            }
            $response = $handler->handle($request);
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