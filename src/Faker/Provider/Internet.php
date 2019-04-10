<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Faker\Provider;

use Faker\Provider\Internet as BaseInternet;

class Internet extends BaseInternet
{
    /**
     * @var string[]
     */
    protected static $userNameFormats = [
        '{{firstName}}.{{lastName}}',
        '{{firstName}}##.{{lastName}}',
        '{{firstName}}.{{lastName}}##',
        '{{lastName}}.{{firstName}}',
        '{{lastName}}##.{{firstName}}',
        '{{lastName}}.{{firstName}}##',
        '{{firstName}}##',
        '{{lastName}}##',
    ];

    /**
     * Generate an email from a context.
     *
     * @param array $context
     * @return string
     * @throws \Exception
     */
    public function contextualEmail(array $context = []): string
    {
        // Fallback on default userName formatter
        if (empty($context)) {
            return $this->generator->format('safeEmail');
        }

        $domain = $context['domain'] ?? static::safeEmailDomain();
        $userName = $context['userName'] ?? $this->contextualUserName($context);

        return preg_replace('/\s/u', '', $userName . '@' . $domain);
    }

    /**
     * Generate a user name from a context.
     *
     * @param array $context
     * @return string
     * @throws \Exception
     */
    public function contextualUserName(array $context = []): string
    {
        // Fallback on default userName formatter
        if (empty($context)) {
            return $this->generator->format('userName');
        }

        $format = static::randomElement(static::$userNameFormats);

        // Build the user name
        $userName = str_replace(
            ['{{firstName}}', '{{lastName}}'],
            [$this->generator->format('firstName'), $this->generator->format('lastName')],
            $format
        );

        $userName = static::bothify($userName);
        $userName = static::transliterate($userName);
        $userName = strtolower($userName);

        // Check if transliterate() didn't support the language and removed all letters
        if (trim($userName, '._') === '') {
            throw new \Exception(
                'userName failed with the selected locale. Try a different locale or activate the "intl" PHP extension.'
            );
        }

        // Clean possible trailing dots from first/last names
        $userName = str_replace('..', '.', $userName);
        $userName = rtrim($userName, '.');

        return $userName;
    }
}
