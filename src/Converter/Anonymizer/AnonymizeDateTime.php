<?php

declare(strict_types=1);

namespace Smile\GdprDump\Converter\Anonymizer;

class AnonymizeDateTime extends AnonymizeDate
{
    /**
     * @inheritdoc
     */
    protected $defaultFormat = 'Y-m-d H:i:s';
}
