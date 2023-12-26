<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator;

use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Payment\PriceCalculator\PreProcess\Preprocess;
use FKSDB\Models\Payment\PriceCalculator\PreProcess\SchedulePrice;
use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Statement;
use Fykosak\Utils\Price\Currency;
use Fykosak\Utils\Price\MultiCurrencyPrice;

/**
 * @implements Statement<void,PaymentHolder>
 */
class PriceCalculator implements Statement
{
    private PaymentService $paymentService;
    /** @phpstan-var Preprocess[] */
    private array $preProcess;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
        $this->preProcess = [
            new SchedulePrice(),
        ];
    }

    /**
     * @phpstan-return Currency[]
     */
    public function getAllowedCurrencies(): array
    {
        return Currency::cases();
    }

    /**
     * @param PaymentHolder $args
     * @throws \Exception
     */
    final public function __invoke(...$args): void
    {
        [$holder] = $args;
        $multiPrice = MultiCurrencyPrice::createFromCurrencies([$holder->getModel()->getCurrency()]);

        foreach ($this->preProcess as $preProcess) {
            $multiPrice->add($preProcess->calculate($holder->getModel()));
        }
        $price = $multiPrice->getPrice($holder->getModel()->getCurrency());
        $this->paymentService->storeModel(
            ['price' => $price->getAmount(), 'currency' => $price->getCurrency()->value],
            $holder->getModel()
        );
    }
}
