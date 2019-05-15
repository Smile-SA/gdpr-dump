<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper;

use Smile\Anonymizer\Config\ConfigInterface;

interface DumperInterface
{
    /**
     * Create a dump file of a database.
     *
     * @param ConfigInterface $config
     * @return $this
     */
    public function dump(ConfigInterface $config): DumperInterface;
}
