<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Tests\Converter;

use PHPUnit\Framework\TestCase;
use Smile\Anonymizer\Converter\ConverterFactory;
use Smile\Anonymizer\Converter\Faker;
use Smile\Anonymizer\Converter\Proxy\Conditional;
use Smile\Anonymizer\Converter\Proxy\Unique;
use Smile\Anonymizer\Faker\FakerService;

class ConverterFactoryTest extends TestCase
{
    /**
     * Test the converter creation from a string definition.
     */
    public function testStringDefinition()
    {
        $factory = $this->createFactory();

        $converter = $factory->create(Dummy::class);
        $this->assertInstanceOf(Dummy::class, $converter);
    }

    /**
     * Test the converter creation from an array definition.
     */
    public function testArrayDefinition()
    {
        $factory = $this->createFactory();

        $converter = $factory->create(['converter' => Dummy::class]);
        $this->assertInstanceOf(Dummy::class, $converter);
    }

    /**
     * Test the creation of a Faker converter.
     */
    public function testFakerConverter()
    {
        $factory = $this->createFactory();

        $converter = $factory->create(['converter' => 'faker', 'parameters' => ['formatter' => 'safeEmail']]);
        $this->assertInstanceOf(Faker::class, $converter);
    }

    /**
     * Test the "unique" definition parameter.
     */
    public function testUniqueParameter()
    {
        $factory = $this->createFactory();

        $converter = $factory->create(['converter' => Dummy::class, 'unique' => true]);
        $this->assertInstanceOf(Unique::class, $converter);

        $converter = $factory->create(['converter' => Dummy::class, 'unique' => false]);
        $this->assertInstanceOf(Dummy::class, $converter);
    }

    /**
     * Test the "condition" definition parameter.
     */
    public function testConditionParameter()
    {
        $factory = $this->createFactory();

        $converter = $factory->create(['converter' => Dummy::class, 'condition' => '{{id}} === 1']);
        $this->assertInstanceOf(Conditional::class, $converter);
    }

    /**
     * Test if an exception is thrown when the converter is not set.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testConverterNotSet()
    {
        $factory = $this->createFactory();

        $factory->create([]);
    }

    /**
     * Test if an exception is thrown when the converter is set but empty.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testEmptyConverter()
    {
        $factory = $this->createFactory();

        $factory->create(['converter' => null]);
    }

    /**
     * Test if an exception is thrown when the "parameters" parameter is not an array.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testParametersNotAnArray()
    {
        $factory = $this->createFactory();

        $factory->create(['converter' => Dummy::class, 'parameters' => '']);
    }

    /**
     * Test if an exception is thrown when a "converter" parameter is used,
     * but the value is not a converter definition.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testConverterParameterMalformed()
    {
        $factory = $this->createFactory();

        $factory->create(['converter' => Dummy::class, 'parameters' => ['converter' => null]]);
    }

    /**
     * Test if an exception is thrown when a "converters" parameter is used,
     * but the value is not an array.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testConvertersParameterNotAnArray()
    {
        $factory = $this->createFactory();

        $factory->create(['converter' => Dummy::class, 'parameters' => ['converters' => null]]);
    }

    /**
     * Test if an exception is thrown when a "converters" parameter is used,
     * but the value is not an array of converter definition.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testConvertersParameterMalformed()
    {
        $factory = $this->createFactory();

        $factory->create(['converter' => Dummy::class, 'parameters' => ['converters' => [null]]]);
    }

    /**
     * Create a converter factory object.
     *
     * @return ConverterFactory
     */
    private function createFactory(): ConverterFactory
    {
        return new ConverterFactory(new FakerService());
    }
}
