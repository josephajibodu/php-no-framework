<?php declare(strict_types=1);

namespace JosephAjibodu\PhpNoFramework;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequestFactory;
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

switch ($routeInfo[0]) {
    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $response = (new \Laminas\Diactoros\Response)->withStatus(405);
        $response->getBody()->write('Method not allowed');
        $response = $response->withStatus(405);
        break;
    case \FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        foreach ($routeInfo[2] as $attributeName => $attributeValue) {
            $request = $request->withAttribute($attributeName, $attributeValue);
        }
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = call_user_func($handler, $request);
        break;
    case \FastRoute\Dispatcher::NOT_FOUND:
    default:
        $response = (new \Laminas\Diactoros\Response)->withStatus(404);
        $response->getBody()->write('Not Found!');
        break;
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