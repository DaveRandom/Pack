<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Unpack;

use DaveRandom\Pack\Compilation\Block;

final class RootBlock extends Block
{
    public function getCode(int $indentation, int $increment): string
    {
        $result = '';

        foreach ($this->codeElements as $element) {
            $result .= $element->getCode($indentation, $increment);
        }

        return $result;
    }
}
