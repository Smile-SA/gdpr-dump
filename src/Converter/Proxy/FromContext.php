<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use RuntimeException;
use Smile\GdprDump\Converter\ContextAwareInterface;
use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;
use Smile\GdprDump\Dumper\DumpContext;

final class FromContext implements ConverterInterface, ContextAwareInterface
{
    private const TYPE_ROW_DATA = 'row_data';
    private const TYPE_PROCESSED_DATA = 'processed_data';
    private const TYPE_VARIABLES = 'variables';

    private DumpContext $dumpContext;
    private string $type;
    private string $index;

    public function setParameters(array $parameters): void
    {
        $input = (new ParameterProcessor())
            ->addParameter('key', Parameter::TYPE_STRING, true)
            ->process($parameters);

        $key = $input->get('key');
        $parts = explode('.', $key);

        $allowed = [self::TYPE_ROW_DATA, self::TYPE_PROCESSED_DATA, self::TYPE_VARIABLES];
        if (count($parts) !== 2 || !in_array($parts[0], $allowed, true)) {
            throw new ValidationException(sprintf('Invalid context key "%s"', $key));
        }

        $this->type = $parts[0];
        $this->index = $parts[1];
    }

    public function setDumpContext(DumpContext $dumpContext): void
    {
        $this->dumpContext = $dumpContext;
    }

    public function convert(mixed $value): mixed
    {
        return match ($this->type) {
            self::TYPE_ROW_DATA => $this->dumpContext->currentRow[$this->index] ?? null,
            self::TYPE_PROCESSED_DATA => $this->dumpContext->processedData[$this->index] ?? null,
            self::TYPE_VARIABLES => $this->dumpContext->variables[$this->index] ?? null,
            default => throw new RuntimeException(sprintf('Invalid index "%s"', $this->index)),
        };
    }
}
