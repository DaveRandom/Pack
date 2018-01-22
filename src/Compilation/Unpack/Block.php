<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Unpack;

use DaveRandom\Pack\Compilation\Statement;

abstract class Block extends \DaveRandom\Pack\Compilation\Block
{
    private $compiled = false;

    protected $countVarName;
    protected $size = 0;

    public function __construct(string $countVarName)
    {
        $this->countVarName = $countVarName;
    }

    final public function addSize(int $size)
    {
        $this->size += $size;
    }

    public function compile(int $indentation, int $increment): string
    {
        if (!$this->compiled) {
            $this->compiled = true;

            if ($this->size > 0) {
                $this->appendCodeElements(new Statement("{$this->countVarName} += {$this->size};"));
            }
        }

        return '';
    }
}
