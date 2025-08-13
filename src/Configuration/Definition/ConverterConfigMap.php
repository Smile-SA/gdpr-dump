<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Definition;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @extends ArrayCollection<string, ConverterConfig>
 */
final class ConverterConfigMap extends ArrayCollection
{
    /**
     * Deep clone the object.
     */
    public function __clone(): void
    {
        foreach ($this as $index => $converterConfig) {
            $this->set($index, clone $converterConfig);
        }
    }
}
