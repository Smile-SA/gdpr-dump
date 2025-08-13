<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Config\EventListener;

use Smile\GdprDump\Configuration\Event\ConfigurationParsedEvent;
use Smile\GdprDump\Configuration\EventListener\EnvVarListener;
use Smile\GdprDump\Configuration\Loader\EnvVarProcessor;
use Smile\GdprDump\Tests\Unit\TestCase;

final class EnvVarListenerTest extends TestCase
{
    /**
     * Assert that the listener uses the env var processed on the parsed data.
     */
    public function testEnvVarListener(): void
    {
        putenv('TEST_VAR=1');

        $data = (object) ['strict_schema' => '%env(bool:TEST_VAR)%'];
        $listener = new EnvVarListener(new EnvVarProcessor());
        $listener(new ConfigurationParsedEvent($data));

        $env = getenv('TEST_ENV_VAR');

        $this->assertSame(true, $data->strict_schema);

        putenv('TEST_VAR');
    }
}
