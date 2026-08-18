<?php

use App\Models\UserModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class UserLoginIdentifierTest extends CIUnitTestCase
{
    public function testLooksLikeEmail(): void
    {
        $this->assertTrue(UserModel::looksLikeEmail('advocate@example.com'));
        $this->assertTrue(UserModel::looksLikeEmail('  Name@HighCourt.tn.gov.in '));
        $this->assertFalse(UserModel::looksLikeEmail('9876543210'));
        $this->assertFalse(UserModel::looksLikeEmail('+91 98765 43210'));
        $this->assertFalse(UserModel::looksLikeEmail(''));
    }

    public function testNormaliseMobileStripsPrefixAndPunctuation(): void
    {
        $this->assertSame('9876543210', UserModel::normaliseMobile('9876543210'));
        $this->assertSame('9876543210', UserModel::normaliseMobile('+91 98765 43210'));
        $this->assertSame('9876543210', UserModel::normaliseMobile('919876543210'));
        $this->assertSame('9876543210', UserModel::normaliseMobile('09876543210'));
        $this->assertSame('9876543210', UserModel::normaliseMobile('0919876543210'));
        $this->assertSame('', UserModel::normaliseMobile('abc'));
    }
}
