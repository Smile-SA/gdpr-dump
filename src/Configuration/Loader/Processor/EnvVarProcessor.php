<?php

declare(strict_types=1);

namespace Smile\GdprDump\Configuration\Loader\Processor;

use Smile\GdprDump\Configuration\Loader\Env\EnvVarParser;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;

#[AsTaggedItem(priority: 10)]
class EnvVarProcessor implements Processor
{
    public function __construct(private EnvVarParser $envVarParser)
    {
    }

    /**
     * Replaces env var placeholders such as `%env(VAR)%`.
     */
    public function process(stdClass $configuration): void
    {
        $this->envVarParser->parse($configuration);
    }
}
