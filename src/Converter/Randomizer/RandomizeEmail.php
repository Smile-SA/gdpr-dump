<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Randomizer;

use Smile\GdprDump\Converter\ConverterInterface;
use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

final class RandomizeEmail implements ConverterInterface
{
    /**
     * @var string[]
     */
    private array $domains;

    private int $domainsCount;
    private RandomizeText $textConverter;

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

        $this->textConverter = new RandomizeText();
        $this->textConverter->setParameters(array_diff_key($parameters, $emailParams));
    }

    /**
     * @inheritdoc
     */
    public function convert(mixed $value, array $context = []): string
    {
        $value = (string) $value;
        if ($value === '') {
            return $value;
        }

        // Replace the username
        $parts = explode('@', $value);
        $value = $this->textConverter->convert($parts[0]);

        if (!isset($parts[1])) {
            return $value;
        }

        // Replace the email domain
        $index = mt_rand(0, $this->domainsCount - 1);
        $value .= '@' . $this->domains[$index];

        return $value;
    }
}
