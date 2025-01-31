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
$response->getBody()->write('Hello, World! ');
$response->getBody()->write('The URI is: ' . $request->getUri()->getPath());

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