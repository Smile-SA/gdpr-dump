<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

class AnonymizeNumber extends AnonymizeText
{
    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $isFirstCharacter = true;

        foreach (str_split((string) $value) as $index => $char) {
            if (!is_numeric($char)) {
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
