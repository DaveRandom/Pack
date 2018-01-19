<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Unpack;

use DaveRandom\Pack\Compilation\Block;

final class InnerBlock extends Block
{
    private $header;
    private $trailer;

    public function __construct(string $header, string $trailer = '')
    {
        $this->header = \trim($header);
        $this->trailer = \trim($trailer);
    }

    public function getCode(int $indentation, int $increment): string
    {
        $padding = \str_repeat(' ', $indentation);
        $innerIndentation = $indentation + $increment;

        $result = "{$padding}{$this->header} {\n";

        foreach ($this->codeElements as $element) {
            $result .= $element->getCode($innerIndentation, $increment);
        }

        return $result . "{$padding}}" . \rtrim(' ' . $this->trailer) . "\n";
    }
}
