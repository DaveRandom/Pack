<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Pack;

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
        $indentation += $increment;

        $result = "{$padding}{$this->header} {\n";

        foreach ($this->codeElements as $element) {
            $result .= $element instanceof AssignmentOperation
                ? $element->getCodeAsAssignment($indentation, $increment, '.=')
                : $element->getCode($indentation, $increment);
        }

        return $result . $padding . \rtrim("} {$this->trailer}") . "\n";
    }
}
