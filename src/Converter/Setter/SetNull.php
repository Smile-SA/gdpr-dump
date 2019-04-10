<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Setter;

use Smile\Anonymizer\Converter\ConverterInterface;

class SetNull implements ConverterInterface
{
    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return null;
    }
}
