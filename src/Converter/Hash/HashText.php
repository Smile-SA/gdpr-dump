<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Hash;

use Smile\GdprDump\Converter\ContextAwareInterface;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Dumper\DumpContext;

final class HashText implements ConverterInterface, ContextAwareInterface
{
    private const DEFAULT_ALGORITHM = 'sha224';

    private string $secret;
    private string $algorithm;
    private int $length;

    /**
     * Number of characters for each algo (4 bit = 1 hexadecimal character).
     *
     * @var array<string, int>
     */
    private array $lengthMap = [
        'sha1' => 160 / 4,
        'sha224' => 224 / 4,
        'sha256' => 256 / 4,
        'sha384' => 384 / 4,
        'sha512' => 512 / 4,
    ];

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('algorithm', Parameter::TYPE_STRING, true, self::DEFAULT_ALGORITHM)
            ->addParameter('length', Parameter::TYPE_INT, true, $this->lengthMap[self::DEFAULT_ALGORITHM] / 2)
            ->process($parameters);

        $this->algorithm = $input->get('algorithm');
        $this->length = $input->get('length');

        if (!array_key_exists($this->algorithm, $this->lengthMap)) {
            $allowed = array_keys($this->lengthMap);
            throw new ValidationException(
                sprintf('Invalid algorithm "%s". Allowed values: %s.', $this->algorithm, implode(', ', $allowed))
            );
        }

        $maxLength = $this->lengthMap[$this->algorithm];
        if ($this->length > $maxLength) {
            throw new ValidationException(
                sprintf('The parameter "length" must be lower or equal than %s.', $maxLength)
            );
        }

        // Collision resistance strength is half the strength of the hashed value
        $minLength = $maxLength / 2;
        if ($this->length < $minLength) {
            throw new ValidationException(
                sprintf('The parameter "length" must be greater or equal than %s.', $minLength)
            );
        }
    }

    public function setDumpContext(DumpContext $dumpContext): void
    {
        $this->secret = $dumpContext->secret ?: 'todo_generate_global_secret_from_helper_class';
    }

    public function convert(mixed $value): string
    {
        $value = (string) $value;
        if ($value === '') {
            return $value;
        }

        $value = hash_hmac($this->algorithm, $value, $this->secret);

        return strlen($value) > $this->length
            ? substr($value, 0, $this->length)
            : $value;
    }
}
