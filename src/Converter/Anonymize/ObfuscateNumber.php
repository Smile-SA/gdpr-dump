<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Anonymize;

use Smile\Anonymizer\Converter\ConverterInterface;

class ObfuscateNumber implements ConverterInterface
{
    /**
     * @var \Closure
     */
    private $replaceCallback;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->replaceCallback = function () {
            return mt_rand(0, 9);
        };
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return preg_replace_callback('/[0-9]/', $this->replaceCallback, $value);
    }
}
