<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Anonymize;

use Smile\Anonymizer\Converter\ConverterInterface;

class ObfuscateText implements ConverterInterface
{
    /**
     * @var string
     */
    private $replacements = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /**
     * @var int
     */
    private $replacementsCount;

    /**
     * @var \Closure
     */
    private $replaceCallback;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        if (isset($parameters['replacements'])) {
            $this->replacements = $parameters['replacements'];
        }

        $this->replacementsCount = strlen($this->replacements);

        $this->replaceCallback = function () {
            $index = mt_rand(0, $this->replacementsCount - 1);
            return $this->replacements[$index];
        };
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        return preg_replace_callback('/\w/', $this->replaceCallback, $value);
    }
}
