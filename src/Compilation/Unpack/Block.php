<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Unpack;

interface Block extends CodeElement
{
    function appendCodeElements(CodeElement ...$elements);
}
