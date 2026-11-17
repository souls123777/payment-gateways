<?php

declare(strict_types=1);

namespace App\Tests\UI\Console;

use App\UI\Console\ProcessPaymentCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Boots the real kernel: proves that services.yaml wires the command, the use case,
 * the factory and every tagged gateway together.
 */
final class ProcessPaymentCommandWiringTest extends KernelTestCase
{
    #[DataProvider('paymentMethods')]
    public function testEveryPaymentMethodIsServedByATaggedGateway(string $method): void
    {
        $application = new Application(self::bootKernel());
        $tester = new CommandTester($application->find(ProcessPaymentCommand::NAME));

        $exitCode = $tester->execute(['method' => $method, 'amount' => '100']);

        self::assertSame(Command::SUCCESS, $exitCode);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function paymentMethods(): iterable
    {
        yield 'card' => ['card'];
        yield 'sbp' => ['sbp'];
        yield 'wallet' => ['wallet'];
    }
}
