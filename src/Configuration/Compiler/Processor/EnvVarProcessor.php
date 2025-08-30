<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Compiler\Processor;

use Smile\GdprDump\Configuration\Compiler\CompilerStep;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Configuration\Loader\Env\EnvVarParser;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 10)]
class EnvVarProcessor implements Processor
{
    public function __construct(private EnvVarParser $envVarParser)
    {
    }

    public function getStep(): CompilerStep
    {
        return CompilerStep::BEFORE_VALIDATION;
    }

    /**
     * Replaces env var placeholders such as `%env(VAR)%`.
     */
    public function process(Container $container): void
    {
        $this->envVarParser->parse($container->getRoot());
    }
}
