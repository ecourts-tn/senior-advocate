<?php

use App\Models\AdvocateDbModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class EnrolmentNumberTest extends CIUnitTestCase
{
    public function testParseNumberAndYearFromCommonFormats(): void
    {
        $this->assertSame(
            ['number' => '1234', 'year' => '2010'],
            AdvocateDbModel::parseNumberAndYear('1234/2010')
        );
        $this->assertSame(
            ['number' => '1234', 'year' => '2010'],
            AdvocateDbModel::parseNumberAndYear('Ms. 1234 / 2010')
        );
        $this->assertSame(
            ['number' => '1234', 'year' => '2010'],
            AdvocateDbModel::parseNumberAndYear('01234-2010')
        );
        $this->assertNull(AdvocateDbModel::parseNumberAndYear('ABC'));
        $this->assertNull(AdvocateDbModel::parseNumberAndYear(''));
    }

    public function testSameNumberAndYearIgnoresPrefixAndPadding(): void
    {
        $this->assertTrue(AdvocateDbModel::sameNumberAndYear('Ms.1234/2010', '1234/2010'));
        $this->assertTrue(AdvocateDbModel::sameNumberAndYear('01234/2010', '1234 / 2010'));
        $this->assertFalse(AdvocateDbModel::sameNumberAndYear('1234/2010', '1234/2011'));
        $this->assertFalse(AdvocateDbModel::sameNumberAndYear('1234/2010', '1235/2010'));
    }
}
