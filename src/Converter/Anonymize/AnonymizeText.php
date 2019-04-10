<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Anonymize;

use Smile\Anonymizer\Converter\ConverterInterface;

class AnonymizeText implements ConverterInterface
{
    /**
     * Replace word characters by asterisks.
     */
    const METHOD_OBFUSCATE = 'obfuscate';

    /**
     * Replace word characters by random characters.
     */
    const METHOD_REPLACE = 'replace';

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $replaceCharacters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    /**
     * @var \Closure
     */
    private $replaceCallback;

    /**
     * @param array $parameters
     * @throws \UnexpectedValueException
     */
    public function __construct(array $parameters = [])
    {
        $this->method = $parameters['method'] ?? 'obfuscate';

        if (!in_array($this->method, [self::METHOD_OBFUSCATE, self::METHOD_REPLACE], true)) {
            throw new \UnexpectedValueException(sprintf('Invalid anonymization method "%s".', $this->method));
        }

        if ($this->method === self::METHOD_REPLACE) {
            $this->replaceCallback = function () {
                $index = mt_rand(0, 61);
                return $this->replaceCharacters[$index];
            };
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        if (!is_string($value)) {
            return $value;
        }

        if ($this->method === self::METHOD_REPLACE) {
            return preg_replace_callback('/\w/', $this->replaceCallback, $value);
        }

        return $this->obfuscate($value);
    }

    /**
     * Obfuscate a value.
     *
     * @param string $value
     * @return string
     */
    private function obfuscate(string $value): string
    {
        $isFirstCharacter = true;

        foreach (str_split($value) as $index => $char) {
            if ($isFirstCharacter) {
                $isFirstCharacter = false;
                continue;
            }

            if ($char === ' ' || $char === '_') {
                $isFirstCharacter = true;
                continue;
            }

            $value[$index] = '*';
        }

        return $value;
    }
}
