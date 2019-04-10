<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Anonymize;

class AnonymizeEmail extends AnonymizeText
{
    /**
     * @var array
     */
    private $domains = [
        'example.com',
        'example.net',
        'example.org',
    ];

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        parent::__construct($parameters);

        if (!empty($parameters['domains'])) {
            $this->domains = $parameters['domains'];
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

        $parts = explode('@', $value);

        // Replace alphanumeric characters by random characters
        if (isset($parts[0])) {
            $parts[0] = parent::convert($parts[0]);
        }

        // Replace the email domain
        if (isset($parts[1])) {
            $index = mt_rand(0, 2);
            $parts[1] = $this->domains[$index];
        }

        return implode('@', $parts);
    }
}
