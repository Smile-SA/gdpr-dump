<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;

class AnonymizeEmail extends AnonymizeText
{
    /**
     * @var string[]
     */
    private array $domains;

    private int $domainsCount;

    /**
     * @inheritdoc
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
     */
    public function convert(mixed $value, array $context = []): string
    {
        $value = (string) $value;
        if ($value === '') {
            return $value;
        }

        // Replace the username
        $parts = explode('@', $value);
        $value = parent::convert($parts[0]);

        if (!isset($parts[1])) {
            return $value;
        }

        // Replace the email domain
        $index = mt_rand(0, $this->domainsCount - 1);
        $value .= '@' . $this->domains[$index];

        return $value;
    }
}
