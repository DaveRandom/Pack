<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class Reference implements Type
{
    private $element;
    private $path;

    public function __construct(Type $element, string $path)
    {
        $path = \explode('/', $path);

        if ($path[0] !== '' && $path[0] !== '.' && $path[0] !== '..' && !is_valid_name($path[0])) {
            if (!\ctype_digit($path[0])) {
                throw new \InvalidArgumentException("Invalid path component at index 0: {$path[0]}");
            }

            $path[0] = (int)$path[0];
        }

        for ($i = 1; isset($path[$i]); $i++) {
            if ($path[$i] === '.' || $path[$i] === '..' || is_valid_name($path[$i])) {
                continue;
            }

            if (!\ctype_digit($path[$i])) {
                throw new \InvalidArgumentException("Invalid path component at index {$i}: {$path[$i]}");
            }

            $path[$i] = (int)$path[$i];
        }

        $this->element = $element;
        $this->path = $path;
    }

    private function generatePackCodeForAbsolutePath(PackCompilationContext $ctx, int $count = null)
    {
        $oldPath = $ctx->setCurrentArgPath([]);

        for ($i = 1; isset($this->path[$i]); $i++) {
            $ctx->pushArgDimension($this->path[$i]);
        }

        $this->element->generatePackCode($ctx, $count);

        $ctx->setCurrentArgPath($oldPath);
    }

    public function generatePackCode(PackCompilationContext $ctx, int $count = null)
    {
        if ($this->path[0] === '') { // absolute path
            $this->generatePackCodeForAbsolutePath($ctx, $count);
            return;
        }

        $oldPath = $ctx->getCurrentArgPath();
        $ctx->popArgDimension();

        foreach ($this->path as $component) {
            switch ($component) {
                case '.': {
                    break;
                }
                case '..': {
                    $ctx->popArgDimension();
                    break;
                }
                default: {
                    $ctx->pushArgDimension($component);
                }
            }
        }

        $this->element->generatePackCode($ctx, $count);

        $ctx->setCurrentArgPath($oldPath);
    }
}
