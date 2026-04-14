<?php

/*
 * This file is part of fof/pwned-passwords.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\PwnedPasswords\Tests\unit;

use FoF\PwnedPasswords\HibpClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class HibpClientTest extends TestCase
{
    private Client&MockObject $guzzle;
    private LoggerInterface&MockObject $logger;
    private HibpClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->guzzle = $this->createMock(Client::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->client = new HibpClient($this->guzzle, $this->logger);
    }

    #[Test]
    public function it_returns_true_when_hash_suffix_found_in_response(): void
    {
        // SHA1("password") = 5BAA61E4C9B93F3F0682250B6CF8331B7EE68FD8
        // Prefix: 5BAA6, Suffix: 1E4C9B93F3F0682250B6CF8331B7EE68FD8
        $responseBody = implode("\r\n", [
            '0B1C4DB1A42B9B9D4B89E6E4FCA1D4B2D86:3',
            '1E4C9B93F3F0682250B6CF8331B7EE68FD8:3861493',
            '2A9D56A2B5B7B8B9C0D1E2F3A4B5C6D7E8F:1',
        ]);

        $this->guzzle->expects($this->once())
            ->method('get')
            ->with('https://api.pwnedpasswords.com/range/5baa6')
            ->willReturn(new Response(200, [], $responseBody));

        $this->assertTrue($this->client->isPwned('password'));
    }

    #[Test]
    public function it_returns_false_when_hash_suffix_not_in_response(): void
    {
        // SHA1("correct horse battery staple") prefix/suffix
        $sha1 = sha1('correct horse battery staple');
        $prefix = substr($sha1, 0, 5);

        // Response with unrelated suffixes
        $responseBody = implode("\r\n", [
            '0000000000000000000000000000000000A:1',
            '0000000000000000000000000000000000B:2',
        ]);

        $this->guzzle->expects($this->once())
            ->method('get')
            ->with('https://api.pwnedpasswords.com/range/'.strtolower($prefix))
            ->willReturn(new Response(200, [], $responseBody));

        $this->assertFalse($this->client->isPwned('correct horse battery staple'));
    }

    #[Test]
    public function it_sends_only_the_first_five_characters_of_the_sha1(): void
    {
        $sha1 = sha1('test-password');
        $expectedPrefix = substr($sha1, 0, 5);

        $this->guzzle->expects($this->once())
            ->method('get')
            ->with($this->stringContains('/range/'.$expectedPrefix), $this->anything())
            ->willReturn(new Response(200, [], ''));

        $this->client->isPwned('test-password');
    }

    #[Test]
    public function it_sends_the_add_padding_header(): void
    {
        $this->guzzle->expects($this->once())
            ->method('get')
            ->with(
                $this->anything(),
                $this->callback(fn ($options) => ($options['headers']['Add-Padding'] ?? null) === 'true')
            )
            ->willReturn(new Response(200, [], ''));

        $this->client->isPwned('password');
    }

    #[Test]
    public function it_sends_a_user_agent_header(): void
    {
        $this->guzzle->expects($this->once())
            ->method('get')
            ->with(
                $this->anything(),
                $this->callback(fn ($options) => str_starts_with($options['headers']['User-Agent'] ?? '', 'fof/pwned-passwords'))
            )
            ->willReturn(new Response(200, [], ''));

        $this->client->isPwned('password');
    }

    #[Test]
    public function it_ignores_padded_entries_with_zero_count(): void
    {
        // Suffix of SHA1("password") with count 0 (padding entry) — should NOT match
        $responseBody = '1E4C9B93F3F0682250B6CF8331B7EE68FD8:0';

        $this->guzzle->method('get')
            ->willReturn(new Response(200, [], $responseBody));

        $this->assertFalse($this->client->isPwned('password'));
    }

    #[Test]
    public function it_returns_false_and_logs_warning_on_connection_failure(): void
    {
        $this->guzzle->method('get')
            ->willThrowException(new ConnectException('Connection refused', new Request('GET', 'test')));

        $this->logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('fof/pwned-passwords'));

        $this->assertFalse($this->client->isPwned('password'));
    }

    #[Test]
    public function it_returns_false_and_logs_warning_on_transfer_exception(): void
    {
        $this->guzzle->method('get')
            ->willThrowException(new TransferException('Timeout'));

        $this->logger->expects($this->once())
            ->method('warning');

        $this->assertFalse($this->client->isPwned('password'));
    }

    #[Test]
    public function it_does_not_send_the_full_password_or_hash_to_the_api(): void
    {
        $password = 'my-secret-password';
        $fullHash = sha1($password);

        $this->guzzle->expects($this->once())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->logicalNot($this->stringContains($password)),
                    $this->logicalNot($this->stringContains($fullHash))
                ),
                $this->anything()
            )
            ->willReturn(new Response(200, [], ''));

        $this->client->isPwned($password);
    }

    #[Test]
    public function it_handles_empty_response_body(): void
    {
        $this->guzzle->method('get')
            ->willReturn(new Response(200, [], ''));

        $this->assertFalse($this->client->isPwned('password'));
    }

    #[Test]
    public function it_matches_suffix_case_insensitively(): void
    {
        // SHA1("password") suffix in lowercase
        $suffix = strtolower('1E4C9B93F3F0682250B6CF8331B7EE68FD8');
        $responseBody = $suffix.':3861493';

        $this->guzzle->method('get')
            ->willReturn(new Response(200, [], $responseBody));

        $this->assertTrue($this->client->isPwned('password'));
    }
}
