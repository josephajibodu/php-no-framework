<?php declare(strict_types=1);

namespace JosephAjibodu\PhpNoFramework\Template;

use Mustache_Engine;

final class MustacheRenderer implements Renderer
{
    public function __construct(private Mustache_Engine $engine){}

    public function render(string $template, array $data = []): string
    {
        return $this->engine->render($template, $data);
    }
}