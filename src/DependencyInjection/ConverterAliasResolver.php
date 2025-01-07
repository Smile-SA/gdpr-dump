<?php

declare(strict_types=1);

namespace Smile\GdprDump\DependencyInjection;

use Smile\GdprDump\DependencyInjection\Compiler\ConverterAliasPass;

final class ConverterAliasResolver
{
    /**
     * Get service alias by converter name (e.g. "randomizeText").
     */
    public function getAliasByName(string $name): string
    {
        return ConverterAliasPass::ALIAS_PREFIX . $name;
    }
}
