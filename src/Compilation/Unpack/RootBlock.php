<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Unpack;

final class RootBlock extends Block
{
    public function compile(int $indentation, int $increment): string
    {
        $result = parent::compile($indentation, $increment);

        foreach ($this->codeElements as $element) {
            $result .= $element->compile($indentation, $increment);
        }

        return $result;
    }
}
