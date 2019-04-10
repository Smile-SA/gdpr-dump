<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Anonymize;

use Smile\Anonymizer\Converter\ConverterInterface;

class AnonymizeNumber implements ConverterInterface
{
    /**
     * @var \Closure
     */
    private $callback;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->callback = function () {
            return mt_rand(0, 9);
        };
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        if (!is_string($value) || !is_numeric($value)) {
            return $value;
        }

        return preg_replace_callback('/\w/', $this->callback, $value);
    }
}
