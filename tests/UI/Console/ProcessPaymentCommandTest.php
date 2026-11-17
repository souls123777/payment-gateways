<?php

declare(strict_types=1);

namespace App\Tests\UI\Console;

use App\Application\Payment\ProcessPayment;
use App\Infrastructure\Payment\Gateway\CardGateway;
use App\Infrastructure\Payment\Gateway\SbpGateway;
use App\Infrastructure\Payment\Gateway\WalletGateway;
use App\Infrastructure\Payment\TaggedPaymentGatewayFactory;
use App\UI\Console\ProcessPaymentCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class ProcessPaymentCommandTest extends TestCase
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        $factory = new TaggedPaymentGatewayFactory([new CardGateway(), new SbpGateway(), new WalletGateway()]);
        $this->tester = new CommandTester(new ProcessPaymentCommand(new ProcessPayment($factory)));
    }

    public function testApprovedPaymentExitsWithSuccess(): void
    {
        $exitCode = $this->tester->execute(['method' => 'card', 'amount' => '150000', 'currency' => 'RUB']);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('1500.00 RUB', $this->tester->getDisplay());
        self::assertStringContainsString('approved', $this->tester->getDisplay());
    }

    public function testCurrencyDefaultsToRoubles(): void
    {
        $exitCode = $this->tester->execute(['method' => 'sbp', 'amount' => '100']);

        self::assertSame(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('1.00 RUB', $this->tester->getDisplay());
    }

    public function testDeclinedPaymentExitsWithFailureAndShowsTheReason(): void
    {
        $amount = (string) (WalletGateway::LIMIT_IN_MINOR_UNITS + 1);

        $exitCode = $this->tester->execute(['method' => 'wallet', 'amount' => $amount]);

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('limit_exceeded', $this->tester->getDisplay());
    }

    public function testSbpDeclinesForeignCurrency(): void
    {
        $exitCode = $this->tester->execute(['method' => 'sbp', 'amount' => '100', 'currency' => 'USD']);

        self::assertSame(Command::FAILURE, $exitCode);
        self::assertStringContainsString('currency_not_supported', $this->tester->getDisplay());
    }

    /**
     * @param array<string, string> $input
     */
    #[DataProvider('invalidInputs')]
    public function testInvalidInputExitsWithInvalid(array $input, string $expectedMessage): void
    {
        $exitCode = $this->tester->execute($input);

        self::assertSame(Command::INVALID, $exitCode);
        self::assertStringContainsString($expectedMessage, $this->tester->getDisplay());
    }

    /**
     * @return iterable<string, array{array<string, string>, string}>
     */
    public static function invalidInputs(): iterable
    {
        yield 'unknown method' => [['method' => 'crypto', 'amount' => '100'], 'Unknown payment method'];
        yield 'unknown currency' => [['method' => 'card', 'amount' => '100', 'currency' => 'XXX'], 'Unknown currency'];
        yield 'amount is not a number' => [['method' => 'card', 'amount' => 'abc'], 'whole number'];
        yield 'amount has a fraction' => [['method' => 'card', 'amount' => '10.5'], 'whole number'];
        yield 'amount is zero' => [['method' => 'card', 'amount' => '0'], 'must be positive'];
        yield 'amount is negative' => [['method' => 'card', 'amount' => '-5'], 'must be positive'];
    }
}
