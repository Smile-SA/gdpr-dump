<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

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
     */
    public function __construct(array $parameters = [])
    {
        if (!empty($parameters['domains'])) {
            $this->domains = (array) $parameters['domains'];
        }

        $this->domainsCount = count($this->domains);
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $parts = explode('@', $value);

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
