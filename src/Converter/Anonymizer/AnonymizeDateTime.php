<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Converter\Anonymizer;

class AnonymizeDateTime extends AnonymizeDate
{
    protected $format = 'Y-m-d H:i:s';
}
