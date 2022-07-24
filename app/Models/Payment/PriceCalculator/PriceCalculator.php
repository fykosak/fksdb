<?php

declare(strict_types=1);

namespace FKSDB\Models\Payment\PriceCalculator;

use FKSDB\Models\Transitions\Holder\PaymentHolder;
use FKSDB\Models\Transitions\Holder\ModelHolder;
use FKSDB\Models\Transitions\Callbacks\TransitionCallback;
use FKSDB\Models\ORM\Models\PaymentModel;
use FKSDB\Models\ORM\Services\ServicePayment;
use FKSDB\Models\Payment\PriceCalculator\PreProcess\Preprocess;
use Fykosak\Utils\Price\Currency;
use Fykosak\Utils\Price\MultiCurrencyPrice;

class PriceCalculator implements TransitionCallback
{

    private ServicePayment $servicePayment;
    /** @var Preprocess[] */
    private array $preProcess = [];

    public function __construct(ServicePayment $servicePayment)
    {
        $this->servicePayment = $servicePayment;
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
        $this->servicePayment->updateModel(
            $holder->getModel(),
            ['price' => $price->getAmount(), 'currency' => $price->getCurrency()->value]
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

    /**
     * @throws \Exception
     */
    public function invoke(ModelHolder $holder, ...$args): void
    {
        $this->__invoke($holder, ...$args);
    }
}
