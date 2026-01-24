<?php

namespace App\Services;

/**
 * Detects whether an email should be automatically flagged as test, fake, spam, or legitimate
 */
class EmailFlagDetector
{
    /**
     * Test email patterns
     */
    private const TEST_PATTERNS = [
        'test',
        'demo',
        'sample',
        'example.com',
        'test.com',
        'test.test',
        'localhost',
        'qa',
        'staging',
        'develop',
    ];

    /**
     * Fake email patterns
     */
    private const FAKE_PATTERNS = [
        'fake',
        'faker',
        'notreal',
        'nope',
        'invalid',
    ];

    /**
     * Spam email patterns
     */
    private const SPAM_PATTERNS = [
        'spam',
        'no-reply',
        'noreply',
        'do-not-reply',
        'donotreply',
        'auto-reply',
        'autoreply',
    ];

    /**
     * Temporary email domains (common disposable email services)
     */
    private const TEMPORARY_DOMAINS = [
        'tempmail.com',
        'temp-mail.org',
        'guerrillamail.com',
        'mailinator.com',
        '10minutemail.com',
        'throwaway.email',
        'trashmail.com',
        '0box.eu',
        'yopmail.com',
        'temp.email',
    ];

    /**
     * Detect if an email should be flagged and return the reason
     *
     * @param  ?string  $email  The email to check
     * @return ?string The flag reason (test, fake, spam, duplicate) or null
     */
    public function detect(?string $email): ?string
    {
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $email = strtolower($email);
        [$localPart, $domain] = explode('@', $email);

        // Check for test patterns
        if ($this->matchesPatterns($localPart, $domain, self::TEST_PATTERNS)) {
            return 'test';
        }

        // Check for fake patterns
        if ($this->matchesPatterns($localPart, $domain, self::FAKE_PATTERNS)) {
            return 'fake';
        }

        // Check for spam patterns
        if ($this->matchesPatterns($localPart, $domain, self::SPAM_PATTERNS)) {
            return 'spam';
        }

        // Check for temporary email providers
        if ($this->isTemporaryDomain($domain)) {
            return 'fake';
        }

        return null;
    }

    /**
     * Check if email matches any of the patterns
     */
    private function matchesPatterns(string $localPart, string $domain, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            // Check if pattern contains a domain (like example.com)
            if (str_contains($pattern, '.')) {
                if ($domain === $pattern) {
                    return true;
                }
            } else {
                // Check local part for the pattern
                if (str_contains($localPart, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if domain is a temporary email provider
     */
    private function isTemporaryDomain(string $domain): bool
    {
        return in_array($domain, self::TEMPORARY_DOMAINS);
    }
}
