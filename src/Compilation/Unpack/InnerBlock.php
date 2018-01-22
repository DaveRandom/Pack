<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Unpack;

final class InnerBlock extends Block
{
    private $header;
    private $trailer;

    public function __construct(string $countVarName, string $header, string $trailer = '')
    {
        parent::__construct($countVarName);

        $this->header = \trim($header);
        $this->trailer = \trim($trailer);
    }

    public function compile(int $indentation, int $increment): string
    {
        $result = parent::compile($indentation, $increment);

        $padding = \str_repeat(' ', $indentation);
        $innerIndentation = $indentation + $increment;

        $result .= "{$padding}{$this->header} {\n";

        foreach ($this->codeElements as $element) {
            $result .= $element->compile($innerIndentation, $increment);
        }

        return $result . "{$padding}}" . \rtrim(' ' . $this->trailer) . "\n";
    }
}
