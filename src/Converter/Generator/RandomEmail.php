<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Random\RandomException;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class RandomEmail extends RandomText
{
    /**
     * @var string[]
     */
    private array $domains;

    private int $domainsCount;

    /**
     * @inheritdoc
     * @throws ValidationException
     */
    public function setParameters(array $parameters): void
    {
        parent::setParameters($parameters);

        $input = (new ParameterProcessor())
            ->addParameter('domains', Parameter::TYPE_ARRAY, true, ['example.com', 'example.net', 'example.org'])
            ->process($parameters);

        $this->domains = $input->get('domains');
        $this->domainsCount = count($this->domains);
    }

    /**
     * @inheritdoc
     * @throws RandomException
     */
    public function convert(mixed $value, array $context = []): string
    {
        $domainIndex = random_int(0, $this->domainsCount - 1);

        $value = parent::convert($value);
        if ($value !== '') {
            $value .= '@' . $this->domains[$domainIndex];
        }

        return $value;
    }
}
