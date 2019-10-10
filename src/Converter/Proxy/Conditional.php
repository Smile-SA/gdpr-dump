<?php
declare(strict_types=1);

namespace Smile\GdprDump\Converter\Proxy;

use InvalidArgumentException;
use RuntimeException;
use Smile\GdprDump\Converter\ConverterInterface;

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
     * @var array
     */
    private $statementBlacklist = ['<?php', '<?', '?>'];

    /**
     * @var array
     */
    private $functionWhitelist = [
        'addslashes', 'chr', 'date', 'empty', 'implode', 'is_null', 'is_numeric', 'lcfirst', 'ltrim',
        'md5', 'number_format', 'preg_match', 'rtrim', 'sha1', 'sprintf', 'str_pad', 'str_repeat',
        'htmlentities', 'str_replace', 'str_word_count', 'strchr', 'strcmp', 'strcspn', 'stripcslashes',
        'stripos', 'stripslashes', 'stristr', 'strnatcasecmp', 'strnatcmp', 'strncasecmp', 'strncmp',
        'strpos', 'strrchr', 'strrev', 'htmlspecialchars', 'strripos', 'strrpos', 'strspn', 'strstr',
        'strtolower', 'strtoupper', 'strtr', 'substr', 'substr_compare', 'substr_count', 'substr_replace',
        'time', 'trim', 'ucfirst', 'ucwords', 'vsprintf', 'wordwrap',
    ];

    /**
     * @param array $parameters
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function __construct(array $parameters)
    {
        if (!isset($parameters['condition']) || $parameters['condition'] === '') {
            throw new InvalidArgumentException('The conditional converter requires a "condition" parameter.');
        }

        if (!isset($parameters['if_true_converter']) && !isset($parameters['if_false_converter'])) {
            throw new InvalidArgumentException(
                'The conditional converter requires a "if_true_converter" and/or "if_false_converter" parameter.'
            );
        }

        $this->condition = $this->parseCondition($parameters['condition']);

        if (isset($parameters['if_true_converter'])) {
            $this->ifTrueConverter = $parameters['if_true_converter'];
        }

        if (isset($parameters['if_false_converter'])) {
            $this->ifFalseConverter = $parameters['if_false_converter'];
        }
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.EvalExpression)
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
     * Parse the condition.
     *
     * @param string $condition
     * @return string
     */
    private function parseCondition(string $condition): string
    {
        // Sanitize the condition
        $condition = $this->sanitizeCondition($condition);

        // Validate the condition
        $this->validateCondition($this->removeQuotedValues($condition));

        // Replace SQL column names by their values in the condition
        // e.g. {{identifier}} replaced by $context['row_data']['identifier']
        $condition = preg_replace("/(?<=[^\w[}@]){{(\w+)}}(?=[^\w{}@])/", "\$context['row_data']['$1']", $condition);

        // Replace SQL variable names by their values in the condition
        // e.g. @identifier replaced by $context['vars']['identifier']
        $condition = preg_replace('/(?<=[^\w{}@])@(\w+)(?=[^\w{}@])/', "\$context['vars']['$1']", $condition);

        // If there are still "{", "}" or "@" characters, they were incorrectly used
        if (preg_match('/[@{}]/', $condition, $matches)) {
            throw new RuntimeException(sprintf('Invalid use of "%s" character in condition.', $matches[0]));
        }

        return $condition;
    }

    /**
     * Sanitize the condition.
     *
     * @param string $condition
     * @return string
     * @throws RuntimeException
     */
    private function sanitizeCondition(string $condition): string
    {
        // Remove line breaks
        $condition = preg_replace('/[\r\n]+/', ' ', $condition);

        // Add instruction separator
        if (substr($condition, -1) !== ';') {
            $condition .= ';';
        }

        // Add return statement
        if (substr($condition, 0, 6) !== 'return') {
            $condition = 'return ' . $condition;
        }

        return $condition;
    }

    /**
     * Validate the condition.
     *
     * @param string $condition
     * @throws RuntimeException
     */
    private function validateCondition(string $condition)
    {
        // Prevent usage of "=" operator
        if (preg_match('/[^=!]=[^=]/', $condition)) {
            throw new RuntimeException('The "=" operator is not allowed in a filter.');
        }

        // Prevent usage of "$" character
        if (preg_match('/\$/', $condition)) {
            throw new RuntimeException('The "$" character is not allowed in a filter.');
        }

        // Prevent the use of some statements
        foreach ($this->statementBlacklist as $statement) {
            if (strpos($condition, $statement) !== false) {
                $message = sprintf('The statement "%s" is not allowed in converter conditions.', $statement);
                throw new RuntimeException($message);
            }
        }

        // Prevent the use of static functions
        if (preg_match('/::(\w+) *\(/', $condition)) {
            throw new RuntimeException('Static functions are not allowed in converter conditions.');
        }

        // Allow only specific functions
        if (preg_match_all('/(\w+) *\(/', $condition, $matches)) {
            foreach ($matches[1] as $function) {
                if (!in_array($function, $this->functionWhitelist)) {
                    $message = sprintf('The function "%s" is not allowed in converter conditions.', $function);
                    throw new RuntimeException($message);
                }
            }
        }
    }

    /**
     * Remove quoted values from a variable,
     * e.g. "$s = 'value'" is converted to "$s = ''"
     *
     * @param string $condition
     * @return string
     */
    private function removeQuotedValues(string $condition): string
    {
        // Split the condition into PHP tokens
        $tokens = token_get_all('<?php ' . $condition . ' ?>');
        $condition = '';

        foreach ($tokens as $token) {
            if (!is_array($token)) {
                $condition .= $token;
                continue;
            }

            // Remove magic strings
            $condition .= ($token[0] === T_CONSTANT_ENCAPSED_STRING ? "''" : $token[1]);
        }

        // Remove the opening and closing tag that were added to generate the tokens
        $condition = ltrim($condition, '<?php ');
        $condition = rtrim($condition, ' ?>');

        return $condition;
    }
}
