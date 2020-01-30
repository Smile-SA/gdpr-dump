<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use InvalidArgumentException;
use OverflowException;
use Smile\GdprDump\Converter\ConverterInterface;

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
     * @throws InvalidArgumentException
     */
    public function __construct(array $parameters)
    {
        if (!isset($parameters['converter'])) {
            throw new InvalidArgumentException('The parameter "converter" is required.');
        }

        $this->converter = $parameters['converter'];
        $this->maxRetries = (int) ($parameters['maxRetries'] ?? 100);
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $count = 0;

        do {
            $result = $this->converter->convert($value, $context);

            // Ignore null values
            if ($result === null) {
                return null;
            }

            $count++;
            if ($count > $this->maxRetries) {
                throw new OverflowException(
                    sprintf('Maximum retries of %d reached without finding a unique value.', $this->maxRetries)
                );
            }

            $key = serialize($result);
        } while (array_key_exists($key, $this->generated));

        $this->generated[$key] = null;

        return $result;
    }
}
