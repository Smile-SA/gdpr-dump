<?php

declare(strict_types=1);

namespace Smile\GdprDump\Phar\Minify;

class Php implements MinifierInterface
{
    /**
     * @inheritdoc
     */
    public function minify(string $contents): string
    {
        $result = '';
        $isWhitespace = false;

        foreach (token_get_all($contents) as $token) {
            if (is_string($token)) {
                $result .= $token;
                $isWhitespace = false;
            } elseif ($token[0] === T_COMMENT || $token[0] === T_DOC_COMMENT) {
                // Remove all comments
                $result .= '';
                $isWhitespace = true;
            } elseif ($token[0] === T_WHITESPACE) {
                // Replace everything with a single space (if previous char isn't already a space)
                $result .= !$isWhitespace ? ' ' : '';
                $isWhitespace = true;
            } else {
                $result .= $token[1];
                $isWhitespace = false;
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function supports(string $extension): bool
    {
        return $extension === 'php';
    }
}
