<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator;

use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Callbacks\TransitionCallback;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\PaymentService;
use FKSDB\Models\Payment\PriceCalculator\PreProcess\Preprocess;
use Fykosak\Utils\Price\Currency;
use Fykosak\Utils\Price\MultiCurrencyPrice;

class PriceCalculator implements TransitionCallback
{

    private PaymentService $paymentService;
    /** @var Preprocess[] */
    private array $preProcess = [];

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * @return Currency[]
     */
    public function getAllowedCurrencies(): array
    {
        return Currency::cases();
    }

    public function addPreProcess(Preprocess $preProcess): void
    {
        $this->preProcess[] = $preProcess;
    }

    /**
     * @param PaymentHolder $holder
     * @throws \Exception
     */
    final public function __invoke(ModelHolder $holder, ...$args): void
    {
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

    /**
     * @return array[]
     */
    public function getGridItems(PaymentModel $modelPayment): array
    {
        $items = [];
        foreach ($this->preProcess as $preProcess) {
            $items = \array_merge($items, $preProcess->getGridItems($modelPayment));
        }
        return $items;
    }
}
