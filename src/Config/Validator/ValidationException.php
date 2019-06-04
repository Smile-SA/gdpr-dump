<?php
declare(strict_types=1);

namespace Smile\GdprDump\Config\Validator;

class ValidationException extends \Exception
{
    /**
     * @param string $message
     * @param \Throwable $previous
     */
    public function __construct(string $message, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
