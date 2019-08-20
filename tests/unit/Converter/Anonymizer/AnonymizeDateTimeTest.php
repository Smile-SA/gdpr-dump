<?php
declare(strict_types=1);

namespace Smile\GdprDump\Tests\Unit\Converter\Anonymizer;

use Smile\GdprDump\Converter\Anonymizer\AnonymizeDateTime;

class AnonymizeDateTimeTest extends AnonymizeDateTest
{
    /**
     * Test the converter.
     */
    public function testConverter()
    {
        $converter = new AnonymizeDateTime();

        $date = '1990-12-31 12:05:41';
        $anonymizedDate = $converter->convert($date);
        $this->assertDateIsAnonymized($anonymizedDate, $date, 'Y-m-d H:i:s');
    }

    /**
     * Test using a custom date format.
     */
    public function testFormatParameter()
    {
        $format = 'd/m/Y H:i:s';
        $converter = new AnonymizeDateTime(['format' => $format]);

        $date = '31/12/1990 12:05:41';
        $anonymizedDate = $converter->convert($date);
        $this->assertDateIsAnonymized($anonymizedDate, $date, $format);
    }

    /**
     * Test if an exception is thrown when an invalid date is provided.
     *
     * @expectedException \UnexpectedValueException
     */
    public function testInvalidDateFormat()
    {
        $converter = new AnonymizeDateTime();
        $converter->convert('invalidFormat');
    }
}
