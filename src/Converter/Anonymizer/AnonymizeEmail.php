<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

use UnexpectedValueException;

class AnonymizeEmail extends AnonymizeText
{
    /**
     * @var string[]
     */
    private $domains = [
        'example.com',
        'example.net',
        'example.org',
    ];

    /**
     * @var int
     */
    private $domainsCount;

    /**
     * @param array $parameters
     * @throws UnexpectedValueException
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct($parameters);

        if (array_key_exists('domains', $parameters)) {
            if (!is_array($parameters['domains'])) {
                throw new UnexpectedValueException('The parameter "domains" must be an array.');
            }

            if (empty($parameters['domains'])) {
                throw new UnexpectedValueException('The parameter "domains" must not be empty.');
            }

            $this->domains = $parameters['domains'];
        }

        $this->domainsCount = count($this->domains);
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $string = (string) $value;
        if ($string === '') {
            return $value;
        }

        $parts = explode('@', $string);

        if (isset($parts[0])) {
            $parts[0] = parent::convert($parts[0]);
        }

        // Replace the email domain
        if (isset($parts[1])) {
            $index = mt_rand(0, $this->domainsCount - 1);
            $parts[1] = $this->domains[$index];
        }

        return implode('@', $parts);
    }
}
