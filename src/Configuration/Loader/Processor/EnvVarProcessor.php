<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Processor;

use Smile\GdprDump\Configuration\Loader\Env\EnvVarParser;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

/**
 * Replaces env var placeholders such as `%env(VAR)%`. This processor has a higher priority than the others.
 */
#[AsTaggedItem(priority: 10)]
class EnvVarProcessor implements Processor
{
    public function __construct(private EnvVarParser $envVarParser)
    {
    }

    public function process(stdClass $configuration): void
    {
        $this->envVarParser->parse($configuration);
    }
}
