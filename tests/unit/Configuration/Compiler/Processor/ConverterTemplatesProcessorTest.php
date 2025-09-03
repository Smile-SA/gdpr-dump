<?php

declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Configuration\Compiler\Processor;

use Smile\GdprDump\Configuration\Compiler\Processor\ConverterTemplatesProcessor;
use Smile\GdprDump\Configuration\Exception\ParseException;
use Smile\GdprDump\Configuration\Loader\Container;
use Smile\GdprDump\Tests\Unit\TestCase;
use Smile\GdprDump\Util\Objects;

final class ConverterTemplatesProcessorTest extends TestCase
{
    /**
     * Assert that converter templates are merged into the configuration.
     */
    public function testTemplatesMerged(): void
    {
        $container = new Container(
            (object) [
                'tables' => (object) [
                    'no_converter' => (object) [
                        'truncate' => true,
                    ],
                    'no_template' => (object) [
                        'converters' => (object) [
                            'email' => (object) [
                                'converter' => 'randomizeEmail',
                            ],
                        ],
                    ],
                    'template' => (object) [
                        'converters' => (object) [
                            'email' => (object) [
                                'converter' => 'uniqueEmail',
                            ],
                        ],
                    ],
                    'template_with_config' => (object) [
                        'converters' => (object) [
                            'email' => (object) [
                                'converter' => 'uniqueEmail',
                                'unique' => false,
                                'parameters' => (object) [
                                    'min_length' => 5,
                                ],
                            ],
                        ],
                    ],
                    'converter_parameter' => (object) [
                        'converters' => (object) [
                            'username' => (object) [
                                'converter' => 'cache',
                                'parameters' => (object) [
                                    'cache_key' => 'user',
                                    'converter' => (object) ['converter' => 'uniqueUser'],
                                ],
                            ],
                        ],
                    ],
                    'converters_parameter_object' => (object) [
                        'converters' => (object) [
                            'chain' => (object) [
                                'converter' => 'chain',
                                'parameters' => (object) [
                                    'converters' => (object) [
                                        'username' => (object) ['converter' => 'uniqueUser'],
                                        'fullname' => (object) ['converter' => 'randomizeText'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'converters_parameter_array' => (object) [
                        'converters' => (object) [
                            'chain' => (object) [
                                'converter' => 'chain',
                                'parameters' => (object) [
                                    'converters' => [
                                        (object) ['converter' => 'uniqueUser'],
                                        (object) ['converter' => 'toLower'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'converter_templates' => (object) [
                    'uniqueEmail' => (object) [
                        'converter' => 'randomizeEmail',
                        'unique' => true,
                        'parameters' => (object) [
                            'domains' => ['example.com'],
                            'min_length' => 10,
                        ],
                    ],
                    'uniqueUser' => (object) [
                        'converter' => 'randomizeText',
                        'unique' => true,
                    ],
                ],
            ]
        );

        // Build the expected result (same configuration as above but with templates applied)
        $expected = $container->toArray();
        $expected['tables']['template']['converters']['email']['converter'] = 'randomizeEmail';
        $expected['tables']['template']['converters']['email']['unique'] = true;
        $expected['tables']['template']['converters']['email']['parameters'] = [
            'domains' => ['example.com'],
            'min_length' => 10,
        ];
        $expected['tables']['template_with_config']['converters']['email']['converter'] = 'randomizeEmail';
        $expected['tables']['template_with_config']['converters']['email']['unique'] = false;
        $expected['tables']['template_with_config']['converters']['email']['parameters']['domains'] = ['example.com'];

        // phpcs:disable Generic.Files.LineLength.TooLong
        $expected['tables']['converter_parameter']['converters']['username']['parameters']['converter']['converter'] = 'randomizeText';
        $expected['tables']['converter_parameter']['converters']['username']['parameters']['converter']['unique'] = true;
        $expected['tables']['converters_parameter_object']['converters']['chain']['parameters']['converters']['username']['converter'] = 'randomizeText';
        $expected['tables']['converters_parameter_object']['converters']['chain']['parameters']['converters']['username']['unique'] = true;
        $expected['tables']['converters_parameter_array']['converters']['chain']['parameters']['converters'][0]['converter'] = 'randomizeText';
        $expected['tables']['converters_parameter_array']['converters']['chain']['parameters']['converters'][0]['unique'] = true;
        // phpcs:enable Generic.Files.LineLength.TooLong

        $processor = new ConverterTemplatesProcessor();
        $processor->process($container);

        $this->assertEquals($expected, $container->toArray());
    }

    /**
     * Assert that the processor doesn't do anything if no converter templates were declared.
     */
    public function testNoActionIfNoTemplatesDeclared(): void
    {
        $container = new Container(
            (object) [
                'tables' => (object) [
                    'users' => (object) [
                        'converters' => (object) [
                            'email' => (object) ['converter' => 'uniqueEmail'],
                        ],
                    ],
                ],
            ]
        );

        $expected = Objects::deepClone($container->getRoot());
        $processor = new ConverterTemplatesProcessor();
        $processor->process($container);
        $this->assertEquals($expected, $container->getRoot());
    }

    /**
     * Assert that an exception is thrown when trying to apply converter templates recursively.
     */
    public function testErrorOnRecursiveTemplate(): void
    {
        $container = new Container(
            (object) [
                'tables' => (object) [
                    'template' => (object) [
                        'converters' => (object) [
                            'email' => (object) ['converter' => 'uniqueEmail'],
                        ],
                    ],
                ],
                'converter_templates' => (object) [
                    'uniqueEmail' => (object) ['converter' => 'uniqueUser'],
                    'uniqueUser' => (object) ['converter' => 'randomizeText'],
                ],
            ]
        );

        $processor = new ConverterTemplatesProcessor();
        $this->expectException(ParseException::class);
        $processor->process($container);
    }
}
