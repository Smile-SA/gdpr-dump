<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper;

use Smile\Anonymizer\Config\ConfigInterface;

interface DumperInterface
{
    /**
     * Create a dump file of a database.
     */
    public function dump(ConfigInterface $config);
}
