<?php declare(strict_types=1);

namespace DaveRandom\Pack\Compilation\Pack;

use DaveRandom\Pack\Compilation\Block;

final class RootBlock extends Block
{
    private $resultVarName;

    public function __construct(string $resultVarName)
    {
        $this->resultVarName = $resultVarName;
    }

    public function getCode(int $indentation, int $increment): string
    {
        $firstElement = $this->codeElements[0];

        if (\count($this->codeElements) === 1 && $firstElement instanceof AssignmentOperation) {
            return $firstElement->getCodeAsReturn($indentation, $increment, false);
        }

        $result = '';
        $padding = \str_repeat(' ', $indentation);
        $assignments = 0;

        // if first element is not an assign op, declare the result var at the top of the function
        if (!$firstElement instanceof AssignmentOperation) {
            $result .= "{$padding}{$this->resultVarName} = '';\n";
            $assignments++;
        }

        // loop all elements except the last and generate code for them
        for ($i = 0, $element = $this->codeElements[$i], $l = \count($this->codeElements) - 1; $i < $l; $element = $this->codeElements[++$i]) {
            $result .= $element instanceof AssignmentOperation
                ? $element->getCodeAsAssignment($indentation, $increment, $assignments++ ? '.=' : '=')
                : $element->getCode($indentation, $increment);
        }

        $lastElement = $this->codeElements[$i];

        if ($lastElement instanceof AssignmentOperation) {
            return $result . $lastElement->getCodeAsReturn($indentation, $increment, true);
        }

        // If the last element was not an assignment, explicitly return the result var
        $result .= $this->codeElements[$i]->getCode($indentation, $increment);
        $result .= "{$padding}return {$this->resultVarName};\n";

        return $result;
    }
}
