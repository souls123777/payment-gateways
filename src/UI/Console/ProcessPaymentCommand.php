<?php

declare(strict_types=1);

namespace App\UI\Console;

use App\Application\Payment\Exception\PaymentGatewayNotFoundException;
use App\Application\Payment\ProcessPayment;
use App\Domain\Payment\Currency;
use App\Domain\Payment\Money;
use App\Domain\Payment\PaymentMethod;
use App\Domain\Payment\PaymentResult;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: ProcessPaymentCommand::NAME,
    description: 'Takes a payment through the gateway that matches the payment method',
)]
final class ProcessPaymentCommand extends Command
{
    public const NAME = 'app:payment:pay';

    private const ARGUMENT_METHOD = 'method';
    private const ARGUMENT_AMOUNT = 'amount';
    private const ARGUMENT_CURRENCY = 'currency';

    private const HELP_METHOD = 'Payment method: %s';
    private const HELP_AMOUNT = 'Amount in minor units (kopecks, cents): 150000 is 1500.00 RUB';
    private const HELP_CURRENCY = 'Currency: %s';
    private const CHOICES_SEPARATOR = ', ';

    private const ERROR_UNKNOWN_METHOD = 'Unknown payment method "%s". Supported: %s.';
    private const ERROR_UNKNOWN_CURRENCY = 'Unknown currency "%s". Supported: %s.';
    private const ERROR_INVALID_AMOUNT = 'Amount must be a whole number of minor units, got "%s".';

    private const LABEL_METHOD = 'Method';
    private const LABEL_AMOUNT = 'Amount';
    private const LABEL_STATUS = 'Status';
    private const LABEL_REASON = 'Reason';
    private const AMOUNT_FORMAT = '%s %s';
    private const MESSAGE_APPROVED = 'Payment approved.';
    private const MESSAGE_DECLINED = 'Payment declined.';

    public function __construct(private readonly ProcessPayment $processPayment)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARGUMENT_METHOD,
                InputArgument::REQUIRED,
                sprintf(self::HELP_METHOD, self::methodChoices()),
            )
            ->addArgument(self::ARGUMENT_AMOUNT, InputArgument::REQUIRED, self::HELP_AMOUNT)
            ->addArgument(
                self::ARGUMENT_CURRENCY,
                InputArgument::OPTIONAL,
                sprintf(self::HELP_CURRENCY, self::currencyChoices()),
                Currency::Rub->value,
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $method = $this->parseMethod($input);
            $amount = $this->parseAmount($input, $this->parseCurrency($input));
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return Command::INVALID;
        }

        try {
            $result = $this->processPayment->execute($method, $amount);
        } catch (PaymentGatewayNotFoundException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $this->render($io, $amount, $result);

        return $result->isApproved() ? Command::SUCCESS : Command::FAILURE;
    }

    private function parseMethod(InputInterface $input): PaymentMethod
    {
        $raw = (string) $input->getArgument(self::ARGUMENT_METHOD);

        return PaymentMethod::tryFrom(strtolower($raw))
            ?? throw new \InvalidArgumentException(
                sprintf(self::ERROR_UNKNOWN_METHOD, $raw, self::methodChoices()),
            );
    }

    private function parseCurrency(InputInterface $input): Currency
    {
        $raw = (string) $input->getArgument(self::ARGUMENT_CURRENCY);

        return Currency::tryFrom(strtoupper($raw))
            ?? throw new \InvalidArgumentException(
                sprintf(self::ERROR_UNKNOWN_CURRENCY, $raw, self::currencyChoices()),
            );
    }

    private function parseAmount(InputInterface $input, Currency $currency): Money
    {
        $raw = (string) $input->getArgument(self::ARGUMENT_AMOUNT);
        $amountInMinorUnits = filter_var($raw, FILTER_VALIDATE_INT);

        if (false === $amountInMinorUnits) {
            throw new \InvalidArgumentException(sprintf(self::ERROR_INVALID_AMOUNT, $raw));
        }

        return new Money($amountInMinorUnits, $currency);
    }

    private function render(SymfonyStyle $io, Money $amount, PaymentResult $result): void
    {
        $rows = [
            [self::LABEL_METHOD => $result->method->value],
            [self::LABEL_AMOUNT => sprintf(
                self::AMOUNT_FORMAT,
                $amount->toDecimalString(),
                $amount->currency->value,
            )],
            [self::LABEL_STATUS => $result->status->value],
        ];

        if (null !== $result->declineReason) {
            $rows[] = [self::LABEL_REASON => $result->declineReason->value];
        }

        $io->definitionList(...$rows);

        if ($result->isApproved()) {
            $io->success(self::MESSAGE_APPROVED);

            return;
        }

        $io->warning(self::MESSAGE_DECLINED);
    }

    private static function methodChoices(): string
    {
        return implode(
            self::CHOICES_SEPARATOR,
            array_map(static fn (PaymentMethod $method): string => $method->value, PaymentMethod::cases()),
        );
    }

    private static function currencyChoices(): string
    {
        return implode(
            self::CHOICES_SEPARATOR,
            array_map(static fn (Currency $currency): string => $currency->value, Currency::cases()),
        );
    }
}
