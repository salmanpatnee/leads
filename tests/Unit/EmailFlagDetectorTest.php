<?php

namespace Tests\Unit;

use App\Services\EmailFlagDetector;
use PHPUnit\Framework\TestCase;

class EmailFlagDetectorTest extends TestCase
{
    private EmailFlagDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detector = new EmailFlagDetector;
    }

    // Test patterns
    public function test_detects_test_email_pattern(): void
    {
        $this->assertEquals('test', $this->detector->detect('test@gmail.com'));
        $this->assertEquals('test', $this->detector->detect('test.user@company.com'));
        $this->assertEquals('test', $this->detector->detect('TEST@gmail.com'));
    }

    public function test_detects_demo_pattern(): void
    {
        $this->assertEquals('test', $this->detector->detect('demo@gmail.com'));
        $this->assertEquals('test', $this->detector->detect('demo.account@example.org'));
    }

    public function test_detects_sample_pattern(): void
    {
        $this->assertEquals('test', $this->detector->detect('sample@gmail.com'));
        $this->assertEquals('test', $this->detector->detect('sample.user@company.com'));
    }

    public function test_detects_example_com_domain(): void
    {
        $this->assertEquals('test', $this->detector->detect('user@example.com'));
        $this->assertEquals('test', $this->detector->detect('admin@example.com'));
    }

    public function test_detects_test_com_domain(): void
    {
        $this->assertEquals('test', $this->detector->detect('user@test.com'));
        $this->assertEquals('test', $this->detector->detect('admin@test.com'));
    }

    public function test_detects_fake_pattern(): void
    {
        $this->assertEquals('fake', $this->detector->detect('fake@gmail.com'));
        $this->assertEquals('fake', $this->detector->detect('faker@company.com'));
        $this->assertEquals('fake', $this->detector->detect('notreal@example.org'));
    }

    public function test_detects_spam_pattern(): void
    {
        $this->assertEquals('spam', $this->detector->detect('spam@gmail.com'));
        $this->assertEquals('spam', $this->detector->detect('noreply@company.com'));
        $this->assertEquals('spam', $this->detector->detect('no-reply@company.com'));
        $this->assertEquals('spam', $this->detector->detect('donotreply@company.com'));
    }

    public function test_detects_temporary_email_providers(): void
    {
        $this->assertEquals('fake', $this->detector->detect('user@tempmail.com'));
        $this->assertEquals('fake', $this->detector->detect('user@10minutemail.com'));
        $this->assertEquals('fake', $this->detector->detect('user@guerrillamail.com'));
        $this->assertEquals('fake', $this->detector->detect('user@mailinator.com'));
    }

    public function test_returns_null_for_legitimate_email(): void
    {
        $this->assertNull($this->detector->detect('john.doe@company.com'));
        $this->assertNull($this->detector->detect('jane.smith@example.org'));
        $this->assertNull($this->detector->detect('support@mycompany.com'));
    }

    public function test_returns_null_for_invalid_email(): void
    {
        $this->assertNull($this->detector->detect('not-an-email'));
        $this->assertNull($this->detector->detect(''));
        $this->assertNull($this->detector->detect(null));
    }

    public function test_case_insensitive_detection(): void
    {
        $this->assertEquals('test', $this->detector->detect('TEST@GMAIL.COM'));
        $this->assertEquals('fake', $this->detector->detect('FAKE@COMPANY.COM'));
        $this->assertEquals('spam', $this->detector->detect('NOREPLY@COMPANY.COM'));
    }
}
