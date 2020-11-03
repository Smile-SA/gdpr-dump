<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Generator;

use Smile\GdprDump\Converter\Parameters\Parameter;
use Smile\GdprDump\Converter\Parameters\ParameterProcessor;
use Smile\GdprDump\Converter\Parameters\ValidationException;

class RandomEmail extends RandomText
{
    /**
     * @var string[]
     */
    protected $domains;

    /**
     * @var int
     */
    protected $domainsCount;

    /**
     * @param array $parameters
     * @throws ValidationException
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct($parameters);

        $input = (new ParameterProcessor())
            ->addParameter('domains', Parameter::TYPE_ARRAY, true, ['example.com', 'example.net', 'example.org'])
            ->process($parameters);

        $this->domains = $input->get('domains');
        $this->domainsCount = count($this->domains);
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $domainIndex = mt_rand(0, $this->domainsCount - 1);

        $value = parent::convert($value);
        if ($value !== '') {
            $value .= '@' . $this->domains[$domainIndex];
        }

        return $value;
    }
}
