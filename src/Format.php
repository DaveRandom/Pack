<?php declare(strict_types=1);

namespace DaveRandom\Pack;

final class Format
{
    private $string;
    private $dataSize;
    private $names = [];
    private $extractFunc;

    private $packFunc;
    private $unpackFunc;

    private function compilePackFunc(): \Closure
    {
    }

    private function compileUnpackFunc(): \Closure
    {

    }

    private function getRepeaterSpec(int $quantity, string $name, bool $isLastElement): string
    {
        if ($quantity === 1) {
            return '';
        }

        if ($quantity !== Element::REPEAT) {
            return (string)$quantity;
        }

        if ($isLastElement) {
            return '*';
        }

        throw new \LogicException(
            "Element '{$name}' specified as repeated, but is not the last element"
        );
    }

    private function validateNames(array $names, int $currentIndex)
    {
        foreach ($names as $name => $index) {
            if (isset($this->names[$name])) {
                throw new \LogicException(
                    "Format element name '{$name}' is duplicated: appears at indexes {$this->names[$name]} and {$index}"
                );
            }

            $this->names[$name] = $currentIndex;
        }
    }

    public function __construct(Element ...$elements)
    {
        $elementsByName = [];
        $components = [];
        $dataSize = 0;
        $hasBitField = false;

        foreach ($elements as $i => $element) {
            $code = $element->getCode();
            $name = $element->getName();
            $quantity = $element->getQuantity();
            $hasBitField = $hasBitField || $element instanceof BitField;

            // todo: string formats
            if (!\array_key_exists($code, ELEMENT_SIZES_BYTES)) {
                throw new \LogicException("Unknown unpack code at element '{$name}'");
            }

            $this->validateNames($element instanceof BitField ? $element->getFieldNames() : [$name], $i);

            $components[] = "{$code}{$name}{$this->getRepeaterSpec($quantity, $name, !isset($element[$i + 1]))}";
            $dataSize += ELEMENT_SIZES_BYTES[$code];
            $elementsByName[$name] = $element;
        }

        $this->string = \implode('/', $components);
        $this->dataSize = $dataSize;

        if (!$hasBitField) {
            $this->extractFunc = static function($data) { return $data; };
            return;
        }

        $this->extractFunc = static function($data) use($elementsByName) {
            $result = [];

            foreach ($elementsByName as $element) {
                $result += $element->extract($data);
            }

            return $result;
        };

        $this->packFunc = function($args) {
            return ($this->packFunc = $this->compilePackFunc())($args);
        };
        $this->unpackFunc = function($string, $offset) {
            return ($this->unpackFunc = $this->compileUnpackFunc())($string, $offset);
        };
    }

    public function pack(...$args): string
    {
        return ($this->packFunc)($args);
    }

    public function unpack(string $string, int $offset): array
    {
        return ($this->unpackFunc)($string, $offset);
    }

    public function getString(): string
    {
        return $this->string;
    }

    public function getDataSize(): int
    {
        return $this->dataSize;
    }

    /**
     * @param array $data
     * @return int[]
     */
    public function extract(array $data): array
    {
        return ($this->extractFunc)($data);
    }
}
