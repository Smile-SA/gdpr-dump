<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Anonymizer;

use Smile\Anonymizer\Converter\ConverterInterface;

class AnonymizeText implements ConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $isFirstCharacter = true;

        foreach (str_split((string) $value) as $index => $char) {
            if ($char === ' ' || $char === '_' || $char === '.') {
                $isFirstCharacter = true;
                continue;
            }

            if ($isFirstCharacter) {
                $isFirstCharacter = false;
                continue;
            }

            $value[$index] = '*';
        }

        return $value;
    }
}
