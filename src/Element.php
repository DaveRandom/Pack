<?php declare(strict_types=1);

namespace DaveRandom\Pack;

interface Element
{
    const REPEAT = -1;

    function getName(): string;

    function isBuiltIn(): bool;

    function getPackFormat(): string;

    function getUnpackFormat(): string;

    function getPackCode(): string;

    function getUnpackCode(): string;
}
