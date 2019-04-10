<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Proxy;

use Smile\Anonymizer\Converter\ConverterInterface;

class Unique implements ConverterInterface
{
    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var int
     */
    private $maxRetries;

    /**
     * @var array
     */
    private $generated = [];

    /**
     * @param array $parameters
     * @throws \InvalidArgumentException
     */
    public function __construct(array $parameters = [])
    {
        if (!isset($parameters['converter'])) {
            throw new \InvalidArgumentException('The unique converter requires a "converter" parameter.');
        }

        if ($parameters['converter'] instanceof Optional) {
            throw new \InvalidArgumentException('The optional converter cannot be chained after the unique converter.');
        }

        $this->converter = $parameters['converter'];
        $this->maxRetries = (int) ($parameters['maxRetries'] ?? 100);
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $i = 0;

        do {
            $result = $this->converter->convert($value, $context);

            // Ignore null values
            if ($result === null) {
                return null;
            }

            $i++;
            if ($i > $this->maxRetries) {
                throw new \OverflowException(
                    sprintf('Maximum retries of %d reached without finding a unique value', $this->maxRetries)
                );
            }
        } while (array_key_exists(serialize($result), $this->generated));

        $this->generated[serialize($result)] = null;

        return $result;
    }

    /**
     * Reset the cache that stores the generated values.
     *
     * @return $this
     */
    public function reset()
    {
        $this->generated = [];

        return $this;
    }
}
