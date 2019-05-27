<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Proxy;

use Smile\Anonymizer\Converter\ConverterInterface;

class Conditional implements ConverterInterface
{
    /**
     * @var string
     */
    private $condition;

    /**
     * @var ConverterInterface
     */
    private $ifTrueConverter;

    /**
     * @var ConverterInterface
     */
    private $ifFalseConverter;

    /**
     * @param array $parameters
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function __construct(array $parameters)
    {
        if (!isset($parameters['condition']) || $parameters['condition'] === '') {
            throw new \InvalidArgumentException('The conditional converter requires a "condition" parameter.');
        }

        if (!isset($parameters['if_true_converter']) && !isset($parameters['if_false_converter'])) {
            throw new \InvalidArgumentException(
                'The conditional converter requires a "if_true_converter" and/or "if_false_converter" parameter.'
            );
        }

        $this->condition = $this->sanitize((string) $parameters['condition']);

        if (isset($parameters['if_true_converter'])) {
            $this->ifTrueConverter = $parameters['if_true_converter'];
        }

        if (isset($parameters['if_false_converter'])) {
            $this->ifFalseConverter = $parameters['if_false_converter'];
        }
    }

    /**
     * @inheritdoc
     */
    public function convert($value, array $context = [])
    {
        $result = eval($this->condition);

        if ($result) {
            if ($this->ifTrueConverter) {
                $value = $this->ifTrueConverter->convert($value, $context);
            }
        } elseif ($this->ifFalseConverter) {
            $value = $this->ifFalseConverter->convert($value, $context);
        }

        return $value;
    }

    /**
     * Sanitize a filter.
     *
     * @param string $filter
     * @return string
     * @throws \RuntimeException
     */
    private function sanitize(string $filter): string
    {
        // Remove line breaks
        $filter = preg_replace('/[\r\n]+/', ' ', $filter);

        // Add instruction separator
        if (substr($filter, -1) !== ';') {
            $filter .= ';';
        }

        // Add return statement
        if (substr($filter, 0, 6) !== 'return') {
            $filter = 'return ' . $filter;
        }

        // Check if the filter is valid
        $this->validate($filter);

        // Replace variables, e.g. {{id}} to $context['id']
        $left = preg_quote('{{');
        $right = preg_quote('}}');
        $filter = preg_replace("/$left(\w+)$right/", "\$context['$1']", $filter);

        return $filter;
    }

    /**
     * Validate the filter.
     *
     * @param string $filter
     * @throws \RuntimeException
     */
    private function validate(string $filter)
    {
        // Prevent usage of "=" operator
        if (preg_match('/[^=!]=[^=]/', $filter)) {
            throw new \RuntimeException('The "=" operator is not allowed in a filter.');
        }

        // Prevent usage of "$" character
        if (preg_match('/\$/', $filter)) {
            throw new \RuntimeException('The "$" character is not allowed in a filter.');
        }

        // Prevent the use of some statements
        foreach ($this->getStatementBlacklist() as $statement) {
            if (strpos($filter, $statement) !== false) {
                throw new \RuntimeException(sprintf('The statement "%s" is not allowed in a filter.', $statement));
            }
        }

        // Prevent the use of static functions
        if (preg_match('/::(\w+) *\(/', $filter)) {
            throw new \RuntimeException('Static functions are not allowed in a filter.');
        }

        // Allow only specific functions
        if (preg_match_all('/(\w+) *\(/', $filter, $matches)) {
            $functionWhitelist = $this->getFunctionWhitelist();

            foreach ($matches[1] as $function) {
                if (!in_array($function, $functionWhitelist)) {
                    throw new \RuntimeException(sprintf('The function "%s" is not allowed in a filter.', $function));
                }
            }
        }
    }

    /**
     * Get the statements forbidden in a filter.
     *
     * @return array
     */
    private function getStatementBlacklist(): array
    {
        return [
            '<?php', '<?', '?>',
        ];
    }

    /**
     * Get the functions allowed in a filter.
     *
     * @return string[]
     */
    private function getFunctionWhitelist(): array
    {
        return [
            'addslashes', 'chr', 'date', 'empty', 'implode', 'is_null', 'is_numeric', 'lcfirst', 'ltrim',
            'md5', 'number_format', 'preg_match', 'rtrim', 'sha1', 'sprintf', 'str_pad', 'str_repeat',
            'htmlentities', 'str_replace', 'str_word_count', 'strchr', 'strcmp', 'strcspn', 'stripcslashes',
            'stripos', 'stripslashes', 'stristr', 'strnatcasecmp', 'strnatcmp', 'strncasecmp', 'strncmp',
            'strpos', 'strrchr', 'strrev', 'htmlspecialchars', 'strripos', 'strrpos', 'strspn', 'strstr',
            'strtolower', 'strtoupper', 'strtr', 'substr', 'substr_compare', 'substr_count', 'substr_replace',
            'time', 'trim', 'ucfirst', 'ucwords', 'vsprintf', 'wordwrap',
        ];
    }
}
