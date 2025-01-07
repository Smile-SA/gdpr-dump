<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\DependencyInjection;

use Smile\GdprDump\DependencyInjection\Compiler\ConverterAliasPass;
use Smile\GdprDump\DependencyInjection\ConverterAliasResolver;
use Smile\GdprDump\Tests\Unit\TestCase;

final class ConverterAliasResolverTest extends TestCase
{
    /**
     * Test the alias resolver.
     */
    public function testResolver(): void
    {
        $resolver = new ConverterAliasResolver();
        $converterName = 'randomizeText';
        $this->assertSame(ConverterAliasPass::ALIAS_PREFIX . $converterName, $resolver->getAliasByName($converterName));
    }
}
