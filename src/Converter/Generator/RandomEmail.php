<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

final class RandomEmail implements ConverterInterface
{
    /**
     * @var string[]
     */
    private array $domains;

    private int $domainsCount;
    private RandomText $textConverter;

    /**
     * @inheritdoc
     */
    public function setParameters(array $parameters): void
    {
        $emailParams = array_intersect_key($parameters, array_flip(['domains']));

        $input = (new ParameterProcessor())
            ->addParameter('domains', Parameter::TYPE_ARRAY, true, ['example.com', 'example.net', 'example.org'])
            ->process($emailParams);

        $this->domains = $input->get('domains');
        $this->domainsCount = count($this->domains);

        $this->textConverter = new RandomText();
        $this->textConverter->setParameters(array_diff_key($parameters, $emailParams));
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): string
    {
        $domainIndex = mt_rand(0, $this->domainsCount - 1);

        $value = $this->textConverter->convert($value);
        if ($value !== '') {
            $value .= '@' . $this->domains[$domainIndex];
        }

        return $value;
    }
}
