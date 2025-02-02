<?php declare(strict_types = 1);

use JosephAjibodu\PhpNoFramework\Controllers\AnotherController;
use JosephAjibodu\PhpNoFramework\Controllers\HelloController;

return function(\FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/hello[/{name}]', HelloController::class);
    $r->addRoute('GET', '/another-route', AnotherController::class);
};